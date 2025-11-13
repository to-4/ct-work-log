<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AttendanceDetailDisplayTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_logged_in_user_name_on_attendance_detail_page(): void
    {
        // 1. ステータス作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. ユーザー作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 勤怠データ作成（本日分）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '17:30',
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 勤怠詳細ページにアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 6. ページが正常に表示されていることを確認
        $response->assertStatus(200);

        // 7. ページ上にユーザーの名前が表示されていることを確認
        $response->assertSee($user->name);
    }

    #[Test]
    public function it_displays_selected_work_date_on_detail_page(): void
    {
        // 1. ステータス作成（退勤済を想定）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. 一般ユーザー作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 勤怠データを作成（本日の日付で登録）
        $today = Carbon::today();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '17:30',
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 勤怠詳細画面にアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 6. ステータス200（正常表示）
        $response->assertStatus(200);

        // 7. 日付欄に登録された日付（work_date）が表示されていることを確認
        $response->assertSee($attendance->work_date->format('Y年'));
        $response->assertSee($attendance->work_date->format('n月'));
        $response->assertSee($attendance->work_date->format('j日'));
    }

    #[Test]
    public function it_displays_correct_clock_in_and_out_times_on_detail_page(): void
    {
        // 1. ステータスマスタ作成（例：勤務中・退勤済）
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '勤務中',
        ]);

        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. テスト用ユーザー作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 勤怠データ作成（出勤9:00 / 退勤18:00）
        $workDate = Carbon::today();
        $clockIn = Carbon::now()->setTime(9, 0);
        $clockOut = Carbon::now()->setTime(18, 0);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '17:30',
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 勤怠詳細ページにアクセス
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 6. 正常表示（HTTP 200）
        $response->assertStatus(200);

        // 7. 出勤・退勤時刻が正しく表示されていることを確認
        $response->assertSee('09:00');
        $response->assertSee('17:30');
    }

    #[Test]
    public function it_displays_break_times_correctly_on_attendance_detail_page(): void
    {
        // 1. ステータスを作成（勤務中 / 退勤済）
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '勤務中',
        ]);
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. 一般ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 勤怠データ作成（本日分）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '17:30',
        ]);

        // 4. 休憩データを作成（例：12:00～12:30）
        $breakStart = Carbon::today()->setTime(12, 0);
        $breakEnd = Carbon::today()->setTime(12, 30);

        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => $breakStart->format('H:i'),
            'break_end_at' => $breakEnd->format('H:i'),
        ]);

        // 5. ログイン状態を再現
        $this->actingAs($user);

        // 6. 勤怠詳細ページを取得
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 7. ステータス200確認
        $response->assertStatus(200);

        // 8. 「休憩」欄に正しい時間が表示されていることを確認
        $response->assertSee($breakStart->format('H:i'));
        $response->assertSee($breakEnd->format('H:i'));
    }
}
