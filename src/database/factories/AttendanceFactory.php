<?php

namespace Database\Factories;

use App\Models\AttendanceStatus;
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
        $clockIn = (clone $workDate)->setTime(9, 0, 0);
        $clockOut = (clone $workDate)->setTime(18, 0, 0);

        // 勤務時間（9時間 = 540分）
        $workingMinutes = 540;

        return [
            'user_id' => User::inRandomOrder()->value('id') ?? 1,
            'work_date' => $workDate->format('Y-m-d'),
            'clock_in_at' => $clockIn->format('Y-m-d H:i:s'),
            'clock_out_at' => $clockOut->format('Y-m-d H:i:s'),
            'note' => $this->faker->optional()->sentence(3),
            'attendance_status_id' => AttendanceStatus::factory(),
            'working_minutes' => $workingMinutes,
            'break_minutes' => 0,
        ];
    }
}
