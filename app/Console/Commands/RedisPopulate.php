<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RedisPopulate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:populate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private array $categoriesCount = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // return categories grouped by parent
        $groupedCategories = [];
        $this->categoriesCount = DB::table('resources_categories')
            ->selectRaw('cat_id, COUNT(res_id) AS counter')->groupBy('cat_id')->get()
            ->groupBy('cat_id')->toArray();

        $groupedCategories = $this->getGroupedCategories($groupedCategories);

        $parents = [];
        $categoriesTree = $this->makeCategoriesTree($groupedCategories, 0, $parents);
        \AMSRedis::set('category', 'all', serialize($categoriesTree));

        $categoriesMain = [];
        foreach ($categoriesTree as $categoryTreeLevel0) {
            $categoryOnly = $categoryTreeLevel0;
            unset($categoryOnly['children']);
            $categoriesMain[$categoryOnly['cat_id']] = $categoryOnly;
            $_categoryMainCategory = [];
            foreach ($categoryTreeLevel0['children'] as $categoryTreeLevel1) {
                $categoryOnly2 = $categoryTreeLevel1;
                unset($categoryOnly2['children']);
                $_categoryMainCategory[$categoryOnly2['cat_id']] = $categoryOnly2;
                \AMSRedis::set('category', $categoryTreeLevel1['cat_id'], $categoryTreeLevel1);
                $this->cacheDeepCategories($categoryTreeLevel1);
            }
            $categoriesMain[$categoryOnly['cat_id']]['children'] = $_categoryMainCategory;
        }
        \AMSRedis::set('category', 'main', $categoriesMain);

        $this->info('OK');

        return parent::SUCCESS;
    }

    private function cacheDeepCategories(array $categoryTreePart): void
    {
        if (! empty($categoryTreePart['children'])) {
            foreach ($categoryTreePart['children'] as $child) {
                if (! empty($child['children'])) {
                    $this->cacheDeepCategories($child);
                }
                \AMSRedis::set('category', $child['cat_id'], $child);
            }
        }
    }

    private function makeCategoriesTree(array &$categories, int $parentId, array &$parents): array
    {
        $tree = [];
        if (isset($categories[$parentId])) {
            $children = $categories[$parentId];
            foreach ($children as $category) {
                $item = [];
                $item['cat_id'] = $category['cat_id'];
                $item['name'] = $category['name'];
                $item['parents'] = $parents;
                $newParents = array_merge($parents);
                $newParents[] = $item['cat_id'];
                $children = $this->makeCategoriesTree($categories, $category['cat_id'], $newParents);
                $item['children'] = $children;
                $count = 0;
                foreach ($item['children'] as $node) {
                    $count += $node['count'];
                }
                $item['count'] = $this->getCategoriesCount($category['cat_id']) + $count;
                $tree[$item['cat_id']] = $item;
            }
        }

        return $tree;
    }

    private function getCategoriesCount($catId): int
    {
        if (isset($this->categoriesCount[$catId])) {
            return $this->categoriesCount[$catId][0]->counter;
        }

        return 0;
    }

    private function getGroupedCategories(array $groupedCategories): array
    {
        collect(Category::orderBy('sort')->orderBy('cat_name')->get())
            ->each(function ($item, $key) use (&$groupedCategories) {
                if (! isset($groupedCategories[$item->parent_id])) {
                    $groupedCategories[$item->parent_id] = [];
                }
                $groupedCategories[$item->parent_id][$item->cat_id] = [
                    'cat_id' => $item->cat_id,
                    'name' => $item->cat_name,
                    'parent_id' => $item->parent_id,
                    'sort' => $item->sort,
                    'count' => $this->categoriesCount[$item->cat_id][0]?->counter ?? 0,
                ];
            });

        return $groupedCategories;
    }
}
