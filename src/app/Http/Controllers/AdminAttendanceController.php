<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

        return view('admin.attendance.list', compact(
            'attendances',
            'targetDate',
            'prevDate',
            'nextDate',
        ));
    }

    /**
     * 勤怠詳細画面を表示
     *
     * @return void
     */
    public function detail()
    {
        return redirect()->route('test');
    }
}
