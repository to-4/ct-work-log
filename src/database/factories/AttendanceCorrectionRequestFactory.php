<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceCorrectionRequest>
 */
class AttendanceCorrectionRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // ランダムな勤怠データを関連付ける
        $attendanceId = Attendance::inRandomOrder()->value('id') ?? 1;

        // 承認者（管理者）ユーザーIDを取得（存在しない場合は1）
        $approvedBy = User::where('is_admin', true)->inRandomOrder()->value('id') ?? 1;

        // Fakerによるダミー日時生成
        $requestedAt = $this->faker->dateTimeBetween('-7 days', 'now');
        $approvedAt  = (clone $requestedAt)->modify('+8 hours');

        return [
            'attendance_id' => $attendanceId,
            'requested_at' => $requestedAt->format('Y-m-d H:i:s'),
            'approved_by' => $approvedBy,
            'approved_at' => $approvedAt->format('Y-m-d H:i:s'),
        ];
    }}
