<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AdminAttendanceController extends Controller
{
    /**
     * 勤怠一覧画面を表示
     *
     * @return void
     */
    public function list(Request $request)
    {
        $targetDateStr = $request->query('date', Carbon::now()->format('Y-m-d'));
        $targetDate    = Carbon::createFromFormat('Y-m-d', $targetDateStr);
        $prevDate      = $targetDate->copy()->subDay();
        $nextDate      = $targetDate->copy()->addDay();

        $attendances = Attendance::with('user')
            ->whereDate('work_date', $targetDateStr)
            ->orderby('work_date')
            ->get();

        return view('admin.attendances.list', compact(
            'attendances',
            'targetDate',
            'prevDate',
            'nextDate',
        ));
    }

    /**
     * 勤怠詳細画面を表示
     */
    public function detail($id)
    {
        // 対象勤怠データを取得（関連データもまとめて取得）
        /** @var Attendance $attendance */
        $attendance = Attendance::where('id', $id)->first();
        if ($attendance == null) {
            return back()->with('error', '勤怠情報がありません');
        }

        // 退勤済みの勤怠のみ修正可とする
        if ($attendance->attendance_status_id !== AttendanceStatus::COMPLETED) {
            return back()->with('error', 'まだ退勤済みではありません');
        }

        $attendance->loadMissing('user');
        $attendance->loadmissing('attendanceBreaks');
        $temp = $attendance->attendanceBreaks;

        return view('admin.attendances.detail', compact('attendance'));
    }

    /**
     * 勤怠詳細：修正
     *
     * @return void
     */
    public function update(UpdateAttendanceRequest $request, $id)
    {
        // 検証はここへ来る前に完了（$request->validated() でOK）
        $validated = $request->validated();

        /** @var Attendance $attendance */
        $attendance = Attendance::with('attendanceBreaks')->findOrFail($id);

        if ($attendance->attendance_status_id !== AttendanceStatus::COMPLETED) {
            return back()
                ->with('error', '退勤済みの勤怠のみ修正できます。')
                ->withInput();
        }

        try {

            // トランザクションでまとめて処理
            // ※ 失敗時は自動でロールバックがされる
            DB::transaction(function () use ($request, $attendance) {

                // 休憩情報の更新
                foreach ($request->input('breaks', []) as $breakId => $data) {

                    // 値が両方とも空ならスキップ
                    if (empty($data['break_start_at']) && empty($data['break_end_at'])) {
                        continue;
                    }

                    $break = new AttendanceBreak();
                    if ($breakId != 'new') {
                        $break = $attendance->attendanceBreaks()->find($breakId); // 既存情報を取得
                    }

                    // 休憩情報の新規登録/更新
                    $break = $attendance->attendanceBreaks()->find($breakId);
                    $break->break_start_at = $data['break_start_at'];
                    $break->break_end_at   = $data['break_end_at'];
                    $break->break_minutes  = AttendanceBreak::getBreakMinutes($break);
                    $break->save();
                }

                // 勤怠情報を更新
                $attendance->clock_in_at  = $request->input('clock_in_at');
                $attendance->clock_out_at = $request->input('clock_out_at');
                $attendance->note         = $request->input('note');

                // 休憩時間を再集計
                $attendance->load('attendanceBreaks');
                $break_minutes = AttendanceBreak::sumBreakMinutes($attendance->attendanceBreaks);
                $attendance->break_minutes = $break_minutes;

                // 勤務時間を再集計
                $clock_in_at  = Carbon::createFromFormat('H:i', $attendance->clock_in_at);
                $clock_out_at = Carbon::createFromFormat('H:i', $attendance->clock_out_at);
                $working_minutes = $clock_in_at->diffinminutes($clock_out_at);
                $attendance->working_minutes = $working_minutes - $break_minutes;

                $attendance->is_pending_approval = true; // 承認待ちフラグ
                $attendance->save();

                // 勤怠修正申請情報を登録
                AttendanceCorrectionRequest::create([
                    'attendance_id' => $attendance->id,
                    'requested_at'  => Carbon::now(),
                ]);
            });
        } catch (Throwable $e) {
            Log::error('勤怠更新に失敗しました: ' . $e->getMessage(), [
                'user_id' => Auth::User()->id,
                'attendance_id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withErrors(['error' => '更新処理中にエラーが発生しました。時間をおいて再度お試しください。'])
                ->withInput();
        }

        return redirect()->route('request.list');
    }
}
