<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Base;

use App\Base\Checkers\BaseChecker;
use App\Http\Center\Common\ErrorCodeService;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsSessionTokens\FresnsSessionTokens;

class FsChecker extends BaseChecker
{
    // Check: Site Mode = private (uid and mid required)
    public static function checkSiteMode()
    {
        $siteMode = ApiConfigHelper::getConfigByItemKey('site_mode');

        if ($siteMode == 'private') {
            $uid = request()->header('uid');
            $mid = request()->header('mid');
            if (empty($uid) || empty($mid)) {
                return false;
            }
        }

        return true;
    }

    // Check: Whether the member belongs to the user
    public static function checkUserMember($mid, $uid)
    {
        $memberIdArr = FresnsMembers::where('user_id', $uid)->pluck('id')->toArray();
        if (! in_array($mid, $memberIdArr)) {
            return false;
        }

        return true;
    }

    // Check: User or member permissions
    public static function checkUserMemberPermissions($mid, $uid, $token)
    {
        $platform = request()->header('platform');
        if (! empty($mid)) {
            $userToken = FresnsSessionTokens::where('user_id', $uid)
                ->where('member_id', $mid)
                ->where('platform_id', $platform)
                ->value('token');
            if ($userToken != $token) {
                self::checkInfo(ErrorCodeService::MEMBER_TOKEN_ERROR);
            }
        } else {
            $userToken = FresnsSessionTokens::where('user_id', $uid)
                ->where('member_id', null)
                ->where('platform_id', $platform)
                ->value('token');
            if ($userToken != $token) {
                self::checkInfo(ErrorCodeService::USER_TOKEN_ERROR);
            }
        }

        return true;
    }
}
