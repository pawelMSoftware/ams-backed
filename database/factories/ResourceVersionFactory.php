<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResourceVersion>
 */
class ResourceVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'res_id' => 9999999,
            'version' => 1,
            'comment' => fake()->text(20),
            'author' => 'admin',
        ];
    }
}
