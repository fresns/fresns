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
        $standardTime = gmdate('Y-m-d H:i:s');
        $now = DateHelper::fresnsDatabaseCurrentDateTime();
        $hour = Carbon::parse($standardTime)->floatDiffInHours($now, false);
        if ($hour > 0) {
            $hour = '+'.$hour;
        }

        return $hour;
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
     * @return string
     */
    public static function fresnsDatabaseTimezoneByName(string $timezoneName)
    {
        $timezones = ConfigHelper::fresnsConfigByItemKey('timezones');

        return $timezones[$timezoneName];
    }

    /**
     * Get the current database time.
     *
     * @return string
     */
    public static function fresnsDatabaseCurrentDateTime()
    {
        return get_object_vars(DB::select('SELECT NOW()')[0])['NOW()'];
    }

    /**
     * The conversion time is the current database time.
     *
     * @param $datetime
     * @param $timezone
     * @return string
     *
     * @throws \Exception
     */
    public static function fresnsDateTimeToDatabaseTimezone(string $datetime, ?string $timezone = null, ?string $langTag = null)
    {
        if (empty($datetime)) {
            return null;
        }

        $timezone = $timezone ?: ConfigHelper::fresnsConfigByItemKey('default_timezone');
        $langTag = $langTag ?: ConfigHelper::fresnsConfigByItemKey('default_language');
        $dateFormat = ConfigHelper::fresnsConfigDateFormat($langTag);
        $dateTimeFormat = $dateFormat.' H:i:s' ?: 'Y-m-d H:i:s';

        $timezone = $timezone > 0 ? -1 * $timezone : '+'.abs($timezone);
        $standard = strtotime($datetime);
        if (! empty($timezone)) {
            $standard = date('Y-m-d H:i:s', strtotime("$timezone hours", strtotime($datetime)));
        }
        $datetime = new \DateTime($standard);
        $result = $datetime->setTimezone(new \DateTimeZone(DateHelper::fresnsDatabaseTimezone()));

        return $result->format($dateTimeFormat);
    }

    /**
     * Output time values by time zone.
     *
     * @param $datetime
     * @param  string  $timezone
     * @return \DateTime|string
     *
     * @throws \Exception
     */
    public static function fresnsDateTimeByTimezone(?string $datetime = null, ?string $timezone = null, ?string $langTag = null)
    {
        if (empty($datetime)) {
            return null;
        }

        $timezone = $timezone ?: ConfigHelper::fresnsConfigByItemKey('default_timezone');
        $langTag = $langTag ?: ConfigHelper::fresnsConfigByItemKey('default_language');
        $dateFormat = ConfigHelper::fresnsConfigDateFormat($langTag);
        $dateTimeFormat = $dateFormat.' H:i:s' ?: 'Y-m-d H:i:s';

        $dbTimezone = DateHelper::fresnsDatabaseTimezone();
        if ($dbTimezone == $timezone) {
            return date($dateTimeFormat, strtotime($datetime));
        }

        $newTimezone = $dbTimezone > 0 ? -1 * $dbTimezone : '+'.abs($dbTimezone);
        $standard = date('Y-m-d H:i:s', strtotime("$newTimezone hours", strtotime($datetime)));
        if ($timezone == 0) {
            return date($dateTimeFormat, strtotime($standard));
        }

        $time = (new \DateTime($standard))->setTimezone(new \DateTimeZone($timezone));

        return $time->format($dateTimeFormat);
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

        $timezone = $timezone ?: ConfigHelper::fresnsConfigByItemKey('default_timezone');
        $langTag = $langTag ?: ConfigHelper::fresnsConfigByItemKey('default_language');
        $dateFormat = ConfigHelper::fresnsConfigDateFormat($langTag).' H:i';
        $dateFormatNoY = \Str::swap([
            'Y-' => '',
            'Y.' => '',
            '-Y' => '',
            '.Y' => '',
            'Y/' => '',
            '/Y' => '',
        ], $dateFormat);

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
        if (empty($datetime)) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigByItemKey('default_language');

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

        return $diff.' '.$timeFormat;
    }
}
