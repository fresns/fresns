<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

class CollectionUtility
{
    public static function toTree(?array $data = [], string $primary = 'id', string $parent = 'parent_id', string $children = 'children'): array
    {
        // data is empty
        if (empty($data) || count($data) === 0) {
            return [];
        }

        // parameter missing
        if (! array_key_exists($primary, head($data)) || ! array_key_exists($parent, head($data))) {
            return [];
        }

        $items = [];
        foreach ($data as $v) {
            $items[@$v[$primary]] = $v;
        }

        $tree = [];
        foreach ($items as $item) {
            if (isset($items[$item[$parent]])) {
                $items[$item[$parent]][$children][] = &$items[$item[$primary]];
            } else {
                $tree[] = &$items[$item[$primary]];
            }
        }

        return $tree;
    }
}
