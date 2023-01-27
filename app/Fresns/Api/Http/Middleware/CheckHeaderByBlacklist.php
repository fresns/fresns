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

class CheckHeaderByBlacklist
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

        // account blacklist
        $accountBlacklist = match ($siteMode) {
            'public' => config('FsApiBlacklist.publicAccount'),
            'private' => config('FsApiBlacklist.privateAccount'),
            default => [],
        };

        // user blacklist
        $userBlacklist = match ($siteMode) {
            'public' => config('FsApiBlacklist.publicUser'),
            'private' => config('FsApiBlacklist.privateUser'),
            default => [],
        };

        // check blacklist
        if (empty($accountBlacklist) || empty($userBlacklist)) {
            throw new ApiException(33102);
        }

        // check account blacklist
        if (in_array($currentRouteName, $accountBlacklist) && ! $accountLogin) {
            throw new ApiException(31501);
        }

        // check user blacklist
        if (in_array($currentRouteName, $userBlacklist) && ! $userLogin) {
            throw new ApiException(31601);
        }

        // not login
        return $next($request);
    }
}
