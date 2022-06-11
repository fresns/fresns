<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\ConfigHelper;

class HeaderService
{
    // get headers info
    public static function getHeaders()
    {
        $defaultConfig = ConfigHelper::fresnsConfigByItemKeys([
            'default_language',
            'default_timezone',
        ]);

        $header['platformId'] = \request()->header('platformId');
        $header['version'] = \request()->header('version');
        $header['appId'] = \request()->header('appId');
        $header['timestamp'] = \request()->header('timestamp');
        $header['sign'] = \request()->header('sign');
        $header['langTag'] = \request()->header('langTag', $defaultConfig['default_language']);
        $header['timezone'] = \request()->header('timezone', $defaultConfig['default_timezone']);
        $header['aid'] = \request()->header('aid');
        $header['uid'] = \request()->header('uid');
        $header['token'] = \request()->header('token');
        $header['deviceInfo'] = \request()->header('deviceInfo');
        $headers = $header;

        return $headers;
    }
}
