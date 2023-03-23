<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DateHelper
{
    // Get database utc time zone
    public static function fresnsDatabaseTimezone(): string
    {
        $cacheKey = 'fresns_database_timezone';
        $cacheTag = 'fresnsSystems';
        $databaseTimezone = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($databaseTimezone)) {
            $utc = [
                '-12',
                '-11.5',
                '-11',
                '-10.5',
                '-10',
                '-9.5',
                '-9',
                '-8.5',
                '-8',
                '-7.5',
                '-7',
                '-6.5',
                '-6',
                '-5.5',
                '-5',
                '-4.5',
                '-4',
                '-3.5',
                '-3',
                '-2.5',
                '-2',
                '-1.5',
                '-1',
                '-0.5',
                '+0',
                '+0.5',
                '+1',
                '+1.5',
                '+2',
                '+2.5',
                '+3',
                '+3.5',
                '+4',
                '+4.5',
                '+5',
                '+5.5',
                '+5.75',
                '+6',
                '+6.5',
                '+7',
                '+7.5',
                '+8',
                '+8.5',
                '+8.75',
                '+9',
                '+9.5',
                '+10',
                '+10.5',
                '+11',
                '+11.5',
                '+12',
                '+12.75',
                '+13',
                '+13.75',
                '+14',
            ];

            $standardTime = gmdate('Y-m-d H:i:s');

            $dbNow = DateHelper::fresnsDatabaseCurrentDateTime();
            $hour = Carbon::parse($standardTime)->floatDiffInHours($dbNow, false);

            $hour = round($hour);

            if ($hour > -1e-1) {
                $hour = '+'.$hour;
            }

            $closestTimezone = ConfigHelper::fresnsConfigDefaultTimezone();
            $closestDiff = INF;
            foreach ($utc as $tz) {
                $diff = abs($tz - $hour);
                if ($diff < $closestDiff) {
                    $closestDiff = $diff;
                    $closestTimezone = $tz;
                }
            }

            $databaseTimezone = $closestTimezone;

            CacheHelper::put($databaseTimezone, $cacheKey, $cacheTag);
        }

        return $databaseTimezone;
    }

    // Get database time zone names
    public static function fresnsDatabaseTimezoneNames(): array
    {
        $dbUtc = DateHelper::fresnsDatabaseTimezone();
        $timezones = ConfigHelper::fresnsConfigByItemKey('timezones');

        $timezoneNames = array_keys($timezones, $dbUtc);

        return $timezoneNames;
    }

    // Get database env config utc time zone
    public static function fresnsDatabaseTimezoneByName(string $timezoneName): ?string
    {
        $timezones = ConfigHelper::fresnsConfigByItemKey('timezones');

        return $timezones[$timezoneName] ?? null;
    }

    // Get the current database time
    public static function fresnsDatabaseCurrentDateTime(): string
    {
        $cacheKey = 'fresns_database_datetime';
        $cacheTag = 'fresnsSystems';
        $databaseDateTime = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($databaseDateTime)) {
            $databaseDateTime = DB::selectOne('select now() as now')->now;

            CacheHelper::put($databaseDateTime, $cacheKey, $cacheTag, 1, now()->addMinutes(3));
        }

        return $databaseDateTime;
    }

    // The conversion time is the current database time
    public static function fresnsDateTimeToDatabaseTimezone(?string $datetime, ?string $timezone = null, ?string $langTag = null): ?string
    {
        if (! $datetime) {
            return null;
        }

        $timezone = $timezone ?: ConfigHelper::fresnsConfigDefaultTimezone();
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $dateFormat = ConfigHelper::fresnsConfigDateFormat($langTag);

        $dateTimeFormat = 'Y-m-d H:i:s';
        if ($dateFormat) {
            $dateTimeFormat = $dateFormat.' H:i:s';
        }

        $dbTimezone = DateHelper::fresnsDatabaseTimezone();

        $standard = Carbon::createFromFormat($dateTimeFormat, $datetime, $timezone)->setTimezone($dbTimezone)->format($dateTimeFormat);

        return $standard;
    }

    // Output time values by time zone
    public static function fresnsDateTimeByTimezone(?string $datetime = null, ?string $timezone = null, ?string $langTag = null): ?string
    {
        if (! $datetime) {
            return null;
        }

        $datetime = date('Y-m-d H:i:s', strtotime($datetime));

        $timezone = $timezone ?: ConfigHelper::fresnsConfigDefaultTimezone();
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $dateFormat = ConfigHelper::fresnsConfigDateFormat($langTag);

        $dateTimeFormat = 'Y-m-d H:i:s';
        if ($dateFormat) {
            $dateTimeFormat = $dateFormat.' H:i:s';
        }

        $dbTimezone = DateHelper::fresnsDatabaseTimezone();
        if ($dbTimezone == $timezone) {
            return $datetime;
        }

        $standard = Carbon::createFromFormat('Y-m-d H:i:s', $datetime, $dbTimezone)->setTimezone($timezone)->format($dateTimeFormat);

        return $standard;
    }

    // Output time by specified time zone
    public static function fresnsTimeByTimezone(?string $time = null, ?string $timezone = null): ?string
    {
        if (! $time) {
            return null;
        }

        $timezone = $timezone ?: ConfigHelper::fresnsConfigDefaultTimezone();

        $currentTime = DateHelper::fresnsDatabaseCurrentDateTime();
        $dateString = Carbon::createFromFormat('Y-m-d H:i:s', $currentTime)->toDateString();

        // $time = 23:00 or $time = 23:00:00
        if (substr_count($time, ':') == 1) {
            $time = $time.':00';
        }

        $dbTime = $dateString.' '.$time;

        $newDatetime = DateHelper::fresnsDateTimeByTimezone($dbTime, $timezone);

        $newTime = date('H:i', strtotime($newDatetime));

        return $newTime;
    }

    // Formatted time output by time zone and language tag
    public static function fresnsFormatDateTime(?string $datetime = null, ?string $timezone = null, ?string $langTag = null): ?string
    {
        if (empty($datetime)) {
            return null;
        }

        $timezone = $timezone ?: ConfigHelper::fresnsConfigDefaultTimezone();
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $dateFormat = ConfigHelper::fresnsConfigDateFormat($langTag).' H:i';
        $dateFormatNoY = str_replace(
            ['Y-', 'Y.', '-Y', '.Y', 'Y/', '/Y'],
            '',
            $dateFormat
        );

        $tzDatetime = DateHelper::fresnsDateTimeByTimezone($datetime, $timezone, $langTag);
        $tzDatetimeY = date('Y', strtotime($tzDatetime));
        $tzDatetimeMd = date('m-d', strtotime($tzDatetime));

        $dbDatetime = DateHelper::fresnsDatabaseCurrentDateTime();
        $tzDbDatetime = DateHelper::fresnsDateTimeByTimezone($dbDatetime, $timezone, $langTag);
        $tzDbDatetimeY = date('Y', strtotime($tzDbDatetime));
        $tzDbDatetimeMd = date('m-d', strtotime($tzDbDatetime));

        if ($tzDatetimeY != $tzDbDatetimeY) {
            return $tzDatetime;
        } elseif ($tzDatetimeMd != $tzDbDatetimeMd) {
            return date($dateFormatNoY, strtotime($tzDatetime));
        }

        return date('H:i', strtotime($tzDatetime));
    }

    // Processing output by language humanization time
    public static function fresnsFormatTime(?string $datetime = null, ?string $langTag = null): ?string
    {
        if (! $datetime) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();
        $currentTime = DateHelper::fresnsDatabaseCurrentDateTime();
        $languageMenus = ConfigHelper::fresnsConfigByItemKey('language_menus');
        $timeFormatItem = collect($languageMenus)->where('langTag', $langTag)->first();

        // $timeLine = strtotime($currentTime) - strtotime($datetime);
        $timeLine = time() - strtotime($datetime);

        if ($timeLine < 60 * 60) {
            // {n} minute ago
            $timeInt = floor($timeLine / 60);
            $timeFormatString = $timeFormatItem['timeFormatMinute'] ?? '{n} minute ago';
        } elseif ($timeLine < 60 * 60 * 24) {
            // {n} hour ago
            $timeInt = floor($timeLine / (60 * 60));
            $timeFormatString = $timeFormatItem['timeFormatHour'] ?? '{n} hour ago';
        } elseif ($timeLine < 60 * 60 * 24 * 30) {
            // {n} day ago
            $timeInt = floor($timeLine / (60 * 60 * 24));
            $timeFormatString = $timeFormatItem['timeFormatDay'] ?? '{n} day ago';
        } elseif ($timeLine < 60 * 60 * 24 * 7 * 4 * 12) {
            // {n} month ago
            $timeInt = floor($timeLine / (60 * 60 * 24 * 7 * 4));
            $timeFormatString = $timeFormatItem['timeFormatMonth'] ?? '{n} month ago';
        } else {
            // {n} year ago
            $timeInt = floor($timeLine / (60 * 60 * 24 * 7 * 4 * 12));
            $timeFormatString = $timeFormatItem['timeFormatYear'] ?? '{n} year ago';
        }

        if ($timeInt <= 0) {
            $timeInt = 1;
        }

        return str_replace('{n}', $timeInt, $timeFormatString);

        // $timeLine = time() - strtotime($datetime);
        // if ($timeLine <= 0) {
        //     // Just now
        //     return 'Just now';
        // } elseif ($timeLine < 60) {
        //     // {n} second ago
        //     return $timeLine . ' second ago';
        // } elseif ($timeLine < 60 * 60) {
        //     // {n} minute ago
        //     return floor($timeLine / 60) . ' minute ago';
        // } elseif ($timeLine < 60 * 60 * 24) {
        //     // {n} hour ago
        //     return floor($timeLine / (60 * 60)) . ' hour ago';
        // } elseif ($timeLine < 60 * 60 * 24 * 7) {
        //     // {n} day ago
        //     return floor($timeLine / (60 * 60 * 24)) . ' day ago';
        // } elseif ($timeLine < 60 * 60 * 24 * 7 * 4) {
        //     // {n} week ago
        //     return floor($timeLine / (60 * 60 * 24 * 7)) . ' week ago';
        // } elseif ($timeLine < 60 * 60 * 24 * 7 * 4 * 12) {
        //     // {n} month ago
        //     return floor($timeLine / (60 * 60 * 24 * 7 * 4)) . ' month ago';
        // } else {
        //     // {n} year ago
        //     return floor($timeLine / (60 * 60 * 24 * 7 * 4 * 12)) . ' year ago';
        // }
    }

    // Date time format conversion
    public static function fresnsFormatConversion(?string $datetime = null, ?string $langTag = null): ?string
    {
        if (empty($datetime)) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $dateFormat = ConfigHelper::fresnsConfigDateFormat($langTag).' H:i:s';

        return date($dateFormat, strtotime($datetime));
    }
}
