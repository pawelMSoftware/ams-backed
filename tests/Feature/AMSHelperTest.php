<?php

namespace Tests\Feature;

use Tests\AMSTestCase;

class AMSHelperTest extends AMSTestCase
{
    public function test_get_storage_for_disc_alias()
    {
        $this->assertEquals('cache3', \AMSHelper::getStorageNameForDiscAlias('/cache3/'));
        $this->assertEquals('cache3', \AMSHelper::getStorageNameForDiscAlias('/cache3'));
        $this->assertEquals('cache3', \AMSHelper::getStorageNameForDiscAlias('cache3/'));
    }

    public function test_get_flat_categories_array()
    {
        $jsonFilePath = base_path('database/fixtures/test_tree.json');
        $categoriesTree = json_decode(file_get_contents($jsonFilePath), true);
        $categoriesIds = [30879, 30881, 31014];
        $tree = \AMSHelper::getFlatCategoriesArray($categoriesTree, $categoriesIds);
        $this->assertEquals($categoriesIds, array_keys($tree));
        foreach ($categoriesIds as $categoryId) {
            $this->assertEquals($categoryId, $tree[$categoryId]['cat_id']);
        }
    }
}
