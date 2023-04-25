<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Middleware;

use App\Exceptions\ApiException;
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
            throw new ApiException(31601);
        }

        $checkUserRolePrivateWhitelist = PermissionUtility::checkUserRolePrivateWhitelist($authUser->id);
        if ($checkUserRolePrivateWhitelist) {
            return $next($request);
        }

        if (empty($authUser->expired_at)) {
            throw new ApiException(35306);
        }

        $now = time();
        $expireTime = strtotime($authUser->expired_at);

        $currentRouteName = \request()->route()->getName();

        if ($modeConfig['site_private_end_after'] == 1 && $expireTime < $now) {
            $list = [
                'api.user.list',
                'api.user.followers.you.follow',
                'api.user.interaction',
                'api.user.mark.list',
                'api.group.interaction',
                'api.hashtag.list',
                'api.hashtag.interaction',
                'api.post.list',
                'api.post.interaction',
                'api.post.users',
                'api.post.quotes',
                'api.post.logs',
                'api.post.follow',
                'api.post.nearby',
                'api.comment.list',
                'api.comment.interaction',
                'api.comment.logs',
                'api.comment.follow',
                'api.comment.nearby',
            ];

            if (in_array($currentRouteName, $list)) {
                return $this->warning(35303);
            }

            throw new ApiException(35302);
        }

        $blacklist = config('FsApiBlacklist.disableRoutes');

        // check blacklist
        if (in_array($currentRouteName, $blacklist)) {
            throw new ApiException(35302);
        }

        return $next($request);
    }
}
