<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceListTest extends TestCase
{
    Use RefreshDatabase;

    #[Test]
    public function it_displays_all_attendance_records_for_logged_in_user(): void
    {
        // 1. ステータスマスタを作成
        $statusWorking = AttendanceStatus::factory()->create(['id' => 2, 'name' => '勤務中']);
        $statusFinished = AttendanceStatus::factory()->create(['id' => 4, 'name' => '退勤済']);

        // 2. 一般ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 他ユーザー（他人のデータが表示されないことを確認する用）
        $otherUser = User::factory()->create([
            'name' => '他人ユーザー',
            'email' => 'other@example.com',
        ]);

        // 4. 勤怠データを作成（自分用 3件、他人用 1件）
        $today = Carbon::today();

        $myAttendances = [
            Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_status_id' => $statusFinished->id,
                'work_date' => $today->copy()->subDays(2),
                'clock_in_at' => $today->copy()->subDays(2)->setTime(9, 0),
                'clock_out_at' => $today->copy()->subDays(2)->setTime(18, 0),
            ]),
            Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_status_id' => $statusFinished->id,
                'work_date' => $today->copy()->subDay(),
                'clock_in_at' => $today->copy()->subDay()->setTime(9, 15),
                'clock_out_at' => $today->copy()->subDay()->setTime(17, 45),
            ]),
            Attendance::factory()->create([
                'user_id' => $user->id,
                'attendance_status_id' => $statusWorking->id,
                'work_date' => $today,
                'clock_in_at' => $today->copy()->setTime(9, 5),
                'clock_out_at' => null,
            ]),
        ];

        // 他人の勤怠データ
        Attendance::factory()->create([
            'user_id' => $otherUser->id,
            'attendance_status_id' => $statusWorking->id,
            'work_date' => $today,
            'clock_in_at' => $today->copy()->setTime(8, 50),
        ]);

        // 5. ログイン状態を再現
        $this->actingAs($user);

        // 6. 勤怠一覧ページへアクセス
        $response = $this->get(route('attendance.list'));

        // 7. ステータスコード200を確認
        $response->assertStatus(200);

        // 8. 自分の勤怠データ（3件）が表示されていることを確認
        foreach ($myAttendances as $attendance) {
            $response->assertSee($attendance->work_date->format('m/d'));
            if ($attendance->clock_in_at) {
                $response->assertSee($attendance->clock_in_at);
            }
            if ($attendance->clock_out_at) {
                $response->assertSee($attendance->clock_out_at);
            }
        }

        // 9. 他ユーザーの勤怠データが表示されていないことを確認
        $response->assertDontSee($today->copy()->setTime(8, 50)->format('H:i'));
    }

    #[Test]
    public function it_displays_current_month_on_attendance_list(): void
    {
        // 1. Carbon の現在日時を固定
        $fixedDate = Carbon::create(2025, 11, 11, 9, 0, 0);
        Carbon::setTestNow($fixedDate);

        // 2. 一般ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. ログイン状態を再現
        $this->actingAs($user);

        // 4. 勤怠一覧ページへアクセス
        $response = $this->get(route('attendance.list'));

        // 5. ステータスコード200を確認
        $response->assertStatus(200);

        // 6. 現在の月が表示されていることを確認
        // Blade 側では "{{ $currentMonth->format('Y年n月') }}" のように表示される想定
        $response->assertSee($fixedDate->format('Y/m'));

        // Carbon固定解除
        Carbon::setTestNow();
    }

    #[Test]
    public function it_displays_previous_month_records_when_previous_button_is_clicked(): void
    {
        // 1. ステータスマスタを作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. 一般ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 勤怠データ作成
        // 現在月（2025-11）のデータ
        $today = Carbon::create(2025, 11, 11);
        Carbon::setTestNow($today);
        $currentMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => $today->copy()->setDay(5), // 2025-11-05
            'clock_in_at' => '09:00',
            'clock_out_at' => '17:00',
            'working_minutes' => 480,
        ]);

        // 前月（2025-10）のデータ
        $previousMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => $today->copy()->subMonth()->setDay(10), // 2025-10-10
            'clock_in_at' => '09:00',
            'clock_out_at' => '17:00',
            'working_minutes' => 480,
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 勤怠一覧画面（当月）にアクセス
        $response = $this->get(route('attendance.list', ['month' => $today->format('Y-m')]));
        $response->assertStatus(200);
        $response->assertSee('2025/11'); // 表示月確認
        $response->assertSee('11/05');   // 当月データ確認
        $response->assertDontSee('10/10'); // 前月データはまだ非表示

        // 6. 「前月」ボタン押下を再現（GET /attendance.list?month=2025-10）
        $response = $this->get(route('attendance.list', ['month' => $today->copy()->subMonth()->format('Y-m')]));
        $response->assertStatus(200);

        // 7. 前月のデータが表示されていることを確認
        $response->assertSee('2025/10');
        $response->assertSee('10/10');
        $response->assertDontSee('11/05');

        // Carbon固定解除
        Carbon::setTestNow();
    }

    #[Test]
    public function it_displays_next_month_records_when_next_button_is_clicked(): void
    {
        // 1. ステータスマスタを作成（退勤済のみ使用）
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. 一般ユーザーを作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 現在月・翌月の勤怠データを作成
        $baseDate = Carbon::create(2025, 11, 11);
        Carbon::setTestNow($baseDate);

        // 現在月（11月）のデータ
        $currentMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => $baseDate->copy()->setDay(5), // 2025-11-05
        ]);

        // 翌月（12月）のデータ
        $nextMonthAttendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => $baseDate->copy()->addMonth()->setDay(7), // 2025-12-07
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 現在月（11月）の勤怠一覧を開く
        $response = $this->get(route('attendance.list', ['month' => $baseDate->format('Y-m')]));
        $response->assertStatus(200);
        $response->assertSee('2025/11');
        $response->assertSee('11/05');  // 当月データが表示
        $response->assertDontSee('12/07'); // 翌月データは非表示

        // 6. 翌月ボタン押下を再現（GET /attendance.list?month=2025-12）
        $response = $this->get(route('attendance.list', ['month' => $baseDate->copy()->addMonth()->format('Y-m')]));
        $response->assertStatus(200);

        // 7. 翌月のデータが表示され、当月データが非表示であることを確認
        $response->assertSee('2025/12');
        $response->assertSee('12/07');
        $response->assertDontSee('11/05');

        // Carbon固定解除
        Carbon::setTestNow();
    }

    #[Test]
    public function it_transitions_to_attendance_detail_page_when_detail_button_is_clicked(): void
    {
        // 1. ステータスマスタ作成
        $statusFinished = AttendanceStatus::factory()->create([
            'id' => 4,
            'name' => '退勤済',
        ]);

        // 2. 一般ユーザー作成
        $user = User::factory()->create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);

        // 3. 勤怠データ作成（本日分）
        $attendance = Attendance::factory()->create([
            'user_id' => $user->id,
            'attendance_status_id' => $statusFinished->id,
            'work_date' => Carbon::today(),
            'clock_in_at' => Carbon::now()->setTime(9, 0)->format('h:i'),
            'clock_out_at' => Carbon::now()->setTime(18, 0)->format('h:i'),
        ]);

        // 4. ログイン状態を再現
        $this->actingAs($user);

        // 5. 勤怠一覧ページへアクセス
        $response = $this->get(route('attendance.list'));
        $response->assertStatus(200);
        $response->assertSee('詳細');

        // 6. 「詳細」ボタン押下（詳細ページへの遷移を再現）
        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));

        // 7. 正常遷移（ステータスコード200）
        $response->assertStatus(200);

        // 8. 勤怠詳細画面に当該日付が表示されていることを確認
        $response->assertSee($attendance->work_date->format('Y年'));
        $response->assertSee($attendance->work_date->format('m月d日'));
        $response->assertSee($attendance->clock_in_at);
        $response->assertSee($attendance->clock_out_at);
    }
}
