<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Middleware;

use App\Exceptions\ApiException;
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

        // current route name
        $currentRouteName = \request()->route()->getName();

        // notify user activity
        if ($fresnsResp->getData('uid')) {
            $uri = sprintf('/%s', ltrim(\request()->getRequestUri(), '/'));

            SubscribeUtility::notifyUserActivity($currentRouteName, $uri, $fresnsResp->getData(), \request()->all());
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
