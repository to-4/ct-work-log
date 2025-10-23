<?php

namespace Database\Seeders;

use App\Models\AttendanceStatus;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AttendanceStatusSeeder::class,
            AttendanceSeeder::class,
            AttendanceCorrectionRequestSeeder::class,
        ]);
    }
}
