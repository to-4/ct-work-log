<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceTimeDisplayTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function current_time_is_displayed_correctly_on_attendance_page(): void
    {
        // 1. 一般ユーザーを作成
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 2. テストユーザーとしてログイン
        $this->actingAs($user);

        // 3. 現在時刻（HH:MM）を取得
        $now = Carbon::now();
        $expectedTime = $now->format('H:i');

        // 4. 勤怠画面へアクセス
        $response = $this->get(route('attendance.index'));

        // 5. ステータス確認
        $response->assertStatus(200);

        // 6. Blade上に現在時刻が表示されていることを確認
        //   (1分の誤差を許容するため、前後1分もチェック)
        $timesToCheck = [
            $expectedTime,
            $now->clone()->subMinute()->format('H:i'),
            $now->clone()->addMinute()->format('H:i'),
        ];

        $found = false;
        foreach ($timesToCheck as $time) {
            if (str_contains($response->getContent(), $time)) {
                $found = true;
                break;
            }
        }

        $this->assertTrue(
            $found,
            "画面に現在時刻({$expectedTime})が表示されていません。"
        );
    }
}
