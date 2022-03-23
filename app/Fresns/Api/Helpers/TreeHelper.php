<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Helpers;

class TreeHelper
{
    // for object
    public static function buildTreeFromObjects($items)
    {
        $childs = [];

        foreach ($items as $item) {
            $childs[$item->parent_id ?? 0][] = $item;
        }

        foreach ($items as $item) {
            if (isset($childs[$item->id])) {
                $item->childs = $childs[$item->id];
            }
        }

        return $childs[0] ?? [];
    }

    // array version
    public static function buildTreeFromArray($items)
    {
        $childs = [];

        foreach ($items as &$item) {
            $childs[$item['parent_id'] ?? 0][] = &$item;
        }

        unset($item);

        foreach ($items as &$item) {
            if (isset($childs[$item['id']])) {
                $item['childs'] = $childs[$item['id']];
            }
        }

        $ret = $childs[0] ?? [];

        return $ret;
    }

    public static function getAllIdsInTreeData($categoryArr, &$idArr)
    {
        foreach ($categoryArr as $category) {
            $idArr[] = $category['id'] ?? '';
            $children = $category['children'] ?? [];
            if (! empty($children)) {
                self::getAllIdsInTreeData($children, $idArr);
            }
        }
    }
}
