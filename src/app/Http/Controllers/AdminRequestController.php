<?php

namespace App\Http\Controllers;

use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;

class AdminRequestController extends Controller
{
    /**
     * 一覧画面を表示
     *
     */
    public function list(Request $request)
    {
        $status = $request->input('status', 'pending');

        $query = AttendanceCorrectionRequest::with(['attendance', 'attendance.user'])
            ->orderByDesc('requested_at');

        if ($status === 'pending') {
            $query->whereNull('approved_at');
        } else {
            $query->whereNotNull('approved_at');
        }

        $requests = $query->get();

        return view('admin.requests.list', compact('requests', 'status'));
    }
}
