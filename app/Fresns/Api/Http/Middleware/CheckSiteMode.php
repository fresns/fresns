<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Middleware;

use App\Fresns\Api\Exceptions\ResponseException;
use App\Fresns\Api\Traits\ApiHeaderTrait;
use App\Fresns\Api\Traits\ApiResponseTrait;
use App\Helpers\ConfigHelper;
use App\Utilities\PermissionUtility;
use Closure;
use Illuminate\Http\Request;

class CheckSiteMode
{
    use ApiHeaderTrait;
    use ApiResponseTrait;

    public function handle(Request $request, Closure $next)
    {
        $modeConfig = ConfigHelper::fresnsConfigByItemKeys([
            'site_mode',
            'site_private_end_after',
        ]);

        if ($modeConfig['site_mode'] == 'public') {
            return $next($request);
        }

        $authUser = $this->user();

        if (empty($authUser)) {
            throw new ResponseException(31601);
        }

        $checkUserRolePrivateWhitelist = PermissionUtility::checkUserRolePrivateWhitelist($authUser->id);
        if ($checkUserRolePrivateWhitelist) {
            return $next($request);
        }

        if (empty($authUser->expired_at)) {
            throw new ResponseException(35306);
        }

        $currentRouteName = \request()->route()->getName();

        $expired = $authUser->expired_at->isPast();

        if ($modeConfig['site_private_end_after'] == 1 && $expired) {
            $disableList = config('FsApiBlacklist.disableByContentNotVisible');

            if (in_array($currentRouteName, $disableList)) {
                return $this->warning(35303);
            }

            throw new ResponseException(35302);
        }

        $blacklist = config('FsApiBlacklist.disableForAfterExpiry');

        // check blacklist
        if (in_array($currentRouteName, $blacklist)) {
            throw new ResponseException(35302);
        }

        return $next($request);
    }
}
