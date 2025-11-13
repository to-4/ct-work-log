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

class AdminStaffListTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_displays_all_general_users_for_admin(): void
    {
        // 1. 管理者ユーザー作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 2. 一般ユーザー作成（複数）
        $users = User::factory()->count(3)->sequence(
            ['name' => '山田太郎', 'email' => 'taro@example.com'],
            ['name' => '佐藤花子', 'email' => 'hanako@example.com'],
            ['name' => '鈴木次郎', 'email' => 'jiro@example.com'],
        )->create();

        // 3. 管理者としてログイン
        $this->actingAs($admin);

        // 4. スタッフ一覧ページにアクセス
        $response = $this->get(route('admin.staff.list')); // ルート名に合わせて修正可

        // 5. HTTPステータス確認
        $response->assertStatus(200);

        // 6. 一般ユーザーの情報が一覧に含まれていることを確認
        foreach ($users as $user) {
            $response->assertSee($user->name);
            $response->assertSee($user->email);
        }

        // 7. 管理者自身が一覧に含まれていないことを確認（任意）
        $response->assertDontSee('管理者ユーザー');
    }

    #[Test]
    public function it_displays_selected_user_attendance_records_for_admin(): void
    {
        // 1. 管理者ユーザー作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 2. 一般ユーザー作成
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
            'is_admin' => false,
        ]);

        // 3. 勤怠ステータス作成（退勤済）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 4. 勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'break_minutes' => 30,
        ]);

        // 4-1. 休憩データを追加
        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '12:00',
            'break_end_at' => '12:30',
        ]);

        // 5. 管理者でログイン
        $this->actingAs($admin);

        // 6. 対象ユーザーの勤怠一覧ページにアクセス
        $response = $this->get(route('admin.attendance.staff_list', ['id' => $user->id]));

        // 7. ステータス確認
        $response->assertStatus(200);

        // 8. 勤怠情報が正しく表示されていることを確認
        $month = Carbon::today()->format('Y/m');
        $response->assertSee($user->name);
        $response->assertSee($month);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('0:30');
    }

    #[Test]
    public function it_displays_previous_month_attendance_records_when_admin_presses_previous_button(): void
    {
        // 1. 管理者ユーザー作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 2. 一般ユーザー作成
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
            'is_admin' => false,
        ]);

        // 3. 勤怠ステータス（退勤済）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 4. 当月（今月）と前月の日付設定
        $today = Carbon::today();              // 例: 2025-11-12
        $previousMonth = $today->copy()->subMonthNoOverflow(); // 例: 2025-10-12

        // 5. 当月の勤怠データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => $today,
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        // 6. 前月の勤怠データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => $previousMonth,
            'clock_in_at' => '08:45',
            'clock_out_at' => '17:15',
        ]);

        // 7. 管理者としてログイン
        $this->actingAs($admin);

        // 8. 「前月」ボタン押下を再現（クエリで指定）
        $response = $this->get(route('admin.attendance.staff_list', [
            'id' => $user->id,
            'month' => $previousMonth->format('Y-m'),
        ]));

        // 9. ステータス確認
        $response->assertStatus(200);

        // 10. 前月の日付・時刻が表示されていることを確認
        $response->assertSee($previousMonth->format('Y/m'));
        $response->assertSee('08:45');
        $response->assertSee('17:15');

        // 11. 当月の勤怠情報が表示されていないことを確認
        $response->assertDontSee($today->format('Y/m'));
        $response->assertDontSee('09:00');
        $response->assertDontSee('18:00');
    }

    #[Test]
    public function it_displays_next_month_attendance_records_when_admin_presses_next_button(): void
    {
        // 1. 管理者ユーザー作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 2. 一般ユーザー作成
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
            'is_admin' => false,
        ]);

        // 3. 勤怠ステータス作成（退勤済）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 4. 日付設定
        $today = Carbon::today();                    // 当月 (例: 2025-11-12)
        $nextMonth = $today->copy()->addMonthNoOverflow(); // 翌月 (例: 2025-12-12)

        // 5. 当月データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => $today,
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        // 6. 翌月データ
        Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => $nextMonth,
            'clock_in_at' => '08:45',
            'clock_out_at' => '17:15',
        ]);

        // 7. 管理者でログイン
        $this->actingAs($admin);

        // 8. 「翌月」ボタン押下を再現（クエリパラメータで指定）
        $response = $this->get(route('admin.attendance.staff_list', [
            'id' => $user->id,
            'month' => $nextMonth->format('Y-m'),
        ]));

        // 9. ステータス確認
        $response->assertStatus(200);

        // 10. 翌月のデータが表示されていることを確認
        $response->assertSee($nextMonth->format('Y/m'));
        $response->assertSee('08:45');
        $response->assertSee('17:15');

        // 11. 当月のデータが表示されていないことを確認
        $response->assertDontSee($today->format('Y/m'));
        $response->assertDontSee('09:00');
        $response->assertDontSee('18:00');
    }

    #[Test]
    public function it_navigates_to_attendance_detail_page_when_admin_presses_detail_button(): void
    {
        // 1. 管理者ユーザーを作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 2. 一般ユーザーを作成
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
            'is_admin' => false,
        ]);

        // 3. 勤怠ステータス（退勤済）を作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 4. 当日の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        // 5. 管理者としてログイン
        $this->actingAs($admin);

        // 6. 勤怠一覧ページを開く
        $response = $this->get(route('admin.attendance.staff_list', ['id' => $user->id]));
        $response->assertStatus(200);

        // 7. 「詳細」ボタン（リンク）が存在することを確認
        $response->assertSee('詳細');

        // 8. 「詳細」ボタン押下を想定して遷移先URLにアクセス
        $detailUrl = route('admin.attendance.detail', ['id' => $attendance->id]);
        $response = $this->get($detailUrl);

        // 9. 詳細画面が正常に表示されることを確認
        $response->assertStatus(200);

        // 10. 勤怠詳細情報が含まれていることを確認
        $response->assertSee($user->name);
        $response->assertSee(Carbon::today()->isoFormat('YYYY年M月D日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }
}
