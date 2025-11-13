<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AttendanceEndTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_changes_status_to_finished_after_pressing_end_button(): void
    {
        // 1. ステータスデータを作成（出勤中・退勤済）
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '出勤中',
        ]);

        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. 出勤中ユーザーを作成
        $user = User::factory()->create();

        // 3. 勤怠データ作成（出勤中で開始）
        $clock_in = Carbon::now()->addMinutes(-30)->format('H:i');
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => $clock_in,
            'clock_out_at' => null,
            'attendance_status_id' => $statusWorking->id,
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 「退勤」ボタンが表示されていることを確認
        $response = $this->get(route('attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('退勤');

        // 6. 「退勤」処理を実行（POST /attendance/end）
        $response = $this->post(route('attendance.end'));

        // 7. データベースでステータスが「退勤済」になっていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusFinished->id,
        ]);

        // 8. リダイレクト後の画面に「退勤済」と表示されていることを確認
        $response = $this->get(route('attendance.index'));
        $response->assertSee('退勤済');
    }

    #[Test]
    public function it_displays_clock_out_time_on_attendance_list_after_end_process(): void
    {
        // 1. ステータスを定義
        $statusOff = AttendanceStatus::factory()->create([
            'id' => 1,
            'name' => '勤務外',
        ]);
        $statusWorking = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '出勤中',
        ]);
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. 勤務外ユーザーを作成
        $user = User::factory()->create();

        // 3. ログイン状態を再現
        $this->actingAs($user);

        // 4. 出勤処理を実行
        $clockIn = Carbon::now()->setTime(9, 0);
        Carbon::setTestNow($clockIn);
        $this->post(route('attendance.start'));
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $statusWorking->id,
        ]);

        // 5. 退勤処理を実行
        $clockOut = Carbon::now()->setTime(18, 0);
        Carbon::setTestNow($clockOut);
        $this->post(route('attendance.end'));

        // 6. DB上で退勤済になっていることを確認
        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_out_at' => $clockOut->format('H:i'),
        ]);

        // 7. 勤怠一覧画面を取得して退勤時刻が表示されているか確認
        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);
        $response->assertSee($clockOut->format('H:i'));
    }
}
