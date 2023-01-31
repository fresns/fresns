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
    /**
     * Get database utc time zone.
     *
     * @return string
     */
    public static function fresnsDatabaseTimezone()
    {
        $cacheKey = 'fresns_database_timezone';
        $cacheTag = 'fresnsSystems';
        $databaseTimezone = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($databaseTimezone)) {
            $standardTime = gmdate('Y-m-d H:i:s');

            $dbNow = DateHelper::fresnsDatabaseCurrentDateTime();
            $hour = Carbon::parse($standardTime)->floatDiffInHours($dbNow, false);

            $hour = round($hour);

            if ($hour > 0) {
                $hour = '+'.$hour;
            }

            $databaseTimezone = $hour;

            CacheHelper::put($databaseTimezone, $cacheKey, $cacheTag);
        }

        return $databaseTimezone;
    }

    /**
     * Get database time zone names.
     *
     * @return array
     */
    public static function fresnsDatabaseTimezoneNames()
    {
        $dbUtc = DateHelper::fresnsDatabaseTimezone();
        $timezones = ConfigHelper::fresnsConfigByItemKey('timezones');

        $timezoneNames = array_keys($timezones, $dbUtc);

        return $timezoneNames;
    }

    /**
     * Get database env config utc time zone.
     *
     * @return null|string
     */
    public static function fresnsDatabaseTimezoneByName(string $timezoneName)
    {
        $timezones = ConfigHelper::fresnsConfigByItemKey('timezones');

        return $timezones[$timezoneName] ?? null;
    }

    /**
     * Get the current database time.
     *
     * @return string
     */
    public static function fresnsDatabaseCurrentDateTime()
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

    /**
     * The conversion time is the current database time.
     *
     * @param  null|string  $datetime
     * @param  null|string  $timezone
     * @return string
     *
     * @throws \Exception
     */
    public static function fresnsDateTimeToDatabaseTimezone(?string $datetime, ?string $timezone = null, ?string $langTag = null)
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

    /**
     * Output time values by time zone.
     *
     * @param $datetime
     * @param  string  $timezone
     * @return \DateTime|string|null
     *
     * @throws \Exception
     */
    public static function fresnsDateTimeByTimezone(?string $datetime = null, ?string $timezone = null, ?string $langTag = null)
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

    public static function fresnsTimeByTimezone(?string $time = null, ?string $timezone = null)
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

    /**
     * Formatted time output by time zone and language tag.
     *
     * @param  string  $datetime
     * @param  string  $timezone
     * @param  string  $langTag
     * @return string
     */
    public static function fresnsFormatDateTime(?string $datetime = null, ?string $timezone = null, ?string $langTag = null)
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

    /**
     * Processing output by language humanization time.
     *
     * @param $datetime
     * @param  string  $langTag
     * @return string
     */
    public static function fresnsFormatTime(?string $datetime = null, ?string $langTag = null)
    {
        if (! $datetime) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $currentTime = DateHelper::fresnsDatabaseCurrentDateTime();

        $jet = Carbon::parse($datetime);
        $diff = Carbon::parse($currentTime)->diffInMinutes($jet);
        $symbol = 'timeFormatMinute';
        if ($diff > 60) {
            $diff = Carbon::parse($currentTime)->diffInHours($jet);
            $symbol = 'timeFormatHour';
            if ($diff > 24) {
                $diff = Carbon::parse($currentTime)->diffInDays($jet);
                $symbol = 'timeFormatDay';
                if ($diff > 30) {
                    $diff = Carbon::parse($currentTime)->diffInMonths($jet);
                    $symbol = 'timeFormatMonth';
                }
            }
        }
        $diff = $diff > 0 ? -1 * $diff : '+'.abs($diff);
        $timeFormat = ConfigHelper::fresnsConfigByItemKey('language_menus');
        foreach ($timeFormat as $item) {
            if ($item['langTag'] == $langTag) {
                $timeFormat = $item[$symbol];
                $timeFormat = mb_substr($timeFormat, '4');
            }
        }
        $diff = $datetime < now() ? abs($diff) : $diff;

        if ($diff <= 0) {
            $diff = 1;
        }

        return $diff.' '.$timeFormat;
    }

    /**
     * Date time format conversion。
     *
     * @param  string  $datetime
     * @param  string  $langTag
     * @return string
     */
    public static function fresnsFormatConversion(?string $datetime = null, ?string $langTag = null)
    {
        if (empty($datetime)) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $dateFormat = ConfigHelper::fresnsConfigDateFormat($langTag).' H:i:s';

        return date($dateFormat, strtotime($datetime));
    }
}
