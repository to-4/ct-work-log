<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AttendanceUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_shows_error_when_clock_in_time_is_after_clock_out_time(): void
    {
        // 1. 勤務ステータス作成（退勤済）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. テスト用ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 勤怠データを作成（本日分）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->setTime(9, 0),
            'clock_out_at' => Carbon::now()->setTime(18, 0),
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 出勤時間を退勤時間より後に設定
        $formData = [
            'clock_in_at' => '19:00',
            'clock_out_at' => '18:00',
        ];

        // 6. 更新リクエスト送信（PUT）
        $response = $this->from(route('attendance.detail', $attendance->id))
            ->put(route('attendance.detail.update', $attendance->id), $formData);

        // 7. バリデーションエラーでリダイレクトされること
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['clock_in_at']);

        // 8. エラーメッセージを追跡して表示を確認
        $response = $this->followingRedirects()
            ->put(route('attendance.detail.update', $attendance->id), $formData);

        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    #[Test]
    public function it_shows_error_when_break_start_is_after_clock_out(): void
    {
        // 1. ステータスを作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. テスト用ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 勤怠データを作成（出勤9:00 / 退勤18:00）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->setTime(9, 0),
            'clock_out_at' => Carbon::now()->setTime(18, 0),
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 不正データ（休憩開始19:00）を送信
        $formData = [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'note' => 'テスト',
            'breaks' => [
                'new' => [
                    'break_start_at' => '19:00', // ← 退勤後の不正データ
                    'break_end_at' => '19:30',
                ],
            ],
        ];

        // 6. 更新処理実行
        $response = $this->from(route('attendance.detail', $attendance->id))
            ->put(route('attendance.detail.update', $attendance->id), $formData);

        // 7. バリデーションエラーでリダイレクトされることを確認
        $response->assertStatus(302);
        // $response->assertSessionHasErrors(['break_start_at']);

        // 8. Blade上に「休憩時間が不適切な値です」が表示されることを確認
        $response = $this->followingRedirects()
            ->put(route('attendance.detail.update', $attendance->id), $formData);

        $response->assertSee('休憩時間が不適切な値です');
    }

    #[Test]
    public function it_shows_error_when_break_end_is_after_clock_out(): void
    {
        // 1. ステータスを作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. テスト用ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 勤怠データを作成（出勤9:00 / 退勤18:00）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->setTime(9, 0),
            'clock_out_at' => Carbon::now()->setTime(18, 0),
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 不正データ（休憩終了19:30）を送信
        $formData = [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'note' => 'テスト',
            'breaks' => [
                'new' => [
                    'break_start_at' => '17:30',
                    'break_end_at' => '19:30', // ← 退勤後の不正データ
                ],
            ],
        ];

        // 6. 更新リクエスト送信
        $response = $this->from(route('attendance.detail', $attendance->id))
            ->put(route('attendance.detail.update', $attendance->id), $formData);

        // 7. バリデーションエラーでリダイレクトされることを確認
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['breaks.new.break_end_at']);

        // 8. エラーメッセージ表示確認
        $response = $this->followingRedirects()
            ->put(route('attendance.detail.update', $attendance->id), $formData);

        $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

    #[Test]
    public function it_shows_error_when_note_is_empty(): void
    {
        // 1. ステータスを作成（退勤済）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. テスト用ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 勤怠データを作成（出勤9:00 / 退勤18:00）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->setTime(9, 0),
            'clock_out_at' => Carbon::now()->setTime(18, 0),
            'note' => 'テストメモ',
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 備考を空欄にして更新を送信
        $formData = [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'note' => '', // 未入力
        ];

        // 6. 更新リクエスト送信
        $response = $this->from(route('attendance.detail', $attendance->id))
            ->put(route('attendance.detail.update', $attendance->id), $formData);

        // 7. バリデーションエラー確認（リダイレクト発生）
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['note']);

        // 8. エラーメッセージ表示確認
        $response = $this->followingRedirects()
            ->put(route('attendance.detail.update', $attendance->id), $formData);

        $response->assertSee('備考を記入してください');
    }

    #[Test]
    public function it_creates_correction_request_and_displays_in_admin_screens(): void
    {
        // 1 管理者・一般ユーザー作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 2. 勤怠ステータス・勤怠データ作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        /** @var Attendance @attendance */
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
            'note' => '修正前のメモ',
        ]);

        // 3. 一般ユーザーでログインし、修正申請送信
        $this->actingAs($user);

        $formData = [
            'clock_in_at' => '09:30',
            'clock_out_at' => '18:10',
            'note' => '修正申請テスト',
        ];

        $response = $this->put(route('attendance.detail.update', $attendance->id), $formData);

        $response->assertStatus(302); // リダイレクトOK

        // 4. 修正申請レコードが生成されていることを確認
        $this->assertDatabaseHas('attendance_correction_requests', [
            'attendance_id' => $attendance->id,
        ]);

        // 5. 管理者でログインして申請一覧画面へ
        $this->actingAs($admin);

        $listResponse = $this->get(route('request.list'));
        $listResponse->assertStatus(200);
        $listResponse->assertSee('修正申請テスト'); // 申請理由が表示

        // 6. 承認画面（個別詳細）でも表示されていることを確認
        $requestId = $attendance->attendanceCorrectionRequests->first()->id;
        $approveResponse = $this->get(route('admin.request.approve', ['attendance_correct_request_id' => $requestId]));
        $approveResponse->assertStatus(200);
        $approveResponse->assertSee('修正申請テスト');
    }

    #[Test]
    public function it_displays_logged_in_users_pending_requests_on_request_list(): void
    {
        // 1. 勤怠ステータスを作成（退勤済）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. 一般ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 勤怠データを作成
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today()->subDay(),
            'clock_in_at' => '10:00:00',
            'clock_out_at' => '19:00:00',
        ]);
        $attendance3 = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today()->subDay(2),
            'clock_in_at' => '10:00:00',
            'clock_out_at' => '19:00:00',
        ]);

        // 4. ユーザーでログイン
        $this->actingAs($user);

        // 5. 勤怠修正を2件申請
        $formData1 = [
            'clock_in_at' => '09:30',
            'clock_out_at' => '18:10',
            'note' => '修正申請テスト1',
        ];

        $response = $this->put(route('attendance.detail.update', $attendance1->id), $formData1);
        $response->assertStatus(302); // リダイレクトOK

        $formData2 = [
            'clock_in_at' => '09:30',
            'clock_out_at' => '18:10',
            'note' => '修正申請テスト2',
        ];
        $response = $this->put(route('attendance.detail.update', $attendance2->id), $formData2);
        $response->assertStatus(302); // リダイレクトOK

        // 6. 申請一覧ページへアクセス
        $response = $this->get(route('request.list'));

        // 7. ステータスコード確認
        $response->assertStatus(200);

        // 8. ログインユーザー自身の申請が全て表示されていること
        $response->assertSee(Carbon::today()->format('Y/m/d'));
        $response->assertSee(Carbon::today()->subDay()->format('Y/m/d'));
        $response->assertDontSee(Carbon::today()->subDay(2)->format('Y/m/d'));

    }

    #[Test]
    public function it_displays_only_approved_requests_in_the_approved_list(): void
    {
        // 1. 管理者・一般ユーザー作成
        $admin = User::factory()->create([
            'name' => '管理者ユーザー',
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 2. 勤怠ステータスと勤怠データを作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        /** @var Attendance $attendance1 */
        $attendance1 = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);
        /** @var Attendance $attendance2 */
        $attendance2 = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today()->subDay(),
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);

        // 4. ユーザーでログイン
        $this->actingAs($user);

        // 5. 勤怠修正を2件申請
        $formData1 = [
            'clock_in_at' => '09:30',
            'clock_out_at' => '18:10',
            'note' => '修正申請テスト1',
        ];

        $response = $this->put(route('attendance.detail.update', $attendance1->id), $formData1);
        $response->assertStatus(302); // リダイレクトOK

        $formData2 = [
            'clock_in_at' => '09:30',
            'clock_out_at' => '18:10',
            'note' => '修正申請テスト2',
        ];
        $response = $this->put(route('attendance.detail.update', $attendance2->id), $formData2);
        $response->assertStatus(302); // リダイレクトOK

        // 6. 管理者でログイン
        $this->actingAs($admin);

        // 7. 管理者が承認処理を行う
        $attendance1->load('attendanceCorrectionRequests');
        $attendance2->load('attendanceCorrectionRequests');

        $requestId1 = $attendance1->attendanceCorrectionRequests->first()->id;
        $response = $this->put(route(
            'admin.request.approve.update',
            $requestId1
        ));
        $response->assertStatus(302); // リダイレクトOK

        $requestId2 = $attendance2->attendanceCorrectionRequests->first()->id;
        $response = $this->put(route(
            'admin.request.approve.update',
            $requestId2
        ));
        $response->assertStatus(302); // リダイレクトOK

        // 8. ログイン状態を再現
        $this->actingAs($user);

        // 9. 申請一覧ページへアクセス
        $response = $this->get(route('request.list', ['status' => 'approved']));

        // 10. ステータスコード確認
        $response->assertStatus(200);

        // 11. ログインユーザー自身の申請が全て表示されていること
        $response->assertSee(Carbon::today()->format('Y/m/d'));
        $response->assertSee(Carbon::today()->subDay()->format('Y/m/d'));
    }

    #[Test]
    public function it_redirects_to_attendance_detail_when_pressing_detail_button_from_request_list(): void
    {
        // 1. ユーザーと勤怠データ準備
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
        ]);

        // 2. ログイン状態を再現
        $this->actingAs($user);

        // 3. 勤怠詳細更新（修正申請を想定）
        $formData = [
            'clock_in_at' => '09:30',
            'clock_out_at' => '18:30',
            'note' => '修正申請テスト',
        ];
        $response = $this->put(route('attendance.detail.update', $attendance->id), $formData);
        $response->assertStatus(302); // 更新後リダイレクト

        // 4. 申請一覧ページを開く
        $response = $this->get(route('request.list'));
        $response->assertStatus(200);
        $response->assertSee('詳細'); // 一覧上に「詳細」ボタンが存在

        // 4.1 「詳細」ボタンのリンク先が正しいことを確認
        $expectedUrl = route('attendance.detail', $attendance->id);
        $response->assertSee("href=\"{$expectedUrl}\"", false);

        // 5. 「詳細」ボタン押下 → 勤怠詳細ページ遷移を確認
        $response = $this->get(route('attendance.detail', $attendance->id));

        // 6. 勤怠詳細ページが正しく表示されるか確認
        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');       // ページタイトル
        $response->assertSee('テスト太郎');     // ユーザー名
        $response->assertSee('09:30');         // 修正後の出勤時刻
        $response->assertSee('18:30');         // 修正後の退勤時刻
        $response->assertSee('修正申請テスト'); // 備考
    }
}
