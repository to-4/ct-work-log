<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AttendanceStatus>
 */
class AttendanceStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id'   => $this->faker->numberBetween(1, 4), // ステータスID
            'name' => $this->faker->name(),              // ステータス名
        ];
    }
}
