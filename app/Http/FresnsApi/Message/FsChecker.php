<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Message;

use App\Base\Checkers\BaseChecker;
use App\Http\Center\Common\ErrorCodeService;
use App\Http\Center\Common\LogService;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsDb\FresnsFiles\FresnsFiles;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRelsService;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesService;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsUsers\FresnsUsers;

class FsChecker extends BaseChecker
{
    public static function checkSendMessage($mid)
    {
        // return true;
        // Key Name dialog_status Configure global dialog function
        $dialogStatus = ApiConfigHelper::getConfigByItemKey(FsConfig::DIALOG_STATUS);
        if (! $dialogStatus) {
            return self::checkInfo(ErrorCodeService::DIALOG_ERROR);
        }

        // In case of private mode, when expired (members > expired_at ) no messages are allowed to be sent.
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $memberInfo = FresnsMembers::find($mid);
            if ($memberInfo['expired_at'] && ($memberInfo['expired_at'] <= date('Y-m-d H:i:s'))) {
                LogService::info('Your account status has expired', $memberInfo);

                return self::checkInfo(ErrorCodeService::MEMBER_EXPIRED_ERROR);
            }
        }

        // Determine if the member master role has the right to send private messages (member_roles > permission > dialog=true)
        $roleId = FresnsMemberRoleRelsService::getMemberRoleRels($mid);
        if (empty($roleId)) {
            return self::checkInfo(ErrorCodeService::ROLE_DIALOG_ERROR);
        }
        $memberRole = FresnsMemberRoles::where('id', $roleId)->first();
        if (! empty($memberRole)) {
            $permission = $memberRole['permission'];
            $permissionArr = json_decode($permission, true);
            if (! empty($permissionArr)) {
                $permissionMap = FresnsMemberRolesService::getPermissionMap($permissionArr);
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

        // Determine if the other party has deleted (members > deleted_at)
        $recvMid = request()->input('recvMid');
        $recvMidInfo = FresnsMembers::where('uuid', $recvMid)->first();
        if (! $recvMidInfo) {
            return self::checkInfo(ErrorCodeService::MEMBER_ERROR);
        }

        // Determine whether the dialog settings match each other (members > dialog_limit)
        $memberInfo = FresnsMembers::where('uuid', $recvMid)->first();
        if ($memberInfo['id'] == $mid) {
            return self::checkInfo(ErrorCodeService::SEND_ME_ERROR);
        }
        // dialog_limit = 2 / Only members that I am allowed to follow
        if ($memberInfo['dialog_limit'] == 2) {
            $count = FresnsMemberFollows::where('member_id', $mid)->where('follow_type', 1)->where('follow_id', $memberInfo['id'])->count();
            if ($count == 0) {
                return self::checkInfo(ErrorCodeService::DIALOG_LIMIT_2_ERROR);
            }
        }
        // dialog_limit = 3 / Members I follow and members I have certified
        if ($memberInfo['dialog_limit'] == 3) {
            $count = FresnsMemberFollows::where('member_id', $mid)->where('follow_type', 1)->where('follow_id', $memberInfo['id'])->count();
            if ($count == 0) {
                return self::checkInfo(ErrorCodeService::DIALOG_LIMIT_3_ERROR);
            }
            $myInfo = FresnsMembers::find($mid);
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
