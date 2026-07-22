<?php

namespace Database\Factories;

use App\Models\AMSUser;
use App\Models\Category;
use App\Models\Disc;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resource>
 */
class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $typeId = Category::where('parent_id', 10)->inRandomOrder()->limit(1)->get()->first()->cat_id;
        $licenseId = Category::where('parent_id', 31)->inRandomOrder()->limit(1)->get()->first()->cat_id;
        $user = AMSUser::inRandomOrder()->limit(1)->get()->first();
        $discId = Disc::inRandomOrder()->limit(1)->get()->first()->disc_id;
        $name = 'Resource '.fake()->city();
        $keywords = implode(', ', array_unique(array_map(function () {
            return fake()->firstName;
        }, range(0, 4))));

        return [
            'name' => $name,
            'native_name' => strtolower($name),
            'author' => $user->login,
            'date' => date('Y-m-d'),
            'file_path' => fake()->filePath(),
            'remarks' => fake()->text(50),
            'disc_id' => $discId,
            'keywords' => $keywords,
            'converted' => 1,
            'typeid' => $typeId,
            'licenseid' => $licenseId,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Resource $resource) {
            // add creating other things here?
            parent::configure();
        });
    }
}
