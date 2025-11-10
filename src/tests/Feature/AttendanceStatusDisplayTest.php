<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceStatusDisplayTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function test_attendance_status_off_is_displayed_correctly(): void
    {
        // 1. ステータスデータを作成（ID=1 が「勤務外」）
        $status = AttendanceStatus::factory()->create([
            'id' => 1,
            'name' => '勤務外',
        ]);

        // 2. ユーザー作成
        $user = User::factory()->create();

        // 3. 勤務外の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $status->id,
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 勤怠画面にアクセス
        $response = $this->get(route('attendance.index'));

        // 6. ステータス「勤務外」が画面に表示されているか確認
        $response->assertStatus(200)
                ->assertSee('勤務外');
    }

    #[Test]
    public function test_attendance_status_working_is_displayed_correctly(): void
    {
        // 1. ステータスデータを作成（ID=2 が「出勤中」）
        $status = AttendanceStatus::factory()->create([
            'id' => 2,
            'name' => '出勤中',
        ]);

        // 2. ユーザー作成
        $user = User::factory()->create();

        // 3. 出勤中の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $status->id,
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 勤怠画面にアクセス
        $response = $this->get(route('attendance.index'));

        // 6. ステータス「出勤中」が画面に表示されているか確認
        $response->assertStatus(200)
            ->assertSee('出勤中');
    }

    #[Test]
    public function test_attendance_status_on_break_is_displayed_correctly(): void
    {
        // 1. ステータスデータを作成（ID=3 が「休憩中」）
        $status = AttendanceStatus::factory()->create([
            'id' => 3,
            'name' => '休憩中',
        ]);

        // 2. ユーザー作成
        $user = User::factory()->create();

        // 3. 休憩中の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $status->id,
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 勤怠画面にアクセス
        $response = $this->get(route('attendance.index'));

        // 6. ステータス「休憩中」が画面に表示されているか確認
        $response->assertStatus(200)
            ->assertSee('休憩中');
    }

    #[Test]
    public function test_attendance_status_completed_is_displayed_correctly(): void
    {
        // 1. ステータスデータを作成（ID=4 が「退勤済」）
        $status = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. ユーザー作成
        $user = User::factory()->create();

        // 3. 退勤済の勤怠データを作成
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'attendance_status_id' => $status->id,
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 勤怠画面にアクセス
        $response = $this->get(route('attendance.index'));

        // 6. ステータス「退勤済」が画面に表示されているか確認
        $response->assertStatus(200)
            ->assertSee('退勤済');
    }
}
