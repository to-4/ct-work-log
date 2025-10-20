<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // id や created_at は、Model::create で対応
        $contents = [
            [
                'id' => 1,
                'name' => '管理者',
                'email' => 'admin@coachtech.com',
                'password' => Hash::make('pass'),
                'is_admin' => true,
            ],
            [
                'id' => 2,
                'name' => '西　怜奈',
                'email' => 'reina.n@coachtech.com',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
            [
                'id' => 3,
                'name' => '山田　太郎',
                'email' => 'taro.y@coachtech.com',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
            [
                'id' => 4,
                'name' => '増田　一世',
                'email' => 'issei.m@coachtech.com',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
            [
                'id' => 5,
                'name' => '山本　敬吉',
                'email' => 'keikichi.y@coachtech.com',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
            [
                'id' => 6,
                'name' => '秋田　朋美',
                'email' => 'tomomi.a@coachtech.com',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
            [
                'id' => 7,
                'name' => '中西　教夫',
                'email' => 'norio.n@coachtech.com',
                'password' => Hash::make('password'),
                'is_admin' => false,
            ],
        ];

        foreach ($contents as $content) {
            User::create($content);
        }
    }
}
