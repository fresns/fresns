<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Helpers;

class ArrayHelper
{
    /**
     * Sorting multidimensional arrays
     * The first parameter is the array to be sorted, the rest is the key to be sorted (key) and the sorting method, the key then needs to be connected up and down because it has to cope with the multi-dimensional situation, using ".".
     *
     * @example multiDimensionSort($arr,'price',SORT_DESC,'top1.field',SORT_ASC)
     *
     * @param  mixed  ...$args
     * @return mixed
     */
    public static function multiDimensionSort(...$args)
    {
        $arr = array_shift($args); // Get the array to be sorted, the rest is the key to be sorted and the sort type
        $sort_arg = [];
        foreach ($args as $arg) {
            // The main purpose here is to get the value corresponding to the sorted key
            $sort = $arr;
            if (is_string($arg)) {
                $arg = explode('.', $arg); // Set the key under the multi-dimensional array inside the parameter, and use '.' to connect the keys of the lower level, here we get the key, and then the following loop gets the value corresponding to the key in the array $arr
                foreach ($arg as $key) {
                    $sort = array_column($sort, $key); // The dimension of $sort is reduced by one with each loop
                }
                $sort_arg[] = $sort;
            } else {
                $sort_arg[] = $arg; // Sorting methods SORT_ASC, SORT_DESC, etc.
            }
        }
        $sort_arg[] = &$arr; // The approximate structure of this array is: [$sort, SORT_ASC, $sort2, SORT_DESC, $arr]

        call_user_func_array('array_multisort', $sort_arg);

        return $arr;
    }

    /**
     * Two-dimensional arrays sorted by a field.
     *
     * @param  array  $array  / The array to sort
     * @param  string  $keys  / Key fields to sort
     * @param  string  $sort  / Sort Type: SORT_ASC, SORT_DESC
     * @return array / Sorted arrays
     */
    public static function arraySort(&$array, $keys, $sortDirection)
    {
        $keysValue = [];
        foreach ($array as $k => $v) {
            if (! isset($v[$keys])) {
                return $array;
            }
            $keysValue[$k] = intval($v[$keys]);
        }
        array_multisort($keysValue, $sortDirection, $array);

        return $array;
    }

    // object to array
    public static function objectToArray($obj)
    {
        $a = json_encode($obj);
        $b = json_decode($a, true);

        return $b;
    }

    // Get description
    public static function keyDescInArray($key, $arr, $matchKey = 'key', $descKey = 'text')
    {
        foreach ($arr as $item) {
            if (! is_array($item)) {
                $item = self::objectToArray($item);
            }
            if (isset($item[$matchKey]) && $item[$matchKey] == $key) {
                return  $item[$descKey] ?? 'Unknown';
            }
        }

        return 'Unknown';
    }
}
