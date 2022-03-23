<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Fresns\Api\Http\Center\Common\LogService;

class SignHelper
{
    public static function checkSign($dataMap, $signKey)
    {
        $inputSign = $dataMap['sign'];
        unset($dataMap['sign']);

        $genSign = self::genSign($dataMap, $signKey);
        $info = [];
        $info['input_sign'] = $inputSign;
        $info['gen_sign'] = $genSign;
        LogService::info('check sign: ', $info);

        if ($inputSign == $genSign) {
            return true;
        }

        return $info;
    }

    public static function genSign($dataMap, $signKey)
    {
        // Sort the values of the array by key
        ksort($dataMap);
        // Generate the url mode
        $params = http_build_query($dataMap);
        $params = $params."&key={$signKey}";
        // Generate sign
        $sign = md5($params);

        return $sign;
    }

    public static function checkTokenParam(string $token = '', string $aid = '', int $uid = 0)
    {
        if ($aid && $uid && $token) {
            return true;
        } elseif ($aid && $token) {
            return true;
        } elseif ($token && empty($uid)) {
            return true;
        }

        return false;
    }
}
