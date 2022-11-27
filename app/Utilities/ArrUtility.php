<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

class ArrUtility
{
    // get key value
    public static function get(?array $arrays, string $key, string|array $values)
    {
        if (empty($arrays)) {
            return [];
        }

        $values = (array) $values;

        [$findData, $otherData] = collect($arrays)->partition(function ($item) use ($key, $values) {
            return in_array($item[$key], $values);
        });

        $data = $findData->values()->toArray();

        if (count($data) == 1) {
            return $data[0];
        }

        return $data;
    }

    // forget key value
    public static function forget(?array $arrays, string $key, string|array $values)
    {
        if (empty($arrays)) {
            return [];
        }

        $values = (array) $values;

        [$findData, $otherData] = collect($arrays)->partition(function ($item) use ($key, $values) {
            return in_array($item[$key], $values);
        });

        $data = $otherData->values()->toArray();

        if (count($data) == 1) {
            return $data[0];
        }

        return $data;
    }

    // pull key value
    public static function pull(?array &$arrays, string $key, string|array $values)
    {
        if (empty($arrays)) {
            return [];
        }

        $values = (array) $values;

        [$findData, $otherData] = collect($arrays)->partition(function ($item) use ($key, $values) {
            return in_array($item[$key], $values);
        });

        $arrays = $otherData->values()->toArray();

        $data = $findData->values()->toArray();

        if (count($data) == 1) {
            return $data[0];
        }

        return $data;
    }

    // edit value
    public static function editValue(?array $array, string $key, string $value, string $newValue)
    {
        if (empty($array)) {
            return [];
        }

        // $array format
        // [
        //     {
        //         "name":"language",
        //         "canDelete":false
        //     }
        // ]

        // $key = name
        // $value = language
        // $newValue = lang

        foreach ($array as $arrayKey => $arrayItem) {
            if (! is_array($arrayItem)) {
                continue;
            }

            if (! array_key_exists($key, $arrayItem)) {
                continue;
            }

            if ($arrayItem[$key] == $value) {
                $array[$arrayKey][$key] = $newValue;
            }
        }

        return $array;
    }

    // edit key name
    public static function editKey(?object $object, string $key, string $newKey)
    {
        if (empty($object)) {
            return null;
        }

        // $object format
        // {
        //     "language": "Language"
        // }

        // $key = language
        // $newKey = lang

        if (property_exists($object, $key)) {
            $object->$newKey = $object->$key;
            unset($object->$key);
        }

        return $object;
    }
}
