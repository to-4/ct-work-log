<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    /**
     * 一覧画面を表示
     * ※ 管理者か一般ユーザで処理を分岐
     */
    public function list(Request $request)
    {
        $status = $request->input('status', 'pending');

        /** @var User $user */
        $user = Auth::user();
        $query = AttendanceCorrectionRequest::with(['attendance', 'attendance.user'])
            ->orderByDesc('requested_at');

        if ($status === 'pending') {
            $query->whereNull('approved_at');
        } else {
            $query->whereNotNull('approved_at');
        }

        if ($user->is_admin == false) {
            $userId = $user->id;
            $query->whereHas('attendance', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        $requests = $query->get();

        if ($user->is_admin == false) {
            return view('requests.list', compact('requests', 'status'));
        } else {
            return view('admin.requests.list', compact('requests', 'status'));
        }
    }
}
