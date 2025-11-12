<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceClockInTest extends TestCase
{
    Use RefreshDatabase;

    #[Test]
    public function clock_in_button_changes_status_to_working(): void
    {
        // 1. ステータスデータを作成
        $statusOn = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '勤務中',
        ]);

        // 2. 勤務外のユーザーを作成
        $user = User::factory()->create();

        // 3. ログイン状態を再現
        $this->actingAs($user);

        // 4. 画面に「出勤」ボタンが表示されていることを確認
        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('出勤');

        // 5. 出勤処理を実行（POSTで打刻）
        $response = $this->post(route('attendance.start'));

        // 6. DBが勤務中に更新されていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusOn->id,
        ]);

        // 7. 再表示画面に「勤務中」と表示されることを確認
        $response = $this->followingRedirects()->get(route('attendance.index'));
        $response->assertSee('勤務中');
    }

    #[Test]
    public function it_does_not_display_clock_in_button_for_finished_user(): void
    {
        // 1. ステータスデータを作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. ユーザー作成
        $user = User::factory()->create();

        // 3. 勤怠データを「退勤済」状態で作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusFinished->id,
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 勤怠打刻画面を取得
        $response = $this->get(route('attendance.index'));

        // 6. 正常に画面が表示されていることを確認
        $response->assertStatus(200);

        // 7. 「出勤」ボタンが表示されていないことを確認
        $response->assertDontSee('出勤');
    }

    #[Test]
    public function it_displays_clock_in_time_on_attendance_list_after_clock_in(): void
    {
        // 1. ステータスデータ作成（勤務中）
        $statusOn = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '勤務中',
        ]);

        // 2. 勤務外ユーザーを作成
        $user = User::factory()->create();

        // 3. ログイン状態を再現
        $this->actingAs($user);

        // 4. 出勤処理を実行（POST /attendance/start）
        $response = $this->post(route('attendance.start'));

        // 5. DBに出勤時刻が登録されていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusOn->id,
        ]);

        // 6. 勤怠一覧画面で出勤時刻が表示されていることを確認
        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);

        // 7. Blade上に「HH:MM」形式の出勤時刻があるか確認
        $now = Carbon::now()->format('H:i');
        $response->assertSee(substr($now, 0, 5));
    }
}
