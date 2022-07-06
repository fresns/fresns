<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

class SignHelper
{
    const SIGN_PARAM_ARR = [
        'platformId',
        'version',
        'appId',
        'timestamp',
        'aid',
        'uid',
        'token',
    ];

    // Check Sign
    public static function checkSign(array $signMap, string $appSecret)
    {
        $inputSign = $signMap['sign'];
        unset($signMap['sign']);

        $makeSign = SignHelper::makeSign($signMap, $appSecret);

        return $inputSign == $makeSign;
    }

    // Make Sign
    public static function makeSign(array $signMap, string $appSecret)
    {
        $signParams = collect($signMap)->filter(function ($value, $key) {
            return in_array($key, SignHelper::SIGN_PARAM_ARR);
        })->toArray();

        $signParams = array_filter($signParams);

        ksort($signParams);

        $params = http_build_query($signParams);

        $signData = $params."&key={$appSecret}";

        // Generate sign
        $sign = md5($signData);

        return $sign;
    }
}
