<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AttendanceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // created_at 等は、Model::create で対応
        $contents = [
            [
                'id' => AttendanceStatus::OFF_DUTY, // 1
                'name' => '勤務外',
            ],
            [
                'id' => AttendanceStatus::WORKING, // 2
                'name' => '出勤中',
            ],
            [
                'id' => AttendanceStatus::ON_BREAK, // 3
                'name' => '休憩中',
            ],
            [
                'id' => AttendanceStatus::COMPLETED, // 4
                'name' => '退勤済',
            ],
        ];

        foreach ($contents as $content) {
            AttendanceStatus::create($content);
        }
    }
}
