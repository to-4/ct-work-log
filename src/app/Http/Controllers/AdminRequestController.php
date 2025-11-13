<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminRequestController extends Controller
{
    /**
     * 承認画面を表示
     */
    public function approve(Request $request, $id)
    {

        // 対象申請データを取得（関連データもまとめて取得）
        /** @var AttendanceCorrectionRequest|null $correctionRequest */
        $correctionRequest = AttendanceCorrectionRequest::with('attendance')
            ->where('id', $id)
            ->first();
        if ($correctionRequest == null) {
            return back()->with('error', '申請情報がありません');
        }

        $attendance = $correctionRequest->attendance;
        $attendance->loadMissing('user');
        $attendance->loadmissing('attendanceBreaks');

        return view('admin.requests.approve', compact('attendance', 'correctionRequest'));
    }

    /**
     * 承認画面：承認
     */
    public function update(Request $request, $id)
    {
        /** @var AttendanceCorrectionRequest|null $correctionRequest */
        $correctionRequest = AttendanceCorrectionRequest::with('attendance')->find($id);
        if ($correctionRequest === null) {
            return back()->with('error', '申請情報がありません');
        }

        if ($correctionRequest->attendance === null) {
            return back()->with('error', '紐づく勤怠情報がありません');
        }

        try {
            DB::transaction(function () use ($correctionRequest) {

                // 申請情報を更新
                $correctionRequest->approved_by = Auth::id();
                $correctionRequest->approved_at = now();
                $correctionRequest->save();

                // 勤怠情報を更新（承認待ち）
                $attendance = $correctionRequest->attendance;
                $attendance->is_pending_approval = false;
                $attendance->save();
            });
        } catch (Throwable $e) {
            Log::error('Attendance correction approval failed: '.$e->getMessage(), [
                'request_id' => $correctionRequest->id,
                'admin_id' => Auth::id(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', '承認処理に失敗しました');
        }

        return back();
    }
}
