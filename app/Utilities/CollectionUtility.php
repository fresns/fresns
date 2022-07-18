<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

class CollectionUtility
{
    /**
     * @param  array  $data
     * @param  string  $primary
     * @param  string  $parent
     * @param  string  $children
     * @return null|array
     *                    #
     */
    public static function toTree(?array $data = null, $primary = 'id', $parent = 'parent_id', $children = 'children')
    {
        // data is empty
        if (count($data) === 0) {
            return null;
        }

        // parameter missing
        if (! array_key_exists($primary, head($data)) || ! array_key_exists($parent, head($data))) {
            return null;
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
