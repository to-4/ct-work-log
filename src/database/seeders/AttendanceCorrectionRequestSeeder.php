<?php

namespace Database\Seeders;

use App\Models\AttendanceCorrectionRequest;
use Illuminate\Database\Seeder;

class AttendanceCorrectionRequestSeeder extends Seeder
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
                'requested_at' => '2025-10-05 10:00:00',
                'approved_by' => 1,
                'approved_at' => '2025-10-05 18:00:00',
            ],
            [
                'attendance_id' => 2,
                'requested_at' => '2025-10-05 10:00:00',
                'approved_by' => 1,
                'approved_at' => '2025-10-05 18:00:00',
            ],
            [
                'attendance_id' => 3,
                'requested_at' => '2025-10-05 10:00:00',
                'approved_by' => 1,
                'approved_at' => '2025-10-05 18:00:00',
            ],
            [
                'attendance_id' => 4,
                'requested_at' => '2025-10-05 10:00:00',
                'approved_by' => 1,
                'approved_at' => '2025-10-05 18:00:00',
            ],
            [
                'attendance_id' => 5,
                'requested_at' => '2025-10-05 10:00:00',
                'approved_by' => 1,
                'approved_at' => '2025-10-05 18:00:00',
            ]
        ];

        foreach ($contents as $content) {
            AttendanceCorrectionRequest::create($content);
        }
    }
}
