<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Base;

use App\Fresns\Api\Base\Checkers\BaseChecker;
use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\FsDb\FresnsSessionTokens\FresnsSessionTokens;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\Helpers\ApiConfigHelper;

class FsApiChecker extends BaseChecker
{
    // Check: Site Mode = private (aid and uid required)
    public static function checkSiteMode()
    {
        $siteMode = ApiConfigHelper::getConfigByItemKey('site_mode');

        if ($siteMode == 'private') {
            $aid = request()->header('aid');
            $uid = request()->header('uid');
            if (empty($aid) || empty($uid)) {
                return false;
            }
        }

        return true;
    }

    // Check: Whether the user belongs to the account
    public static function checkAccountUser($uid, $aid)
    {
        $userIdArr = FresnsUsers::where('account_id', $aid)->pluck('id')->toArray();
        if (! in_array($uid, $userIdArr)) {
            return false;
        }

        return true;
    }

    // Check: Account or user permissions
    public static function checkAccountUserPermissions($uid, $aid, $token)
    {
        $platform = request()->header('platform');
        if (! empty($uid)) {
            $accountToken = FresnsSessionTokens::where('account_id', $aid)
                ->where('user_id', $uid)
                ->where('platform_id', $platform)
                ->value('token');
            if ($accountToken != $token) {
                self::checkInfo(ErrorCodeService::USER_TOKEN_ERROR);
            }
        } else {
            $accountToken = FresnsSessionTokens::where('account_id', $aid)
                ->where('user_id', null)
                ->where('platform_id', $platform)
                ->value('token');
            if ($accountToken != $token) {
                self::checkInfo(ErrorCodeService::ACCOUNT_TOKEN_ERROR);
            }
        }

        return true;
    }
}
