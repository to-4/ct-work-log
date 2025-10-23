<?php

namespace Database\Seeders;

use App\Models\Attendance;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // id や created_at は、Model::create で対応
        $contents = [
            [
                'user_id' => 2,
                'work_date' => '2025-09-01',
                'clock_in_at' => '2025-09-01 09:00:00',
                'clock_out_at' => '2025-09-01 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-02',
                'clock_in_at' => '2025-09-02 09:00:00',
                'clock_out_at' => '2025-09-02 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-03',
                'clock_in_at' => '2025-09-03 09:00:00',
                'clock_out_at' => '2025-09-03 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-04',
                'clock_in_at' => '2025-09-04 09:00:00',
                'clock_out_at' => '2025-09-04 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-05',
                'clock_in_at' => '2025-09-05 09:00:00',
                'clock_out_at' => '2025-09-05 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-08',
                'clock_in_at' => '2025-09-08 09:00:00',
                'clock_out_at' => '2025-09-08 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-09',
                'clock_in_at' => '2025-09-09 09:00:00',
                'clock_out_at' => '2025-09-09 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-10',
                'clock_in_at' => '2025-09-10 09:00:00',
                'clock_out_at' => '2025-09-10 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-11',
                'clock_in_at' => '2025-09-11 09:00:00',
                'clock_out_at' => '2025-09-11 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-12',
                'clock_in_at' => '2025-09-12 09:00:00',
                'clock_out_at' => '2025-09-12 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-15',
                'clock_in_at' => '2025-09-15 09:00:00',
                'clock_out_at' => '2025-09-15 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-16',
                'clock_in_at' => '2025-09-16 09:00:00',
                'clock_out_at' => '2025-09-16 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-17',
                'clock_in_at' => '2025-09-17 09:00:00',
                'clock_out_at' => '2025-09-17 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-18',
                'clock_in_at' => '2025-09-18 09:00:00',
                'clock_out_at' => '2025-09-18 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-19',
                'clock_in_at' => '2025-09-19 09:00:00',
                'clock_out_at' => '2025-09-19 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-22',
                'clock_in_at' => '2025-09-22 09:00:00',
                'clock_out_at' => '2025-09-22 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-23',
                'clock_in_at' => '2025-09-23 09:00:00',
                'clock_out_at' => '2025-09-23 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-24',
                'clock_in_at' => '2025-09-24 09:00:00',
                'clock_out_at' => '2025-09-24 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-25',
                'clock_in_at' => '2025-09-25 09:00:00',
                'clock_out_at' => '2025-09-25 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-26',
                'clock_in_at' => '2025-09-26 09:00:00',
                'clock_out_at' => '2025-09-26 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-29',
                'clock_in_at' => '2025-09-29 09:00:00',
                'clock_out_at' => '2025-09-29 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-09-30',
                'clock_in_at' => '2025-09-30 09:00:00',
                'clock_out_at' => '2025-09-30 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-10-01',
                'clock_in_at' => '2025-10-01 09:00:00',
                'clock_out_at' => '2025-10-01 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-10-02',
                'clock_in_at' => '2025-10-02 09:00:00',
                'clock_out_at' => '2025-10-02 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ],
            [
                'user_id' => 2,
                'work_date' => '2025-08-01',
                'clock_in_at' => '2025-08-01 09:00:00',
                'clock_out_at' => '2025-08-01 18:00:00',
                'attendance_status_id' => 4,
                'note' => 'テスト備考',
                'working_minutes' => 480,
            ]
        ];

        foreach ($contents as $content) {
            Attendance::create($content);
        }
    }
}
