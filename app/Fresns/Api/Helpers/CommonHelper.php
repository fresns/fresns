<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Helpers;

class CommonHelper
{
    // Whether https request
    public static function isHttpsRequest()
    {
        if ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
        ) {
            return true;
        }

        return false;
    }

    // Get Domain
    public static function domain()
    {
        $request = request();
        $httpHost = $request->server('HTTP_HOST');

        if (self::isHttpsRequest()) {
            return 'https://'.$httpHost;
        }

        return 'http://'.$httpHost;
    }

    // Get Host
    public static function host()
    {
        $request = request();
        $httpHost = $request->server('HTTP_HOST');

        return $httpHost;
    }

    // Remove the requested data
    public static function removeRequestFields($fieldMap)
    {
        foreach ($fieldMap as $field => $arr) {
            request()->offsetUnset($field);
        }
    }

    // Keep only the requested fields
    public static function onlyRequestFields($onlyFieldArr)
    {
        $allFiledMap = request()->all();

        foreach ($allFiledMap as $field => $value) {
            // Remove if not present
            if (! in_array($field, $onlyFieldArr)) {
                request()->offsetUnset($field);
            }
        }
    }

    // object to array
    public static function objectToArray($obj)
    {
        $a = json_encode($obj);
        $b = json_decode($a, true);

        return $b;
    }
}
