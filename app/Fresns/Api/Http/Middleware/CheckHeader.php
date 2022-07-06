<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Middleware;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\CheckHeaderDTO;
use App\Helpers\ConfigHelper;
use Closure;
use Illuminate\Http\Request;

class CheckHeader
{
    public function handle(Request $request, Closure $next)
    {
        $dtoHeaders = new CheckHeaderDTO(\request()->headers->all());
        $headers = $dtoHeaders->toArray();

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
        if ($headers['aid'] ?? null) {
            $accountLogin = true;
        }

        // user login
        $userLogin = false;
        if ($headers['uid'] ?? null) {
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
