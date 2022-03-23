<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Message;

use App\Fresns\Api\Base\Checkers\BaseChecker;
use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\LogService;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRoles;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRolesService;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollows;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRolesService;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\Helpers\ApiConfigHelper;

class FsChecker extends BaseChecker
{
    public static function checkSendMessage($uid)
    {
        // return true;
        // Key Name dialog_status Configure global dialog function
        $dialogStatus = ApiConfigHelper::getConfigByItemKey(FsConfig::DIALOG_STATUS);
        if (! $dialogStatus) {
            return self::checkInfo(ErrorCodeService::DIALOG_ERROR);
        }

        // In case of private mode, when expired (users > expired_at ) no messages are allowed to be sent.
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $userInfo = FresnsUsers::find($uid);
            if ($userInfo['expired_at'] && ($userInfo['expired_at'] <= date('Y-m-d H:i:s'))) {
                LogService::info('Your account status has expired', $userInfo);

                return self::checkInfo(ErrorCodeService::USER_EXPIRED_ERROR);
            }
        }

        // Determine if the user master role has the right to send private messages (roles > permission > dialog=true)
        $roleId = FresnsUserRolesService::getUserRoles($uid);
        if (empty($roleId)) {
            return self::checkInfo(ErrorCodeService::ROLE_DIALOG_ERROR);
        }
        $userRole = FresnsRoles::where('id', $roleId)->first();
        if (! empty($userRole)) {
            $permission = $userRole['permission'];
            $permissionArr = json_decode($permission, true);
            if (! empty($permissionArr)) {
                $permissionMap = FresnsRolesService::getPermissionMap($permissionArr);
                if (empty($permissionMap)) {
                    return self::checkInfo(ErrorCodeService::ROLE_DIALOG_ERROR);
                }
            }
            if (! isset($permissionMap['dialog'])) {
                return self::checkInfo(ErrorCodeService::ROLE_DIALOG_ERROR);
            }
            if ($permissionMap['dialog'] == false) {
                return self::checkInfo(ErrorCodeService::ROLE_DIALOG_ERROR);
            }
        } else {
            return self::checkInfo(ErrorCodeService::ROLE_DIALOG_ERROR);
        }

        // Determine if the other party has deleted (users > deleted_at)
        $recvUid = request()->input('recvUid');
        $recvUidInfo = FresnsUsers::where('uid', $recvUid)->first();
        if (! $recvUidInfo) {
            return self::checkInfo(ErrorCodeService::USER_ERROR);
        }

        // Determine whether the dialog settings match each other (users > dialog_limit)
        $userInfo = FresnsUsers::where('uid', $recvUid)->first();
        if ($userInfo['id'] == $uid) {
            return self::checkInfo(ErrorCodeService::SEND_ME_ERROR);
        }
        // dialog_limit = 2 / Only users that I am allowed to follow
        if ($userInfo['dialog_limit'] == 2) {
            $count = FresnsUserFollows::where('user_id', $uid)->where('follow_type', 1)->where('follow_id', $userInfo['id'])->count();
            if ($count == 0) {
                return self::checkInfo(ErrorCodeService::DIALOG_LIMIT_2_ERROR);
            }
        }
        // dialog_limit = 3 / Users I follow and users I have certified
        if ($userInfo['dialog_limit'] == 3) {
            $count = FresnsUserFollows::where('user_id', $uid)->where('follow_type', 1)->where('follow_id', $userInfo['id'])->count();
            if ($count == 0) {
                return self::checkInfo(ErrorCodeService::DIALOG_LIMIT_3_ERROR);
            }
            $myInfo = FresnsUsers::find($uid);
            if ($myInfo['verified_status'] == 1) {
                return self::checkInfo(ErrorCodeService::DIALOG_LIMIT_3_ERROR);
            }
        }

        // request
        $message = request()->input('message', null);
        $fid = request()->input('fid', null);
        if ($message && $fid) {
            return self::checkInfo(ErrorCodeService::FILE_OR_TEXT_ERROR);
        }
    }
}
