<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Fakerで基準となる日付を生成
        $workDate = $this->faker->dateTimeBetween('-1 month', 'now');

        // 出勤・退勤時刻（同一日の中で整合性を保つ）
        $clockIn  = (clone $workDate)->setTime(9, 0, 0);
        $clockOut = (clone $workDate)->setTime(18, 0, 0);

        // 休憩1・2の時間
        $break1Start = (clone $workDate)->setTime(12, 0, 0);
        $break1End   = (clone $workDate)->setTime(13, 0, 0);
        $break2Start = null; // 任意で null
        $break2End   = null;

        // 勤務時間（8時間 = 480分）
        $workingMinutes = 480;

        return [
            'user_id' => User::inRandomOrder()->value('id') ?? 1,
            'work_date' => $workDate->format('Y-m-d'),
            'clock_in_at' => $clockIn->format('Y-m-d H:i:s'),
            'clock_out_at' => $clockOut->format('Y-m-d H:i:s'),
            'break1_start_at' => $break1Start->format('Y-m-d H:i:s'),
            'break1_end_at' => $break1End->format('Y-m-d H:i:s'),
            'break2_start_at' => $break2Start,
            'break2_end_at' => $break2End,
            'note' => $this->faker->optional()->sentence(3),
            'working_minutes' => $workingMinutes,
        ];
    }
}
