<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class SignHelper
{
    public static function checkSign($dataMap, $signKey)
    {
        $inputSign = $dataMap['sign'];
        unset($dataMap['sign']);

        $makeSign = self::makeSign($dataMap, $signKey);
        $info = [];
        $info['input_sign'] = $inputSign;
        $info['gen_sign'] = $makeSign;
        Log::info('check sign: ', $info);
        if ($inputSign == $makeSign) {
            return true;
        }
        return $info;
    }

    public static function makeSign($dataMap, $signKey)
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

}
