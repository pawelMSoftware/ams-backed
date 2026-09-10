<?php

namespace App\AMS;

class AMSHelper
{
    public function getStorageNameForDiscAlias(string $filesAlias): string
    {
        return trim($filesAlias, '/\\');
    }

    /**
     * returns flat array with series of categories based on categories tree: parent->childrenNode->childrenNode->...
     */
    public function getFlatCategoriesArray(array $categoriesTree, array $categoriesIds, array $flatTree = []): array
    {
        $categoryId = array_shift($categoriesIds);
        if (! empty($categoriesTree['cat_id']) && $categoriesTree['cat_id'] === $categoryId) {
            $_category = $categoriesTree;
            unset($_category['children']);
            $flatTree[$categoryId] = $_category;
            $_categoriesIds = $categoriesIds;
            $categoryId = array_shift($_categoriesIds);
        }
        if (! empty($categoriesTree['children']) && ! empty($categoriesTree['children'][$categoryId])) {
            $_flatTree = $this->getFlatCategoriesArray($categoriesTree['children'][$categoryId], $categoriesIds);
            $flatTree += $_flatTree;
        }

        return $flatTree;
    }
}
