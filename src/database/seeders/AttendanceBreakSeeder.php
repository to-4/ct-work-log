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
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 2,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 3,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 4,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 5,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 6,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 7,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 8,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 9,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 10,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 11,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 12,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 13,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 14,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 15,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 16,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 17,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 18,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 19,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 20,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 21,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 22,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 23,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 24,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
            [
                'attendance_id' => 25,
                'break_start_at' => '12:00',
                'break_end_at' => '13:00',
                'break_minutes' => 60,
            ],
        ];

        foreach ($contents as $content) {
            AttendanceBreak::create($content);
        }
    }
}
