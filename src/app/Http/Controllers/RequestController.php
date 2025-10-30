<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class RequestController extends Controller
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

        return view('requests.list', compact('requests', 'status'));
    }
}
