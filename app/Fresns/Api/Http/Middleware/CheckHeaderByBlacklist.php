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

class CheckHeaderByBlacklist
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

        // current route name
        $currentRouteName = \request()->route()->getName();

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
