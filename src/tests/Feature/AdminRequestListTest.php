<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Carbon\Carbon;

class AdminRequestListTest extends TestCase
{
    Use RefreshDatabase;

    #[Test]
    public function it_displays_all_pending_requests_for_admin(): void
    {
        // 1. 管理者ユーザー作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 2. 一般ユーザー作成
        $user1 = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
        ]);
        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'hanako@example.com',
        ]);
        $user3 = User::factory()->create([
            'name' => '鈴木次郎',
            'email' => 'jiro@example.com',
        ]);

        // 3. 勤怠ステータス（退勤済）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 4. 勤怠データ作成
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'working_minutes' => 540,
            'note' => '出勤時間修正',
            'is_pending_approval' => true,
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::now(),
            'clock_in_at' => '10:00',
            'clock_out_at' => '19:00',
            'working_minutes' => 540,
            'note' => '退勤時間修正',
            'is_pending_approval' => true,
        ]);
        $attendance3 = Attendance::factory()->create([
            'user_id' => $user3->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::now(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '17:00',
            'working_minutes' => 480,
            'note' => '誤操作修正',
        ]);

        // 5. 修正申請（承認待ち）作成
        AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'requested_at' => Carbon::now(),
            'approved_at' => null, // 必要
        ]);
        AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'requested_at' => Carbon::now(),
            'approved_at' => null, // 必要
        ]);

        // 6. 別の状態（承認済）データも用意
        AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance3->id,
            'requested_at' => Carbon::now(),
            'approved_by' => $admin->id,
            'approved_at' => Carbon::now(),
        ]);

        // 7. 管理者ログイン
        $this->actingAs($admin);

        // 8. 修正申請一覧ページにアクセス
        $response = $this->get(route('request.list'));
        $response->assertStatus(200);

        // 9. 承認待ちデータが全て表示されていることを確認
        $response->assertSee('出勤時間修正');
        $response->assertSee('退勤時間修正');

        // 10. 承認済みデータが含まれていないことを確認
        $response->assertDontSee('誤操作修正');

        // 11. ユーザー名が一覧上に表示されていること
        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
    }

    #[Test]
    public function it_displays_all_approved_requests_for_admin(): void
    {
        // 1. 管理者ユーザー作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        // 2. 一般ユーザー作成
        $user1 = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'taro@example.com',
        ]);
        $user2 = User::factory()->create([
            'name' => '佐藤花子',
            'email' => 'hanako@example.com',
        ]);
        $user3 = User::factory()->create([
            'name' => '鈴木次郎',
            'email' => 'jiro@example.com',
        ]);

        // 3. 勤怠ステータス（退勤済）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 4. 勤怠データ作成
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user1->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'working_minutes' => 540,
            'note' => '出勤時間修正',
            'is_pending_approval' => false,
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user2->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '10:00',
            'clock_out_at' => '19:00',
            'working_minutes' => 540,
            'note' => '退勤時間修正',
            'is_pending_approval' => false,
        ]);
        $attendance3 = Attendance::factory()->create([
            'user_id' => $user3->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '17:00',
            'working_minutes' => 480,
            'note' => '承認待ち修正',
            'is_pending_approval' => true,
        ]);

        // 5. 承認済み申請データを作成
        AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance1->id,
            'requested_at' => Carbon::now()->subDay(),
            'approved_by' => $admin->id,
            'approved_at' => Carbon::now(),
        ]);
        AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance2->id,
            'requested_at' => Carbon::now()->subHours(3),
            'approved_by' => $admin->id,
            'approved_at' => Carbon::now(),
        ]);

        // 6. 承認待ちデータを作成（除外対象）
        AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance3->id,
            'requested_at' => Carbon::now(),
            'approved_by' => null,
            'approved_at' => null,
        ]);

        // 7. 管理者でログイン
        $this->actingAs($admin);

        // 8. 修正申請一覧ページ（承認済みタブ）にアクセス
        $response = $this->get(route('request.list', ['status' => 'approved']));
        $response->assertStatus(200);

        // 9. 承認済みデータ（2件）が表示されていることを確認
        $response->assertSee('出勤時間修正');
        $response->assertSee('退勤時間修正');

        // 10. 承認待ちデータが含まれていないことを確認
        $response->assertDontSee('承認待ち修正');

        // 11. 各ユーザー名が表示されていることを確認
        $response->assertSee('山田太郎');
        $response->assertSee('佐藤花子');
        $response->assertDontSee('鈴木次郎');
    }

    #[Test]
    public function it_displays_request_detail_correctly_for_admin(): void
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

        // 3. 勤怠ステータス作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 4. 勤怠データ作成（出勤 9:00, 退勤 18:00, 休憩 12:00-13:00）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'working_minutes' => 480,
            'break_minutes' => 0,
            'note' => '出勤時間修正',
            'is_pending_approval' => true,
        ]);

        // 5. 修正申請データ作成
        $request = AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_at' => Carbon::now(),
            'approved_by' => null,
            'approved_at' => null,
        ]);

        // 6. 管理者でログイン
        $this->actingAs($admin);

        // 7. 修正申請詳細画面へアクセス
        $response = $this->get(route('admin.request.approve', $request->id));

        // 8. ステータス確認
        $response->assertStatus(200);

        // 9. 申請内容が正しく表示されているか確認
        $response->assertSee('山田太郎');
        $response->assertSee(Carbon::today()->format('Y年'));
        $response->assertSee(Carbon::today()->format('n月'));
        $response->assertSee(Carbon::today()->format('j日'));
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('出勤時間修正');
    }

    #[Test]
    public function it_approves_a_correction_request_and_updates_attendance(): void
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

        // 3. 勤怠ステータス作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 4. 勤怠データ作成（承認前）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'working_minutes' => 480,
            'break_minutes' => 0,
            'note' => '退勤時刻修正',
            'is_pending_approval' => true,
        ]);

        // 5. 修正申請作成（未承認）
        $request = AttendanceCorrectionRequest::factory()->create([
            'attendance_id' => $attendance->id,
            'requested_at' => Carbon::now(),
            'approved_at' => null,
            'approved_by' => null,
        ]);

        // 6. 管理者ログイン
        $this->actingAs($admin);

        // 7. 承認リクエスト送信（PUT）
        $response = $this->put(route('admin.request.approve.update', $request->id));

        // 8. ステータス確認
        $response->assertStatus(302); // リダイレクト（成功時）

        // 9. データ更新確認
        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $request->id,
            'approved_by' => $admin->id,
        ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'is_pending_approval' => false,
        ]);
    }
}
