<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Middleware;

use App\Fresns\Api\Exceptions\ResponseException;
use App\Helpers\ConfigHelper;
use App\Utilities\SubscribeUtility;
use Closure;
use Illuminate\Http\Request;

class CheckHeaderByWhitelist
{
    public function handle(Request $request, Closure $next)
    {
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkHeaders();

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->errorResponse();
        }

        // notify user activity
        if ($fresnsResp->getData('uid')) {
            SubscribeUtility::notifyUserActivity();
        }

        // config
        $siteMode = ConfigHelper::fresnsConfigByItemKey('site_mode');

        // account and user login
        $accountLogin = $fresnsResp->getData('aid') ? true : false;
        $userLogin = $fresnsResp->getData('uid') ? true : false;

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
            throw new ResponseException(33102);
        }

        // current route name
        $currentRouteName = \request()->route()->getName();

        // check account whitelist
        if (! in_array($currentRouteName, $accountWhitelist) && ! $accountLogin) {
            throw new ResponseException(31501);
        }

        // check user whitelist
        if (! in_array($currentRouteName, $userWhitelist) && ! $userLogin) {
            throw new ResponseException(31601);
        }

        // not login
        return $next($request);
    }
}
