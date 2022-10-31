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
    public static function get(?array $arrays = [], string $key, string|array $values)
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

    // remove key value
    public static function forget(?array &$arrays = [], string $key, string|array $values)
    {
        if (empty($arrays)) {
            return false;
        }

        $values = (array) $values;

        [$findData, $otherData] = collect($arrays)->partition(function ($item) use ($key, $values) {
            return in_array($item[$key], $values);
        });

        $arrays = $otherData->values()->toArray();

        return true;
    }

    // pull key value
    public static function pull(?array &$arrays = [], string $key, string|array $values)
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
}
