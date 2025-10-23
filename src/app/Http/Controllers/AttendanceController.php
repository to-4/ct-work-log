<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function Psy\debug;

class AttendanceController extends Controller
{
    /**
     * 勤怠入力表示
     */
    public function index()
    {
        // 勤怠情報を取得
        $user_id = Auth::user()->id;
        $today   = Carbon::today(); // 現在日

        // ログインユーザの現在日データを取得
        // 見つからなければ、空インスタンスをセット
        $attendance = Attendance::where('user_id', $user_id)
            ->wheredate('work_date', $today)
            ->first();
        if (!$attendance)
        {
            $attendance = new Attendance();
            $attendance->attendance_status_id = AttendanceStatus::OFF_DUTY;
        }

        // ローディング
        $attendance->loadMissing('attendanceStatus');

        // ビューに渡す
        return view('attendances.index', compact('attendance'));
    }
}
