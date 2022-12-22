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

class CheckHeader
{
    public function handle(Request $request, Closure $next)
    {
        $headers = [
            'platformId' => \request()->header('platformId'),
            'version' => \request()->header('version'),
            'appId' => \request()->header('appId'),
            'timestamp' => \request()->header('timestamp'),
            'sign' => \request()->header('sign'),
            'langTag' => \request()->header('langTag'),
            'timezone' => \request()->header('timezone'),
            'contentFormat' => \request()->header('contentFormat'),
            'aid' => \request()->header('aid'),
            'aidToken' => \request()->header('aidToken'),
            'uid' => \request()->header('uid'),
            'uidToken' => \request()->header('uidToken'),
            'deviceInfo' => json_decode(\request()->header('deviceInfo'), true),
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

        // account login
        $accountLogin = false;
        if ($headers['aid']) {
            $accountLogin = true;
        }

        // user login
        $userLogin = false;
        if ($headers['uid']) {
            $userLogin = true;
        }

        // account whitelist
        $accountWhitelist = match ($siteMode) {
            default => null,
            'public' => config('FsApiWhitelist.publicAccount'),
            'private' => config('FsApiWhitelist.privateAccount'),
        };

        // user whitelist
        $userWhitelist = match ($siteMode) {
            default => null,
            'public' => config('FsApiWhitelist.publicUser'),
            'private' => config('FsApiWhitelist.privateUser'),
        };

        // check whitelist
        if (! $accountWhitelist || ! $userWhitelist) {
            throw new ApiException(33102);
        }

        // check account whitelist
        if (! $accountLogin && in_array($currentRouteName, $accountWhitelist)) {
            return $next($request);
        }

        // check user whitelist
        if (! $userLogin && in_array($currentRouteName, $userWhitelist)) {
            return $next($request);
        }

        // account and user login
        if ($accountLogin && $userLogin) {
            return $next($request);
        }

        // not login
        throw new ApiException(31501);
    }
}
