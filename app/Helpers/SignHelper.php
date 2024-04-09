<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use Illuminate\Support\Str;

class SignHelper
{
    const SIGN_PARAM_ARR = [
        'X-Fresns-App-Id',
        'X-Fresns-Client-Platform-Id',
        'X-Fresns-Client-Version',
        'X-Fresns-Aid',
        'X-Fresns-Aid-Token',
        'X-Fresns-Uid',
        'X-Fresns-Uid-Token',
        'X-Fresns-Signature-Timestamp',
    ];

    // Check Sign
    public static function checkSign(array $signMap, string $appKey): bool
    {
        $checkArr = [
            'X-Fresns-App-Id' => $signMap['X-Fresns-App-Id'] ?? $signMap['appId'] ?? null,
            'X-Fresns-Client-Platform-Id' => $signMap['X-Fresns-Client-Platform-Id'] ?? $signMap['platformId'] ?? null,
            'X-Fresns-Client-Version' => $signMap['X-Fresns-Client-Version'] ?? $signMap['version'] ?? null,
            'X-Fresns-Aid' => $signMap['X-Fresns-Aid'] ?? $signMap['aid'] ?? null,
            'X-Fresns-Aid-Token' => $signMap['X-Fresns-Aid-Token'] ?? $signMap['aidToken'] ?? null,
            'X-Fresns-Uid' => $signMap['X-Fresns-Uid'] ?? $signMap['uid'] ?? null,
            'X-Fresns-Uid-Token' => $signMap['X-Fresns-Uid-Token'] ?? $signMap['uidToken'] ?? null,
            'X-Fresns-Signature-Timestamp' => $signMap['X-Fresns-Signature-Timestamp'] ?? $signMap['timestamp'] ?? null,
        ];

        $inputSign = $signMap['X-Fresns-Signature'] ?? $signMap['signature'];

        $makeSign = SignHelper::makeSign($checkArr, $appKey);

        return $inputSign == $makeSign;
    }

    // Make Sign
    public static function makeSign(array $signMap, string $appKey): string
    {
        $signParams = collect($signMap)->filter(function ($value, $key) {
            return in_array($key, SignHelper::SIGN_PARAM_ARR);
        })->toArray();

        $signParams = array_filter($signParams);

        ksort($signParams);

        $params = http_build_query($signParams);

        $signData = $params."&AppKey={$appKey}";

        // Generate sign
        $sign = hash('sha256', $signData);

        return $sign;
    }

    // Make Login Token
    public static function makeLoginToken(string|int|null $account = null): string
    {
        $random = Str::random(32);
        $ulid = Str::ulid();
        $uuid = Str::uuid();

        $tokenString = $random.$ulid.$uuid.$account;

        $loginToken = hash('sha256', $tokenString);

        return $loginToken;
    }
}
