<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\AttendanceBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceDetailTest extends TestCase
{
    Use refreshDatabase;

    #[Test]
    public function it_displays_selected_attendance_detail_correctly_for_admin(): void
    {
        // 1. ステータスを作成（退勤済）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. 一般ユーザーを作成
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
        ]);

        // 3. 管理者ユーザーを作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 4. 勤怠データを作成（work_date = today）
        $today = Carbon::today();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => $today,
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'working_minutes' => 510,
            'break_minutes' => 30,
            'note' => '出勤確認テスト',
        ]);

        // 5. 休憩データを作成
        AttendanceBreak::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '12:00',
            'break_end_at' => '12:30',
        ]);

        // 6. 管理者としてログイン
        $this->actingAs($admin);

        // 7. 詳細ページを開く
        $response = $this->get(route('admin.attendance.detail', $attendance->id));

        // 8. HTTPステータス確認
        $response->assertStatus(200);

        // 9. ページタイトルとユーザー名確認
        $response->assertSee('勤怠詳細');
        $response->assertSee('山田太郎');

        // 10. 日付・時刻・備考が正しく表示されているか確認
        $response->assertSee($today->format('Y年'));
        $response->assertSee($today->format('n月'));
        $response->assertSee($today->format('j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('12:30');
        $response->assertSee('出勤確認テスト');
    }

    #[Test]
    public function it_shows_error_when_clock_in_is_later_than_clock_out_for_admin(): void
    {
        // 1. 勤怠ステータス（退勤済）を作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. 一般ユーザーを作成
        $user = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
        ]);

        // 3. 管理者ユーザーを作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 4. 勤怠データを作成（出勤 09:00 / 退勤 18:00）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        // 5. 管理者ログイン
        $this->actingAs($admin);

        // 6. 詳細ページを開く
        $response = $this->get(route('admin.attendance.detail', $attendance->id));
        $response->assertStatus(200);

        // 7. 不正データ（出勤 19:00 > 退勤 18:00）を送信
        $formData = [
            'clock_in_at'  => '19:00',
            'clock_out_at' => '18:00',
            'note' => '不正テスト',
        ];
        $response = $this->put(route('admin.attendance.detail.update', $attendance->id), $formData);

        // 8. リダイレクト＋セッションエラー確認
        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        // 9. 実際のエラーメッセージを画面で確認
        $response = $this->followingRedirects()
            ->put(route('admin.attendance.detail.update', $attendance->id), $formData);

        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    #[Test]
    public function it_shows_error_when_break_start_is_after_clock_out_for_admin(): void
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
        ]);

        // 3. 勤怠ステータス作成（退勤済）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 4. 勤怠データ作成（出勤 09:00 / 退勤 18:00）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        // 5. 管理者ログイン
        $this->actingAs($admin);

        // 6. 不正データ送信（休憩開始19:00 / 終了19:30 → 退勤後）
        $formData = [
            'clock_in_at'  => '09:00',
            'clock_out_at' => '18:00',
            'note' => 'テスト備考',
            'breaks' => [
                'new' => [
                    'break_start_at' => '19:00',
                    'break_end_at'   => '19:30',
                ],
            ],
        ];

        // 7. 更新処理実行
        $response = $this->from(route('admin.attendance.detail', $attendance->id))
            ->put(route('admin.attendance.detail.update', $attendance->id), $formData);

        // 8. バリデーションエラーでリダイレクトされることを確認
        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        // 9. リダイレクト後、Blade上にメッセージが表示されることを確認
        $response = $this->followingRedirects()
            ->put(route('admin.attendance.detail.update', $attendance->id), $formData);

        $response->assertSee('休憩時間が不適切な値です');
    }

    #[Test]
    public function it_shows_error_when_break_end_is_after_clock_out_for_admin(): void
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
        ]);

        // 3. 勤怠ステータス作成（退勤済）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 4. 勤怠データ作成（出勤 09:00 / 退勤 18:00）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        // 5. 管理者としてログイン
        $this->actingAs($admin);

        // 6. 不正データ送信（休憩終了が退勤後）
        $formData = [
            'clock_in_at'  => '09:00',
            'clock_out_at' => '18:00',
            'note' => 'テスト',
            'breaks' => [
                'new' => [
                    'break_start_at' => '17:30',
                    'break_end_at'   => '19:30', // ← 退勤後の不正値
                ],
            ],
        ];

        // 7. 更新リクエスト送信
        $response = $this->from(route('admin.attendance.detail', $attendance->id))
            ->put(route('admin.attendance.detail.update', $attendance->id), $formData);

        // 8. ステータスとセッション確認
        $response->assertStatus(302);
        $response->assertSessionHasErrors();

        // 9. リダイレクト後のBladeにエラーメッセージが表示されるか確認
        $response = $this->followingRedirects()
            ->put(route('admin.attendance.detail.update', $attendance->id), $formData);

        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    #[Test]
    public function it_shows_error_when_note_is_empty_for_admin(): void
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
            'note' => '既存の備考',
        ]);

        // 5. 管理者としてログイン
        $this->actingAs($admin);

        // 6. 備考未入力のデータで更新リクエスト送信
        $formData = [
            'clock_in_at'  => '09:00',
            'clock_out_at' => '18:00',
            'note' => '', // ← 未入力
            'breaks' => [],
        ];

        $response = $this->from(route('admin.attendance.detail', $attendance->id))
            ->put(route('admin.attendance.detail.update', $attendance->id), $formData);

        // 7. ステータスとセッション確認
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['note']);

        // 8. リダイレクト後にBlade上のエラーメッセージを確認
        $response = $this->followingRedirects()
            ->put(route('admin.attendance.detail.update', $attendance->id), $formData);

        $response->assertSee('備考を記入してください');
    }
}
