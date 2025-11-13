<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_all_users_attendance_records_for_today_correctly(): void
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 2. 一般ユーザーを2名作成
        $userA = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
        ]);
        $userB = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'hanako@example.com',
        ]);

        // 3. 勤怠ステータス作成
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '出勤中',
        ]);
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 4. 当日分の勤怠データ作成
        $attendanceA = Attendance::factory()->create([
            'user_id' => $userA->id,
            'attendance_status_id' => $statusWorking->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => null,
        ]);

        $attendanceB = Attendance::factory()->create([
            'user_id' => $userB->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '08:50:00',
            'clock_out_at' => '17:30:00',
        ]);

        // 5. 管理者としてログイン
        $this->actingAs($admin);

        // 6. 勤怠一覧ページを開く
        $response = $this->get(route('admin.attendance.list'));

        // 7. HTTPステータス確認
        $response->assertStatus(200);

        // 8. ページタイトル確認
        $response->assertSee('勤怠一覧');

        // 9. 当日分データが全ユーザー分表示されていることを確認
        $today = Carbon::today()->format('Y/m/d');
        $response->assertSee($today);
        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
        $response->assertSee('09:00');
        $response->assertSee('08:50');
        $response->assertSee('17:30');
    }

    #[Test]
    public function it_displays_today_date_on_admin_attendance_list(): void
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 2. 管理者としてログイン
        $this->actingAs($admin);

        // 3. 勤怠一覧画面を開く
        $response = $this->get(route('admin.attendance.list'));

        // 4. HTTPステータス確認
        $response->assertStatus(200);

        // 5. 今日の日付をフォーマット
        $today = Carbon::today()->format('Y/m/d');

        // 6. ページ内に今日の日付が表示されていることを確認
        $response->assertSee($today);
    }

    #[Test]
    public function it_displays_previous_day_attendance_records_when_admin_presses_previous_button(): void
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 2. 勤怠ステータス作成
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '出勤中',
        ]);
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 3. 一般ユーザー作成
        $userA = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
        ]);

        // 4. 当日・前日の勤怠データを作成
        $today = Carbon::today();
        $previousDay = Carbon::yesterday();

        Attendance::factory()->create([
            'user_id' => $userA->id,
            'attendance_status_id' => $statusWorking->id,
            'work_date' => $today,
            'clock_in_at' => '09:00',
            'clock_out_at' => null,
        ]);

        Attendance::factory()->create([
            'user_id' => $userA->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => $previousDay,
            'clock_in_at' => '08:45',
            'clock_out_at' => '17:15',
        ]);

        // 5. 管理者としてログイン
        $this->actingAs($admin);

        // 6. 「前日」ボタン押下（クエリパラメータで再現）
        $response = $this->get(route('admin.attendance.list', [
            'date' => $previousDay->format('Y-m-d'),
        ]));

        // 7. ステータス確認
        $response->assertStatus(200);

        // 8. ページタイトル・日付表示確認
        $response->assertSee('勤怠一覧');
        $response->assertSee($previousDay->format('Y/m/d'));

        // 9. 前日の勤怠データが表示されていることを確認
        $response->assertSee('山田太郎');
        $response->assertSee('08:45');
        $response->assertSee('17:15');

        // 10. 当日の勤怠データが表示されていないことを確認
        $response->assertDontSee('09:00');
    }

    #[Test]
    public function it_displays_next_day_attendance_records_when_admin_presses_next_button(): void
    {
        // 1. 管理者ユーザー作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 2. 勤怠ステータス作成
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '出勤中',
        ]);

        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 3. 一般ユーザーと翌日分勤怠データを作成
        $userA = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
        ]);

        $today = Carbon::today();
        $nextDay = Carbon::tomorrow();

        // 当日データ（表示されない想定）
        Attendance::factory()->create([
            'user_id' => $userA->id,
            'attendance_status_id' => $statusWorking->id,
            'work_date' => $today,
            'clock_in_at' => '09:00',
            'clock_out_at' => null,
        ]);

        // 翌日データ（表示される想定）
        Attendance::factory()->create([
            'user_id' => $userA->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => $nextDay,
            'clock_in_at' => '08:45',
            'clock_out_at' => '17:30',
        ]);

        // 4. 管理者ログイン状態を再現
        $this->actingAs($admin);

        // 5. 「翌日」ボタン押下（クエリパラメータで再現）
        $response = $this->get(route('admin.attendance.list', [
            'date' => $nextDay->format('Y-m-d'),
        ]));

        // 6. ステータス確認
        $response->assertStatus(200);

        // 7. ページタイトルと翌日の日付表示確認
        $response->assertSee('勤怠一覧');
        $response->assertSee($nextDay->format('Y/m/d'));

        // 8. 翌日分勤怠データ確認
        $response->assertSee('山田太郎');
        $response->assertSee('08:45');
        $response->assertSee('17:30');

        // 9. 当日のデータが表示されないことを確認
        $response->assertDontSee('09:00');
    }
}
