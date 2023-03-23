<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ArrUtility
{
    // get key value
    public static function get(?array $array, string $key, string|array $values): object|array
    {
        if (empty($array)) {
            return [];
        }

        $values = (array) $values;

        [$findData, $otherData] = collect($array)->partition(function ($item) use ($key, $values) {
            return in_array($item[$key], $values);
        });

        $data = $findData->values()->toArray();

        if (count($data) == 1) {
            return $data[0];
        }

        return $data;
    }

    // forget key value
    public static function forget(?array $array, string $key, string|array $values): object|array
    {
        if (empty($array)) {
            return [];
        }

        $values = (array) $values;

        [$findData, $otherData] = collect($array)->partition(function ($item) use ($key, $values) {
            return in_array($item[$key], $values);
        });

        $data = $otherData->values()->toArray();

        if (count($data) == 1) {
            return $data[0];
        }

        return $data;
    }

    // pull key value
    public static function pull(?array &$array, string $key, string|array $values): object|array
    {
        if (empty($array)) {
            return [];
        }

        $values = (array) $values;

        [$findData, $otherData] = collect($array)->partition(function ($item) use ($key, $values) {
            return in_array($item[$key], $values);
        });

        $array = $otherData->values()->toArray();

        $data = $findData->values()->toArray();

        if (count($data) == 1) {
            return $data[0];
        }

        return $data;
    }

    // edit value
    public static function editValue(?array $array, string $key, string $value, string $newValue): array
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
    public static function editKey(?object $object, string $key, string $newKey): object
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

    // array filter
    // $type = whitelist or blacklist
    public static function filter(array $array, string $type, array $filterKeys): array
    {
        $dotData = Arr::dot($array);
        $dotDataKeys = array_keys($dotData);

        $dotKeys = [];
        foreach ($filterKeys as $filterKey) {
            foreach ($dotDataKeys as $dataKey) {
                $startsWith = Str::startsWith($dataKey, $filterKey.'.');

                if ($startsWith) {
                    $dotKeys[] = $dataKey;
                }
            }
        }

        $filterKeys = array_merge($filterKeys, $dotKeys);

        if ($type == 'whitelist') {
            $dotData = Arr::only($dotData, $filterKeys);
        } else {
            $dotData = Arr::except($dotData, $filterKeys);
        }

        return Arr::undot($dotData);
    }
}
