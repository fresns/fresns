<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\ConfigHelper;

class GeneralUtility
{
    // Convert list data into a tree structure
    public static function collectionToTree(?array $data = [], string $primary = 'id', string $parent = 'parent_id', string $children = 'children'): array
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

    // Calculate distance based on latitude and longitude
    public static function distanceOfLocation(string $langTag, float $long, float $lat, float $userLong, float $userLat, ?int $mapId = null, ?int $userMapId = null): int
    {
        $unit = ConfigHelper::fresnsConfigLengthUnit($langTag); // Position unit

        $earthRadius = 6371; // Earth's radius in kilometers

        // Convert longitude and latitude to radians
        $long = deg2rad($long);
        $lat = deg2rad($lat);
        $userLong = deg2rad($userLong);
        $userLat = deg2rad($userLat);

        // Calculate the difference in longitude and latitude
        $dlat = $userLat - $lat;
        $dlong = $userLong - $long;

        // Apply the Haversine formula
        $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat) * cos($userLat) * sin($dlong / 2) * sin($dlong / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        $distance = $earthRadius * $c;

        // Convert the distance to the desired unit
        $distanceByUnit = match ($unit) {
            'km' => $distance, // Kilometers
            'mi' => $distance * 0.6214, // Miles
            default => $distance,
        };

        return round($distanceByUnit);
    }
}
