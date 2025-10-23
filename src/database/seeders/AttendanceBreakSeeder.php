<?php

namespace Database\Seeders;

use App\Models\AttendanceBreak;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceBreakSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // id や created_at は、Model::create で対応
        $contents = [
            [
                'attendance_id' => 1,
                'break_start_at' => '2025-09-01 12:00:00',
                'break_end_at' => '2025-09-01 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 2,
                'break_start_at' => '2025-09-02 12:00:00',
                'break_end_at' => '2025-09-02 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 3,
                'break_start_at' => '2025-09-03 12:00:00',
                'break_end_at' => '2025-09-03 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 4,
                'break_start_at' => '2025-09-04 12:00:00',
                'break_end_at' => '2025-09-04 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 5,
                'break_start_at' => '2025-09-05 12:00:00',
                'break_end_at' => '2025-09-05 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 6,
                'break_start_at' => '2025-09-08 12:00:00',
                'break_end_at' => '2025-09-08 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 7,
                'break_start_at' => '2025-09-09 12:00:00',
                'break_end_at' => '2025-09-09 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 8,
                'break_start_at' => '2025-09-10 12:00:00',
                'break_end_at' => '2025-09-10 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 9,
                'break_start_at' => '2025-09-11 12:00:00',
                'break_end_at' => '2025-09-11 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 10,
                'break_start_at' => '2025-09-12 12:00:00',
                'break_end_at' => '2025-09-12 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 11,
                'break_start_at' => '2025-09-15 12:00:00',
                'break_end_at' => '2025-09-15 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 12,
                'break_start_at' => '2025-09-16 12:00:00',
                'break_end_at' => '2025-09-16 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 13,
                'break_start_at' => '2025-09-17 12:00:00',
                'break_end_at' => '2025-09-17 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 14,
                'break_start_at' => '2025-09-18 12:00:00',
                'break_end_at' => '2025-09-18 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 15,
                'break_start_at' => '2025-09-19 12:00:00',
                'break_end_at' => '2025-09-19 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 16,
                'break_start_at' => '2025-09-22 12:00:00',
                'break_end_at' => '2025-09-22 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 17,
                'break_start_at' => '2025-09-23 12:00:00',
                'break_end_at' => '2025-09-23 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 18,
                'break_start_at' => '2025-09-24 12:00:00',
                'break_end_at' => '2025-09-24 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 19,
                'break_start_at' => '2025-09-25 12:00:00',
                'break_end_at' => '2025-09-25 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 20,
                'break_start_at' => '2025-09-26 12:00:00',
                'break_end_at' => '2025-09-26 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 21,
                'break_start_at' => '2025-09-29 12:00:00',
                'break_end_at' => '2025-09-29 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 22,
                'break_start_at' => '2025-09-30 12:00:00',
                'break_end_at' => '2025-09-30 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 23,
                'break_start_at' => '2025-10-01 12:00:00',
                'break_end_at' => '2025-10-01 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 24,
                'break_start_at' => '2025-10-02 12:00:00',
                'break_end_at' => '2025-10-02 13:00:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 25,
                'break_start_at' => '2025-08-01 12:00:00',
                'break_end_at' => '2025-08-01 13:00:00',
                'break_minutes' => 60,
            ],
        ];

        foreach ($contents as $content) {
            AttendanceBreak::create($content);
        }
    }
}
