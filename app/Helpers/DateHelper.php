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
    const diffYearFormat = [
        'yyyy-mm-dd' => 'Y-m-d H:i', 'yyyy/mm/dd' => 'Y/m/d H:i', 'yyyy.mm.dd' => 'Y.m.d H:i',
        'mm-dd-yyyy' => 'm-d-Y H:i', 'mm/dd/yyyy' => 'm/d/Y H:i', 'mm.dd.yyyy' => 'm.d.Y H:i',
        'dd-mm-yyyy' => 'd-m-Y H:i', 'dd/mm/yyyy' => 'd/m/Y H:i', 'dd.mm.yyyy' => 'd.m.Y H:i',
    ];

    const sameYearFormat = [
        'yyyy-mm-dd' => 'm-d H:i', 'yyyy/mm/dd' => 'm/d H:i', 'yyyy.mm.dd' => 'm.d H:i',
        'mm-dd-yyyy' => 'm-d H:i', 'mm/dd/yyyy' => 'm/d H:i', 'mm.dd.yyyy' => 'm.d H:i',
        'dd-mm-yyyy' => 'd-m H:i', 'dd/mm/yyyy' => 'd/m H:i', 'dd.mm.yyyy' => 'd.m H:i',
    ];

    /**
     * Get database utc time zone.
     *
     * @return string
     */
    public static function fresnsSqlTimezone()
    {
        $standardTime = gmdate('Y-m-d H:i:s');
        $now = self::fresnsSqlCurrentDateTime();
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
    public static function fresnsSqlTimezoneNames()
    {
        $sqlUtc = self::fresnsSqlTimezone();
        $timezones = ConfigHelper::fresnsConfigByItemKey('timezones');

        $timezoneNames = array_keys($timezones, $sqlUtc);

        return $timezoneNames;
    }

    /**
     * Get database env config utc time zone.
     *
     * @return string
     */
    public static function fresnsSqlTimezoneByName(string $TimezoneName)
    {
        $timezones = ConfigHelper::fresnsConfigByItemKey('timezones');

        return $timezones[$TimezoneName];
    }

    /**
     * Get the current database time.
     *
     * @return string
     */
    public static function fresnsSqlCurrentDateTime()
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
    public static function fresnsDateTimeToSqlTimezone($datetime, $timezone = '')
    {
        if (empty($datetime)) {
            return null;
        }

        $timezone = $timezone ?: ConfigHelper::fresnsConfigByItemKey('default_timezone');

        $timezone = $timezone > 0 ? -1 * $timezone : '+'.abs($timezone);
        $standard = strtotime($datetime);
        if (! empty($timezone)) {
            $standard = date('Y-m-d  H:i:s', strtotime("$timezone hours", strtotime($datetime)));
        }
        $datetime = new \DateTime($standard);
        $result = $datetime->setTimezone(new \DateTimeZone(self::fresnsSqlTimezone()));

        return $result->format('Y-m-d H:i:s');
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
    public static function fresnsDateTimeByTimezone($datetime, $timezone = '')
    {
        if (empty($datetime)) {
            return null;
        }

        $timezone = $timezone ?: ConfigHelper::fresnsConfigByItemKey('default_timezone');

        $mysqlZone = self::fresnsSqlTimezone();
        if ($mysqlZone == $timezone) {
            return date('Y-m-d H:i:s', strtotime($datetime));
        }
        $mysqlZone = $mysqlZone > 0 ? -1 * $mysqlZone : '+'.abs($mysqlZone);
        $standard = date('Y-m-d H:i:s', strtotime("$mysqlZone hours", strtotime($datetime)));
        if ($timezone == 0) {
            return $standard;
        }
        $time = (new \DateTime($standard))->setTimezone(new \DateTimeZone($timezone));

        return $time->format('Y-m-d H:i:s');
    }

    /**
     * Formatted time output by time zone and language tag.
     *
     * @param  string  $datetime
     * @param  string  $timezone
     * @param  string  $langTag
     * @return string
     */
    public static function fresnsFormatDateTime($datetime, $timezone = '', $langTag = '')
    {
        if (empty($datetime)) {
            return null;
        }

        $timezone = $timezone ?: ConfigHelper::fresnsConfigByItemKey('default_timezone');
        $langTag = $langTag ?: ConfigHelper::fresnsConfigByItemKey('default_language');

        $datetime = self::fresnsDateTimeByTimezone($datetime, $timezone);
        $datetime = Carbon::parse($datetime);
        $mysqlTime = Carbon::parse(DateHelper::fresnsSqlCurrentDateTime());
        if ($datetime->diffInDays($mysqlTime) == 0) {
            return Carbon::parse($datetime)->format('H:i');
        }
        $yearFormat = $datetime->diffInYears($mysqlTime) > 0 ? self::diffYearFormat : self::sameYearFormat;
        $languageMenus = ConfigHelper::fresnsConfigByItemKey('language_menus');
        foreach ($languageMenus as $languageMenu) {
            if ($languageMenu['langCode'] == $langTag) {
                $dateFormat = $languageMenu['dateFormat'];
            }
        }

        return $datetime->format($yearFormat[$dateFormat]);
    }

    /**
     * Processing output by language humanization time.
     *
     * @param $datetime
     * @param  string  $langTag
     * @return string
     */
    public static function fresnsFormatTime($datetime, $langTag = '')
    {
        if (empty($datetime)) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigByItemKey('default_language');

        $currentTime = DateHelper::fresnsSqlCurrentDateTime();

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
