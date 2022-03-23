<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Base\Services;

use App\Fresns\Api\Center\Common\ValidateService;

class BaseCategoryService extends BaseService
{
    // Search cond
    public $cond = [];

    // Hook functions: Before index
    public function hookListTreeBefore()
    {
        $this->initTreeSearchCond();
    }

    // Initialize search cond
    public function initTreeSearchCond()
    {
        $req = request();
        $cond = [];
        $searchCondConfig = $this->config->getTreeSearchRule();
        foreach ($searchCondConfig as $searchField => $arr) {
            // Skip (No key)
            if (! $req->has($searchField)) {
                continue;
            }

            $searchValue = $req->input($searchField);
            // Skip (No value)
            if ($searchValue === null) {
                continue;
            }

            // Validate array fields
            ValidateService::validParamExist($arr, ['field', 'op']);

            $field = $arr['field'];
            $op = $arr['op'];

            $upperOp = strtoupper($op);

            $cond = [];
            switch ($upperOp) {
                case '=':
                    $cond[] = [$field, $op, $searchValue];
                    break;
                case 'LIKE':
                    $cond[] = [$field, $op, "%$searchValue%"];
                    break;
            }
        }

        $this->cond = $cond;
    }

    public function detail($id)
    {
        $data['detail'] = new $this->resourceDetail($this->model->findById($id));

        // common data
        // To get tree data
        $this->listTree();
        $data['common'] = $this->common();

        return $data;
    }

    // List Show
    public function listTree()
    {
        $this->hookListTreeBefore();

        $topCategoryArr = $this->model->where('parent_id', null)->where($this->cond)->get();

        foreach ($topCategoryArr as &$category) {
            $category['key'] = $category->id;
            $allChildren = $category->getAllChildren();

            if (! empty($allChildren)) {
                $category->children = $allChildren;
                // array_multisort(array_column($category['children'], 'rank_num'), SORT_ASC, $category['children']);
            }
        }

        if (count($topCategoryArr) > 0) {
            // Handling formatting
            $this->formatTree($topCategoryArr);

            $tree = [];
            $this->buildTreeData($topCategoryArr, $tree);
            $this->setTreeData($tree);
        }

        return $topCategoryArr;
    }

    // List Show (sorting)
    public function listTreeNoRankNum()
    {
        $this->hookListTreeBefore();

        $topCategoryArr = $this->model->where('parent_id', 0)->where($this->cond)->get();

        foreach ($topCategoryArr as &$category) {
            $category['key'] = $category->id;
            $allChildren = $category->getAllChildren();

            if (! empty($allChildren)) {
                $category->children = $allChildren;
                // array_multisort(array_column($category['children'], 'rank_num'), SORT_ASC, $category['children']);
            }
        }

        if (count($topCategoryArr) > 0) {
            // Handling formatting
            $this->formatTree($topCategoryArr);
            $tree = [];
            $this->buildTreeData($topCategoryArr, $tree);
            $this->setTreeData($tree);
        }
        $childrenIdArr = [];
        // $this->getChildrenIds2($topCategoryArr,$childrenIdArr);
        return $topCategoryArr;
    }

    // List Show (no cond)
    public function listTreeNoCond()
    {
        $topCategoryArr = $this->model->where('parent_id', null)->get();

        foreach ($topCategoryArr as &$category) {
            $category['key'] = $category->id;
            $allChildren = $category->getAllChildren();

            if (! empty($allChildren)) {
                $category->children = $allChildren;
                // array_multisort(array_column($category['children'], 'rank_num'), SORT_ASC, $category['children']);
            }
        }

        if (count($topCategoryArr) > 0) {
            // Handling formatting
            $this->formatTree($topCategoryArr);
            $tree = [];
            $this->buildTreeData($topCategoryArr, $tree);
            $this->setTreeData($tree);
        }

        return $topCategoryArr;
    }

    // Get all previous levels of the selected menu ID
    public function getParentMenuArr($menuIdArr)
    {
        $parentAllId = [];
        foreach ($menuIdArr as $v) {
            $parentId = Menu::where('id', $v)->value('parent_id');
            $parentIdArr = [];

            $this->parentIdArr($parentId, $parentIdArr);
            $parentAllId[] = $parentIdArr;
        }

        $itemIdArr = [];
        foreach ($parentAllId as $parentId) {
            $itemIdArr = array_merge($itemIdArr, $parentId);
        }

        $itemIdArr = empty($itemIdArr) ? [] : array_unique($itemIdArr);

        return array_unique(array_merge($menuIdArr, $itemIdArr));
    }

    public function parentIdArr($parentId, &$parentIdArr)
    {
        $menu = Menu::find($parentId);

        if ($menu) {
            $parentIdArr[] = $menu['id'];

            $this->parentIdArr($menu['parent_id'], $parentIdArr);
        }
    }

    // Formatting
    public function formatTree(&$categoryArr)
    {
        foreach ($categoryArr as &$item) {
            $children = $item->children;
            if ($item->is_enable == 1) {
                $item->is_enable = true;
            } else {
                $item->is_enable = false;
            }

            // If not empty, get childrenIds
            if ($children && count($children) > 0) {
                $childrenIdArr = [];
                $this->getChildrenIds($item->toArray(), $childrenIdArr);
                $item->childrenIds = $childrenIdArr;
                $this->formatTree($children);
            }
        }
    }

    // Generate data
    public function buildTreeData(&$itemArr, &$categoryArr)
    {
        foreach ($itemArr as $item) {
            $children = $item->children;

            // Get the direct children
            $directChildren = [];
            foreach ($children as $child) {
                if ($child->parent_id == $item->id) {
                    $directChildren[] = $child;
                }
            }
            $children = $directChildren;
            $c = [];
            $c['key'] = $item->id;
            $c['value'] = $item->id;
            $c['name'] = $item->name;
            $c['title'] = $item->name;

            if ($children && count($children) > 0) {
                $this->buildTreeData($children, $c['children']);
            }

            $categoryArr[] = $c;
        }
    }

    // Get childrenIds
    public function getChildrenIds($categoryItem, &$childrenIdArr)
    {
        if (key_exists('children', $categoryItem)) {
            $childrenArr = $categoryItem['children'];
            foreach ($childrenArr as $children) {
                $childrenIdArr[] = $children['id'];
                $this->getChildrenIds($children, $childrenIdArr);
            }
        }
    }

    // Get childrenIds2
    public function getChildrenIds2($categoryItem, &$childrenIdArr)
    {
        if (key_exists('children', $categoryItem)) {
            $childrenArr = $categoryItem['children'];
            foreach ($childrenArr as $children) {
                $childrenIdArr[] = $children['id'];
                $this->getChildrenIds($children, $childrenIdArr);
            }
        }
    }

    // List Show (by cond)
    public function listTreeBycond($cond = [])
    {
        $this->hookListTreeBefore();

        $topCategoryArr = $this->model->where('parent_id', null)->where($this->cond)->where($cond)->get();

        foreach ($topCategoryArr as &$category) {
            $category['key'] = $category->id;
            $allChildren = $category->getAllChildren();
            if (! empty($allChildren)) {
                $category->children = $allChildren;
                // array_multisort(array_column($category['children'], 'rank_num'), SORT_ASC, $category['children']);
            }
        }

        if (count($topCategoryArr) > 0) {
            // Handling formatting
            $this->formatTree($topCategoryArr);

            $tree = [];
            $this->buildTreeData($topCategoryArr, $tree);
            $this->setTreeData($tree);
        }

        return $topCategoryArr;
    }
}
