<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAttendanceRequest;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceStatus;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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

        $attendance->loadMissing('user');
        $attendance->loadmissing('attendanceBreaks');

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
                ->with('error', '退勤済みの勤怠のみ修正できます')
                ->withInput(); // 入力を保持
        }

        try {

            // トランザクションでまとめて処理
            // ※ 失敗時は自動でロールバックがされる
            DB::transaction(function () use ($request, $attendance) {

                // 休憩情報の更新
                foreach ($request->input('breaks', []) as $breakId => $data) {

                    // 値が両方とも空ならスキップ ※ 新規登録のみあり得る
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

                $attendance->save();

            });
        } catch (Throwable $e) {
            Log::error('勤怠更新に失敗しました: ' . $e->getMessage(), [
                'user_id'       => Auth::User()->id,
                'attendance_id' => $id,
                'trace'         => $e->getTraceAsString(),
            ]);

            return back()
                ->with('error', '更新処理中にエラーが発生しました。時間をおいて再度お試しください') 
                ->withInput();
        }

        return redirect()->route('request.list');
    }

    /**
     * スタッフ別の勤怠一覧表示
     *
     * @return void
     */
    public function staff_list(Request $request, $id)
    {

        // ユーザ情報取得
        $user = User::where('id', $id)->first();
        $userId = $id;

        // クエリパラメータを取得
        $targetMonthStr = $request->query('month', Carbon::now()->format('Y-m'));

        // Carbon オブジェクト取得
        $targetMonth = Carbon::createFromFormat('Y-m', $targetMonthStr);

        // 前月・次月を取得
        $prevMonth = $targetMonth->copy()->subMonth();
        $nextMonth = $targetMonth->copy()->addMonth();

        // 勤怠情報（Collection）を取得
        //  - key: YYYY-MM-DD, value: Attendance
        $attendanceMap = Attendance::where('user_id', $userId)
            ->whereMonth('work_date', $targetMonth->month)
            ->whereYear('work_date', $targetMonth->year)
            ->orderby('work_date')
            ->get()
            ->keyBy(function (Attendance $attendance) {
                return $attendance->work_date->toDateString();
            });

        // 当月の全日付分の勤怠情報を生成（未登録日は work_date のみを持つ新インスタンスを作成）
        $period = CarbonPeriod::create(
            $targetMonth->copy()->startOfMonth(),
            $targetMonth->copy()->endOfMonth()
        );

        $attendances = collect();
        foreach ($period as $date) {
            $dateKey = $date->toDateString(); // 日付文字列（YYYY-MM-DD）
            if ($attendanceMap->has($dateKey)) {
                $attendances->push($attendanceMap->get($dateKey));
                continue;
            }

            // ダミーデータ
            $placeholder = new Attendance([
                'user_id'   => $userId,
                'work_date' => $date->copy(),
            ]);
            $attendances->push($placeholder);
        }

        // ビューに渡す
        return view('admin.attendances.staff_list', compact(
            'attendances',
            'targetMonth',
            'prevMonth',
            'nextMonth',
            'user'
        ));
    }
}
