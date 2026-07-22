<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResourceFile>
 */
class ResourceFileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'res_id' => 9999999,
            'version' => 1,
            'source_version' => 1,
            'filename' => pathinfo(fake()->filePath(), PATHINFO_BASENAME),
            'is_preview' => 0,
            'is_hidden' => 0,
            'is_link' => 0,
        ];
    }
}
