<?php

namespace Database\Seeders;

use App\Models\Keyword;
use App\Models\Note;
use App\Models\Resource;
use App\Models\ResourceCategory;
use App\Models\ResourceFile;
use App\Models\ResourceKeyword;
use App\Models\ResourceRelation;
use App\Models\ResourceVersion;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Resource::factory()->count(50)->create(
            // ['author' => $user->login, 'disc_id' => ]
        )->each(function ($resource) {
            // set CATEGORY
            $resourceCategoryData = $this->generateResourceCategoryArray($resource->id);
            ResourceCategory::insert($resourceCategoryData);

            ResourceVersion::factory()->create(
                ['res_id' => $resource->id, 'author' => $resource->author]
            );
            ResourceFile::factory()->create(
                ['res_id' => $resource->id]
            );
            for ($i = 1; $i <= rand(1, 4); $i++) {
                ResourceRelation::factory()->create(
                    ['source_id' => $resource->id]
                );
            }
            $keywords = preg_split('#, ?#', $resource->keywords);
            foreach ($keywords as $key) {
                $keyword = Keyword::firstOrCreate(['key_name' => $key]);
                ResourceKeyword::create(['res_id' => $resource->id, 'key_id' => $keyword->key_id]);
            }
            Note::factory()->create(
                ['res_id' => $resource->id, 'author' => $resource->author]
            );
        });
    }

    /**
     * generates array for given $resourceId with random sub categories for every main category (2 levels)
     */
    private function generateResourceCategoryArray($resourceId): array
    {
        $categoryTree = json_decode(\AMSRedis::get('category', 'main'), true);
        $categories = [];
        foreach ($categoryTree as $category) {
            if (empty($category['cat_id']) || empty($category['children'])) {
                continue;
            }
            $categories[] = $category['cat_id'];
            $randomCatId = array_rand($category['children']);
            $categories[] = $randomCatId;
        }
        $resourceCategories = array_map(function ($value) use ($resourceId) {
            return ['res_id' => $resourceId, 'cat_id' => $value];
        }, $categories);

        return $resourceCategories;
    }
}
