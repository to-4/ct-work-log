<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use App\Models\AttendanceStatus;
use App\Http\Requests\UpdateAttendanceRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

use function Psy\debug;

class AttendanceController extends Controller
{

    /**
     * 勤怠入力表示
     *
     * @return void
     */
    public function index()
    {
        // 勤怠情報を取得
        $user_id = Auth::user()->id;
        $today   = Carbon::today(); // 現在日

        // ログインユーザの現在日データを取得
        // 見つからなければ、空インスタンスをセット
        /** @var Attendance $attendance */
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

    /**
     * 勤怠一覧表示
     *
     * @return void
     */
    public function list(Request $request)
    {

        // クエリパラメータを取得
        $targetMonthStr = $request->query('month', Carbon::now()->format('Y-m'));

        // Carbon オブジェクト取得
        $targetMonth = Carbon::createFromFormat('Y-m', $targetMonthStr);

        // 前月・次月を取得
        $prevMonth = $targetMonth->copy()->subMonth();
        $nextMonth = $targetMonth->copy()->addMonth();

        // 勤怠情報（Collection）を取得
        //  - key: YYYY-MM-DD, value: Attendance
        $userId = Auth::user()->id;
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
            $dateKey = $date->toDateString();
            if ($attendanceMap->has($dateKey)) {
                $attendances->push($attendanceMap->get($dateKey));
                continue;
            }

            $placeholder = new Attendance([
                'user_id'   => $userId,
                'work_date' => $date->copy(),
            ]);
            $attendances->push($placeholder);
        }

        // ビューに渡す
        return view('attendances.list', compact(
            'attendances',
            'targetMonth',
            'prevMonth',
            'nextMonth'
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
        $temp = $attendance->attendanceBreaks;

        // ログインユーザーが他人の勤怠にアクセスしていないか確認
        if (Auth::User()->id !== $attendance->user_id) {
            abort(403, 'このページへアクセスする権限がありません。');
        }

        return view('attendances.detail', compact('attendance'));
    }

    /**
     * 勤怠入力：出勤
     *
     * @return void
     */
    public function start(Request $request)
    {

        $user_id      = Auth::user()->id;
        $today        = Carbon::today(); // 現在日
        $current_time = Carbon::now()->setSecond(0)->format('H:i'); // 現在時刻（秒切り捨て）

        // 勤怠情報を登録
        try {
            Attendance::create([
                'user_id'              => $user_id,
                'work_date'            => $today,
                'clock_in_at'          => $current_time,
                'attendance_status_id' => AttendanceStatus::WORKING,
            ]);
        } catch (\Throwable $e)
        {
            // ログは strage/logs/laravel.log
            Log::error('Attendance create failed: ' . $e->getMessage());
            return back()->with('error', '登録に失敗しました');
        }

        return back();
    }

    /**
     * 勤怠入力：退勤
     *
     * @return void
     */
    public function end(Request $request)
    {
        // 勤怠情報を取得
        $user_id      = Auth::user()->id;
        $today        = Carbon::today(); // 現在日
        $current_time = Carbon::now()->setSecond(0)->format('H:i'); // 現在時刻（秒切り捨て）

        // ログインユーザの現在日データを取得
        // 見つからなければ、エラーメッセージ
        /** @var Attendance $attendance */
        $attendance = Attendance::where('user_id', $user_id)
            ->wheredate('work_date', $today)
            ->first();
        if (!$attendance) {
            // ログは strage/logs/laravel.log
            Log::error('Attendance end failed: 退勤対象となるデータが見つからなかった (user_id=' . $user_id . ', work_date=' . $today->toDateString('y-m-d') . ')');
            return back()->with('error', '退勤更新に失敗しました');
        }

        // 更新
        try {

            $attendance->clock_out_at = $current_time; // 退勤時刻
            $attendance->attendance_status_id = AttendanceStatus::COMPLETED; // 退勤済みステータス

            // 休憩時間の集計（分）
            $break_minutes = AttendanceBreak::sumBreakMinutes($attendance->attendanceBreaks);
            $attendance->break_minutes = $break_minutes;

            // 勤務時間集計（分）
            $clock_in_at  = Carbon::createFromFormat('H:i', $attendance->clock_in_at);
            $clock_out_at = Carbon::createFromFormat('H:i', $attendance->clock_out_at);
            $working_minutes = $clock_in_at->diffinminutes($clock_out_at);
            $attendance->working_minutes = $working_minutes - $break_minutes;

            // テーブル更新
            $attendance->save();
        }
        catch (\Throwable $e)
        {
            Log::error('Attendance end failed: ' . $e->getMessage());
            return back()->with('error', '退勤更新に失敗しました');
        }

        return back();
    }

    /**
     * 勤怠入力：休憩開始
     *
     * @return void
     */
    public function break_start(Request $request)
    {
        // 勤怠情報を取得
        $user_id      = Auth::user()->id;
        $today        = Carbon::today(); // 現在日
        $current_time = Carbon::now()->format('H:i'); // 現在時刻

        // ログインユーザの現在日データを取得
        // 見つからなければ、エラーメッセージ
        /** @var Attendance $attendance */
        $attendance = Attendance::where('user_id', $user_id)
            ->wheredate('work_date', $today)
            ->first();
        if (!$attendance) {
            // ログは strage/logs/laravel.log
            Log::error('Attendance break start failed: 休憩対象となるデータが見つからなかった (user_id=' . $user_id . ', work_date=' . $today->toDateString('y-m-d') . ')');
            return back()->with('error', '休憩開始に失敗しました');
        }

        // 更新
        try {
            // 勤怠情報更新
            $attendance->attendance_status_id = AttendanceStatus::ON_BREAK; // 休憩中ステータス
            $attendance->save();

            // 休憩時間を新規登録
            AttendanceBreak::create([
                'attendance_id'  =>$attendance->id,
                'break_start_at' => $current_time,
            ]);

        } catch (\Throwable $e) {
            Log::error('Attendance break start failed: ' . $e->getMessage());
            return back()->with('error', '休憩開始に失敗しました');
        }

        return back();
    }

    /**
     * 勤怠入力：休憩終了
     *
     * @return void
     */
    public function break_end(Request $request)
    {
        // 勤怠情報を取得
        $user_id      = Auth::user()->id;
        $today        = Carbon::today(); // 現在日
        $current_time = Carbon::now()->setSecond(0)->format('H:i'); // 現在時刻（秒切り捨て）

        // ログインユーザの現在日データを取得
        // 見つからなければ、エラーメッセージ
        /** @var Attendance $attendance */
        $attendance = Attendance::where('user_id', $user_id)
            ->wheredate('work_date', $today)
            ->first();
        if (!$attendance) {
            // ログは strage/logs/laravel.log
            Log::error('Attendance break end failed: 休憩対象となるデータが見つからなかった (user_id=' . $user_id . ', work_date=' . $today->toDateString('y-m-d') . ')');
            return back()->with('error', '休憩終了に失敗しました');
        }

        // 更新
        try {

            // 勤怠情報を更新
            $attendance->attendance_status_id = AttendanceStatus::WORKING; // 勤務中ステータス
            $attendance->save();

            /** @var AttendanceBreak $break */
            $break = $attendance->attendanceBreaks()
                ->where('break_end_at', null)
                ->first();

            $break->break_end_at = $current_time; // 休憩終了時刻
            $break->break_minutes = AttendanceBreak::getBreakMinutes($break); // 休憩時間

            $break->save();
        } catch (\Throwable $e) {
            Log::error('Attendance break end failed: ' . $e->getMessage());
            return back()->with('error', '休憩終了に失敗しました');
        }

        return back();
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
        }
        catch (Throwable $e) {
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
