<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\ConfigHelper;

class LbsUtility
{
    /**
     * Calculate the distance based on the latitude and longitude between two points
     * @param $lng1
     * @param $lat1
     * @param $lng2
     * @param $lat2
     * @return int
     */
    public static function getDistance($lng1, $lat1, $lng2, $lat2)
    {
        // Turning the angle to fox degrees
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $radLat1 = deg2rad($lat1); // deg2rad()function converts angles to radians
        $radLat2 = deg2rad($lat2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $distance = 2 * asin(
            sqrt(
                pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)
            )
        ) * 6378.137;
        return $distance;
    }

    public static function getDistanceWithUnit($langTag, ...$rest)
    {
        $unit = ConfigHelper::fresnsConfigLengthUnit($langTag);

        $distance = LbsUtility::getDistance(...$rest);

        $distanceWithUnit = match ($unit) {
            'km' => $distance,
            'mi' => $distance * 0.6214,
            default => $distance,
        };

        return $distanceWithUnit;
    }

    public static function getDistanceSql($sqlLongitude, $sqlLatitude, $longitude, $latitude, $alias = 'distance')
    {
        $sql = <<<SQL
2 * ASIN(
    SQRT(
        POW(
            SIN(
                (
                    $latitude * PI() / 180 - $sqlLatitude * PI() / 180
                ) / 2
            ), 2
        ) + COS($latitude * PI() / 180) * COS($sqlLatitude * PI() / 180) * POW(
            SIN(
                (
                    $longitude * PI() / 180 - $sqlLongitude * PI() / 180
                ) / 2
            ), 2
        )
    )
) * 6378.137
SQL;
        return sprintf('(%s) as %s', $sql, $alias);
    }
}
