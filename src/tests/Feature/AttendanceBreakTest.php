<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AttendanceBreakTest extends TestCase
{
    use refreshDatabase;

    #[Test]
    public function it_changes_status_to_breaking_after_pressing_break_button(): void
    {
        // 1. ステータスデータを作成
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '出勤中',
        ]);

        $statusBreaking = AttendanceStatus::factory()->create([
            'id' => 3,
            'name' => '休憩中',
        ]);

        // 2. 出勤中ユーザーを作成
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusWorking->id,
        ]);

        // 3. ログイン状態を再現
        $this->actingAs($user);

        // 4. 画面に「休憩入」ボタンが表示されていることを確認
        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('休憩入');

        // 5. 「休憩入」ボタン押下（POST /attendance/break/start）
        $response = $this->post(route('attendance.break.start'));

        // 6. DB上でステータスが「休憩中」(id=3) に更新されていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusBreaking->id,
        ]);

        // 7. リダイレクト後の画面で「休憩中」と表示されていることを確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('休憩中');
    }

    #[Test]
    public function it_displays_break_start_button_even_after_multiple_breaks(): void
    {
        // 1. ステータスデータ作成（出勤中・休憩中）
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '出勤中',
        ]);
        $statusBreaking = AttendanceStatus::factory()->create([
            'id' => 3,
            'name' => '休憩中',
        ]);

        // 2. 出勤中ユーザー作成
        $user = User::factory()->create();

        // 3. 出勤中勤怠データ作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusWorking->id,
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 休憩開始処理（POST /attendance/break/start）
        $response = $this->post(route('attendance.break.start'));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusBreaking->id,
        ]);

        // 6. 休憩終了処理（POST /attendance/break/end）
        $response = $this->post(route('attendance.break.end'));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusWorking->id,
        ]);

        // 7. 勤怠画面で再び「休憩入」ボタンが表示されていることを確認
        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('休憩入');
    }

    #[Test]
    public function it_changes_status_back_to_working_after_break_end(): void
    {
        // 1. ステータスデータを作成（出勤中／休憩中）
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '出勤中',
        ]);

        $statusBreaking = AttendanceStatus::factory()->create([
            'id' => 3,
            'name' => '休憩中',
        ]);

        // 2. 出勤中ユーザー作成
        $user = User::factory()->create();

        // 3. 勤怠データ作成（出勤中で開始）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusWorking->id,
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 「休憩入」処理を実行（POST /attendance/break/start）
        $response = $this->post(route('attendance.break.start'));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusBreaking->id,
        ]);

        // 6. 「休憩戻」ボタンが表示されていることを確認
        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('休憩戻');

        // 7. 「休憩戻」処理を実行（POST /attendance/break/end）
        $response = $this->post(route('attendance.break.end'));

        // 8. ステータスが「出勤中」に戻っていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusWorking->id,
        ]);

        // 9. 画面上で「出勤中」と表示されていることを確認
        $response = $this->followingRedirects()->get(route('attendance.index'));
        $response->assertSee('出勤中');
    }

    #[Test]
    public function it_displays_break_end_button_even_after_multiple_break_cycles(): void
    {
        // 1. ステータスデータ作成（出勤中／休憩中）
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '出勤中',
        ]);

        $statusBreaking = AttendanceStatus::factory()->create([
            'id' => 3,
            'name' => '休憩中',
        ]);

        // 2. 出勤中ユーザー作成
        $user = User::factory()->create();

        // 3. 勤怠データ作成（出勤中で開始）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusWorking->id,
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // === 1回目の休憩 ===
        // 5. 「休憩入」POST実行
        $this->post(route('attendance.break.start'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusBreaking->id,
        ]);

        // 6. 「休憩戻」POST実行
        $this->post(route('attendance.break.end'));

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusWorking->id,
        ]);

        // === 2回目の休憩 ===
        // 7. 再び「休憩入」POST実行
        $this->post(route('attendance.break.start'));

        // 8. ステータスが再度「休憩中」になっていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusBreaking->id,
        ]);

        // 9. 勤怠画面で「休憩戻」ボタンが表示されていることを確認
        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('休憩戻');
    }

    #[Test]
    public function it_displays_break_times_correctly_on_attendance_list(): void
    {
        // 1. ステータスデータを作成（出勤中 / 休憩中）
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '出勤中',
        ]);

        $statusBreaking = AttendanceStatus::factory()->create([
            'id' => 3,
            'name' => '休憩中',
        ]);

        // 2. 出勤中ユーザー作成
        $user = User::factory()->create();

        // 3. 勤怠データ作成（出勤中で開始）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusWorking->id,
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 休憩開始処理（POST /attendance/break/start）
        $breakStart = Carbon::now();
        Carbon::setTestNow($breakStart); // 時刻固定
        $this->post(route('attendance.break.start'));

        // 6. 休憩終了処理（POST /attendance/break/end）
        $breakEnd = Carbon::now()->addMinutes(30); // 0:30
        Carbon::setTestNow($breakEnd); // 時刻固定
        $this->post(route('attendance.break.end'));

        // 7. 休憩レコードがDBに正しく保存されているか確認
        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
            'break_start_at' => $breakStart->format('H:i'),
            'break_end_at' => $breakEnd->format('H:i'),
        ]);

        // 8. 勤怠一覧画面を取得
        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        // 9. Blade上に休憩時間が正しく表示されていることを確認
        $response->assertSee('0:30');
    }
}
