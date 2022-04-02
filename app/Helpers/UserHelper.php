<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Group;
use App\Models\User;
use App\Models\UserRole;

class UserHelper
{
    /**
     * Determine if the user belongs to the account.
     *
     * @param  int  $uid
     * @param  string  $aid
     * @return bool
     */
    public static function fresnsUserAffiliation(int $uid, string $aid)
    {
        $userAccountId = PrimaryHelper::fresnsAccountIdByUid($uid);
        $accountId = PrimaryHelper::fresnsAccountIdByAid($aid);

        return $userAccountId == $accountId ? 'true' : 'false';
    }

    /**
     * Whether the user is disabled or not.
     *
     * @param  int  $uid
     * @return bool
     */
    public static function fresnsUserStatus(int $uid)
    {
        $userStatus = User::where('uid', $uid)->value('is_enable');

        if (empty($userStatus)) {
            return 'false';
        }

        return $userStatus == 0 ? 'true' : 'false';
    }

    /**
     * Determining user role permission.
     *
     * @param  int  $uid
     * @param  array  $permRoleIds
     * @return bool
     */
    public static function fresnsUserRolePermCheck(int $uid, array $permRoleIds)
    {
        $userId = PrimaryHelper::fresnsAccountIdByUid($uid);
        $userRoles = UserRole::where('user_id', $userId)->pluck('role_id')->toArray();

        return array_intersect($permRoleIds, $userRoles) ? 'true' : 'false';
    }

    /**
     * @param  int  $uid
     * @param  array  $gid
     * @return bool
     */
    public static function fresnsUserGroupAdminCheck(int $uid, array $gid)
    {
        $permission = Group::where('gid', $gid)->value('permission');
        $permissionArr = json_decode($permission, true);
        $isAdmin = UserHelper::fresnsUserRolePermCheck($uid, $permissionArr['admin_users']);

        return $isAdmin;
    }
}
