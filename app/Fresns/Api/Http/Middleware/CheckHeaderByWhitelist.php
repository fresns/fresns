<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Middleware;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\HeadersDTO;
use App\Helpers\ConfigHelper;
use Closure;
use Illuminate\Http\Request;

class CheckHeaderByWhitelist
{
    public function handle(Request $request, Closure $next)
    {
        $headers = [
            'appId' => \request()->header('X-Fresns-App-Id'),
            'platformId' => \request()->header('X-Fresns-Client-Platform-Id'),
            'version' => \request()->header('X-Fresns-Client-Version'),
            'deviceInfo' => json_decode(\request()->header('X-Fresns-Client-Device-Info'), true),
            'langTag' => \request()->header('X-Fresns-Client-Lang-Tag'),
            'timezone' => \request()->header('X-Fresns-Client-Timezone'),
            'contentFormat' => \request()->header('X-Fresns-Client-Content-Format'),
            'aid' => \request()->header('X-Fresns-Aid'),
            'aidToken' => \request()->header('X-Fresns-Aid-Token'),
            'uid' => \request()->header('X-Fresns-Uid'),
            'uidToken' => \request()->header('X-Fresns-Uid-Token'),
            'signature' => \request()->header('X-Fresns-Signature'),
            'timestamp' => \request()->header('X-Fresns-Signature-Timestamp'),
        ];

        // check header
        new HeadersDTO($headers);

        // check sign
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifySign($headers);
        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->errorResponse();
        }

        // config
        $siteMode = ConfigHelper::fresnsConfigByItemKey('site_mode');
        $currentRouteName = \request()->route()->getName();

        // account and user login
        $accountLogin = $headers['aid'] ? true : false;
        $userLogin = $headers['uid'] ? true : false;

        // account and user login
        if ($accountLogin && $userLogin) {
            return $next($request);
        }

        // account whitelist
        $accountWhitelist = match ($siteMode) {
            'public' => config('FsApiWhitelist.publicAccount'),
            'private' => config('FsApiWhitelist.privateAccount'),
            default => [],
        };

        // user whitelist
        $userWhitelist = match ($siteMode) {
            'public' => config('FsApiWhitelist.publicUser'),
            'private' => config('FsApiWhitelist.privateUser'),
            default => [],
        };

        // check whitelist
        if (empty($accountWhitelist) || empty($userWhitelist)) {
            throw new ApiException(33102);
        }

        // check account whitelist
        if (! in_array($currentRouteName, $accountWhitelist) && ! $accountLogin) {
            throw new ApiException(31501);
        }

        // check user whitelist
        if (! in_array($currentRouteName, $userWhitelist) && ! $userLogin) {
            throw new ApiException(31601);
        }

        // not login
        return $next($request);
    }
}
