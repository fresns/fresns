<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Group;
use App\Models\GroupAdmin;
use App\Models\PostAllow;
use App\Models\Role;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserFollow;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class PermissionUtility
{
    // Get user content view perm permission
    public static function getUserContentViewPerm(?int $userId = null): array
    {
        $userExpireInfo = PermissionUtility::checkUserStatusOfSiteMode($userId);

        if (empty($userId) && $userExpireInfo['siteMode'] == 'public') {
            $item['type'] = 1;
            $item['dateLimit'] = null;

            return $item;
        }

        if (! $userExpireInfo['userStatus'] && $userExpireInfo['expireAfter'] == 1) {
            $item['type'] = 3;
            $item['dateLimit'] = $userExpireInfo['expireTime'];

            return $item;
        }

        if (! $userExpireInfo['userStatus'] && $userExpireInfo['expireAfter'] == 2) {
            $item['type'] = 2;
            $item['dateLimit'] = $userExpireInfo['expireTime'];

            return $item;
        }

        $item['type'] = 1;
        $item['dateLimit'] = null;

        return $item;
    }

    // Get user main role permission
    public static function getUserMainRolePerm(int $userId): array
    {
        $defaultRoleId = ConfigHelper::fresnsConfigByItemKey('default_role');
        $userRole = UserRole::where('user_id', $userId)->where('is_main', 1)->first();

        $roleId = $userRole->role_id ?? $defaultRoleId;
        $restoreRoleId = $userRole->restore_role_id ?? $defaultRoleId;
        $expireTime = strtotime($userRole->expired_at ?? null);
        $now = time();

        if (! empty($userRole) && $expireTime && $expireTime < $now) {
            $roleId = $restoreRoleId;
        }

        $rolePerm = Role::whereId($roleId)->isEnable()->value('permissions');
        if (empty($rolePerm)) {
            $roleId = null;
            $rolePerm = Role::whereId($defaultRoleId)->isEnable()->value('permissions') ?? [];
        }

        foreach ($rolePerm as $perm) {
            $permission['rid'] = $roleId;
            $permission[$perm['permKey']] = $perm['permValue'];
        }

        return $permission;
    }

    // Get group filter ids
    public static function getGroupFilterIds(?int $userId = null): array
    {
        $hiddenGroupIds = Group::where('type_find', Group::FIND_HIDDEN)->pluck('id')->toArray();

        if (empty($userId)) {
            return $hiddenGroupIds;
        }

        $followGroupIds = UserFollow::type(UserFollow::TYPE_GROUP)->where('user_id', $userId)->pluck('follow_id')->toArray();

        $filterIds = array_values(array_diff($hiddenGroupIds, $followGroupIds));

        return $filterIds;
    }

    // Get post filter by group ids
    public static function getPostFilterByGroupIds(?int $userId = null): array
    {
        $privateGroupIds = Group::where('type_mode', Group::MODE_PRIVATE)->pluck('id')->toArray();

        if (empty($userId)) {
            return $privateGroupIds;
        }

        $followGroupIds = UserFollow::type(UserFollow::TYPE_GROUP)->where('user_id', $userId)->pluck('follow_id')->toArray();

        $filterIds = array_values(array_diff($privateGroupIds, $followGroupIds));

        $blockGroupIds = UserBlock::type(UserBlock::TYPE_GROUP)->where('user_id', $userId)->pluck('block_id')->toArray();

        $filterGroupIdsArr = array_values(array_unique(array_merge($blockGroupIds, $filterIds)));

        return $filterGroupIdsArr;
    }

    // Check if the user belongs to the account
    public static function checkUserAffiliation(int $userId, int $accountId): bool
    {
        $userAccountId = User::where('id', $userId)->value('account_id');

        return $userAccountId == $accountId ? 'true' : 'false';
    }

    // Check user status of the site mode
    public static function checkUserStatusOfSiteMode(?int $userId = null): array
    {
        $modeConfig = ConfigHelper::fresnsConfigByItemKey('site_mode');

        $config['siteMode'] = $modeConfig;
        $config['userStatus'] = false;
        $config['expireTime'] = null;
        $config['expireAfter'] = ConfigHelper::fresnsConfigByItemKey('site_private_end_after');

        if (empty($userId)) {
            return $config;
        }

        if ($modeConfig == 'public') {
            $config['userStatus'] = true;

            return $config;
        }

        $userSet = User::where('id', $userId)->value('expired_at');

        $now = time();
        $expireTime = strtotime($userSet->expired_at);

        if ($expireTime && $expireTime < $now) {
            $config['userStatus'] = true;
            $config['expireTime'] = $userSet->expired_at;

            return $config;
        }

        return $config;
    }

    // Check user permissions
    public static function checkUserPerm(int $userId, array $permUserIds): bool
    {
        return in_array($userId, $permUserIds) ? 'true' : 'false';
    }

    // Check user role permissions
    public static function checkUserRolePerm(int $userId, array $permRoleIds): bool
    {
        $userRoles = UserRole::where('user_id', $userId)->where('expired_at', '<=', now())->pluck('role_id')->toArray();

        return array_intersect($userRoles, $permRoleIds) ? 'true' : 'false';
    }

    // Check user dialog permission
    public static function checkUserDialogPerm(int $receiveUserId, ?int $authUserId = null, ?string $langTag = null)
    {
        $configs = ConfigHelper::fresnsConfigByItemKeys(['dialog_status', 'dialog_files']);
        $receiveUser = PrimaryHelper::fresnsModelById('user', $receiveUserId);

        $info['status'] = $configs['dialog_status'];
        $info['files'] = $configs['dialog_files'];
        $info['code'] = 0;
        $info['message'] = 'ok';

        if (empty($authUserId)) {
            $info['status'] = false;
            $info['code'] = 31601;
            $info['message'] = ConfigUtility::getCodeMessage(31601, 'Fresns', $langTag);

            return  $info;
        }

        if (! $configs['dialog_status']) {
            $info['status'] = false;
            $info['code'] = 36600;
            $info['message'] = ConfigUtility::getCodeMessage(36600, 'Fresns', $langTag);

            return  $info;
        }

        if ($receiveUser->id == $authUserId) {
            $info['status'] = false;
            $info['code'] = 31602;
            $info['message'] = ConfigUtility::getCodeMessage(31602, 'Fresns', $langTag);

            return  $info;
        }

        if (! is_null($receiveUser->deleted_at)) {
            $info['status'] = false;
            $info['code'] = 35203;
            $info['message'] = ConfigUtility::getCodeMessage(35203, 'Fresns', $langTag);

            return  $info;
        }

        if (! $receiveUser->is_enable) {
            $info['status'] = false;
            $info['code'] = 35202;
            $info['message'] = ConfigUtility::getCodeMessage(35202, 'Fresns', $langTag);

            return  $info;
        }

        $authUserRolePerm = PermissionUtility::getUserMainRolePerm($receiveUser->id);
        if (! $authUserRolePerm['dialog']) {
            $info['status'] = false;
            $info['code'] = 36114;
            $info['message'] = ConfigUtility::getCodeMessage(36114, 'Fresns', $langTag);

            return  $info;
        }

        $checkBlock = InteractiveUtility::checkUserBlock(InteractiveUtility::TYPE_USER, $authUserId, $receiveUser->id);
        if ($receiveUser->dialog_limit == 4 || $checkBlock) {
            $info['status'] = false;
            $info['code'] = 36608;
            $info['message'] = ConfigUtility::getCodeMessage(36608, 'Fresns', $langTag);

            return  $info;
        }

        $checkFollow = InteractiveUtility::checkUserFollow(InteractiveUtility::TYPE_USER, $receiveUser->id, $authUserId);
        $authUserVerifiedStatus = User::where('id', $authUserId)->value('verified_status') ?? 0;
        if ($receiveUser->dialog_limit == 3 && ! $checkFollow && ! $authUserVerifiedStatus) {
            $info['status'] = false;
            $info['code'] = 36607;
            $info['message'] = ConfigUtility::getCodeMessage(36607, 'Fresns', $langTag);

            return  $info;
        }

        if ($receiveUser->dialog_limit == 2 && ! $checkFollow) {
            $info['status'] = false;
            $info['code'] = 36606;
            $info['message'] = ConfigUtility::getCodeMessage(36606, 'Fresns', $langTag);

            return  $info;
        }

        return  $info;
    }

    // Check if the user is a group administrator
    public static function checkUserGroupAdmin(int $groupId, int $userId)
    {
        $groupAdminArr = GroupAdmin::where('group_id', $groupId)->pluck('user_id')->toArray();

        return in_array($userId, $groupAdminArr) ? 'true' : 'false';
    }

    // Check if the user has group publishing permissions
    public static function checkUserGroupPublishPerm(int $groupId, array $permissions, ?int $userId = null)
    {
        $perm['allowPost'] = false;
        $perm['reviewPost'] = $permissions['publish_post_review'];
        $perm['allowComment'] = false;
        $perm['reviewComment'] = $permissions['publish_comment_review'];
        $perms = $perm;

        if (empty($userId)) {
            return $perms;
        }

        if ($permissions['publish_post'] == 1 && $permissions['publish_comment'] == 1) {
            $perm['allowPost'] = true;
            $perm['allowComment'] = true;

            return $perms;
        }

        $checkGroupAdmin = static::checkUserGroupAdmin($groupId, $userId);

        if ($checkGroupAdmin) {
            $adminPerm['allowPost'] = true;
            $adminPerm['reviewPost'] = false;
            $adminPerm['allowComment'] = true;
            $adminPerm['reviewComment'] = false;

            return $adminPerm;
        }

        $allowPost = match ($permissions['publish_post']) {
            1 => true,
            2 => InteractiveUtility::checkUserFollow(InteractiveUtility::TYPE_GROUP, $groupId, $userId),
            3 => static::checkUserRolePerm($userId, $permissions['publish_post_roles']),
            4 => false,
            default => false,
        };

        $allowComment = match ($permissions['publish_comment']) {
            1 => true,
            2 => InteractiveUtility::checkUserFollow(InteractiveUtility::TYPE_GROUP, $groupId, $userId),
            3 => static::checkUserRolePerm($userId, $permissions['publish_comment_roles']),
            4 => false,
            default => false,
        };

        $perm['allowPost'] = $allowPost;
        $perm['allowComment'] = $allowComment;

        return $perms;
    }

    // Check post allow
    public static function checkPostAllow(int $postId, int $userId): bool
    {
        $allowUsers = PostAllow::where('post_id', $postId)->where('type', 1)->pluck('object_id')->toArray();
        $checkUser = PermissionUtility::checkUserPerm($userId, $allowUsers);
        if ($checkUser) {
            return true;
        } else {
            $allowRoles = PostAllow::where('post_id', $postId)->where('type', 2)->pluck('object_id')->toArray();

            return PermissionUtility::checkUserRolePerm($userId, $allowRoles);
        }
    }

    // Check post comment perm
    public static function checkPostCommentPerm(?string $pidOrPostId = null, ?int $userId = null): bool
    {
        if (empty($pidOrPostId) || empty($userId)) {
            return false;
        }

        if (is_int($pidOrPostId)) {
            $post = PrimaryHelper::fresnsModelById('post', $pidOrPostId);
        } else {
            $post = PrimaryHelper::fresnsModelByFsid('post', $pidOrPostId);
        }

        if (empty($post)) {
            return false;
        }

        if (! $post->postAppend->is_comment) {
            return false;
        }

        $user = PrimaryHelper::fresnsModelById('user', $post->user_id);

        if ($user->comment_limit != 1) {
            if ($user->comment_limit == 4) {
                return false;
            }

            $checkUserFollow = InteractiveUtility::checkUserFollowMe($userId, $user->id);

            if (! $checkUserFollow) {
                return false;
            }

            $checkUserVerified = PrimaryHelper::fresnsModelById('user', $userId)->verified_status;
            if ($user->comment_limit == 3 && ! $checkUserVerified) {
                return false;
            }
        }

        return true;
    }

    // Check content edit perm
    public static function checkContentEditPerm(Carbon $createDateTime, int $editTimeConfig, ?string $timezone = null, ?string $langTag = null): array
    {
        $editableDateTime = $createDateTime->addMinutes($editTimeConfig);
        $editableSecond = $editableDateTime->timestamp - time();
        $editableTimeMinute = intval($editableSecond / 60);
        $editableTimeSecond = $editableSecond % 60;

        $editableStatus = true;
        if ($editableTimeMinute < 0) {
            $editableStatus = false;
            $editableTimeMinute = '00';
            $editableTimeSecond = '00';
        }

        $perm['editableStatus'] = $editableStatus;
        $perm['editableTime'] = "{$editableTimeMinute}:{$editableTimeSecond}";
        $perm['deadlineTime'] = DateHelper::fresnsFormatDateTime($editableDateTime->format('Y-m-d H:i:s'), $timezone, $langTag);

        return $perm;
    }
}
