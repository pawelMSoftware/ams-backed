<?php

namespace Database\Factories;

use App\Models\RelationDefinition;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResourceRelation>
 */
class ResourceRelationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $randomRelationDefinitionId = RelationDefinition::get()->pluck('id')->random();
        $resourceRelationDestId = Resource::inRandomOrder()->limit(1)->first()->id ?? 9999998;
        return [
            'source_id' => 9999999,
            'relation_id' => $randomRelationDefinitionId,
            'dest_id' => $resourceRelationDestId,
        ];
    }
}
