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
use App\Helpers\StrHelper;
use App\Models\Comment;
use App\Models\Group;
use App\Models\GroupAdmin;
use App\Models\Post;
use App\Models\PostAllow;
use App\Models\Role;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserFollow;
use App\Models\UserRole;
use Carbon\Carbon;

class PermissionUtility
{
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

        return $userAccountId == $accountId ? true : false;
    }

    // Check user permissions
    public static function checkUserPerm(int $userId, array $permUserIds): bool
    {
        return in_array($userId, $permUserIds) ? true : false;
    }

    // Check user role permissions
    public static function checkUserRolePerm(int $userId, array $permRoleIds): bool
    {
        $userRoles = UserRole::where('user_id', $userId)->where('expired_at', '<=', now())->pluck('role_id')->toArray();

        return array_intersect($userRoles, $permRoleIds) ? true : false;
    }

    // Check user dialog permission
    public static function checkUserDialogPerm(int $receiveUserId, ?int $authUserId = null, ?string $langTag = null)
    {
        $dialogStatus = ConfigHelper::fresnsConfigByItemKey('dialog_status');
        $receiveUser = PrimaryHelper::fresnsModelById('user', $receiveUserId);

        $info['status'] = $dialogStatus;
        $info['code'] = 0;
        $info['message'] = 'ok';

        if (empty($authUserId)) {
            $info['status'] = false;
            $info['code'] = 31601;
            $info['message'] = ConfigUtility::getCodeMessage(31601, 'Fresns', $langTag);

            return  $info;
        }

        if (! $dialogStatus) {
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

        return in_array($userId, $groupAdminArr) ? true : false;
    }

    // Check if the user has group publishing permissions
    public static function checkUserGroupPublishPerm(int $groupId, array $permissions, ?int $userId = null)
    {
        $permConfig = [
            'publish_post' => $permissions['publish_post'] ?? 1,
            'publish_post_roles' => $permissions['publish_post_roles'] ?? [],
            'publish_post_review' => $permissions['publish_post_review'] ?? false,
            'publish_comment' => $permissions['publish_comment'] ?? 1,
            'publish_comment_roles' => $permissions['publish_comment_roles'] ?? [],
            'publish_comment_review' => $permissions['publish_comment_review'] ?? false,
        ];

        $perm['allowPost'] = false;
        $perm['reviewPost'] = $permConfig['publish_post_review'];
        $perm['allowComment'] = false;
        $perm['reviewComment'] = $permConfig['publish_comment_review'];
        $perms = $perm;

        if (empty($userId)) {
            return $perms;
        }

        if ($permConfig['publish_post'] == 1 && $permConfig['publish_comment'] == 1) {
            $perms['allowPost'] = true;
            $perms['allowComment'] = true;

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

        $allowPost = match ($permConfig['publish_post']) {
            1 => true,
            2 => InteractiveUtility::checkUserFollow(InteractiveUtility::TYPE_GROUP, $groupId, $userId),
            3 => static::checkUserRolePerm($userId, $permConfig['publish_post_roles']),
            4 => false,
            default => false,
        };

        $allowComment = match ($permConfig['publish_comment']) {
            1 => true,
            2 => InteractiveUtility::checkUserFollow(InteractiveUtility::TYPE_GROUP, $groupId, $userId),
            3 => static::checkUserRolePerm($userId, $permConfig['publish_comment_roles']),
            4 => false,
            default => false,
        };

        $perms['allowPost'] = $allowPost;
        $perms['allowComment'] = $allowComment;

        return $perms;
    }

    // Check post allow
    public static function checkPostAllow(int $postId, ?int $userId = null): bool
    {
        if (empty($userId)) {
            return false;
        }

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

        if (StrHelper::isPureInt($pidOrPostId)) {
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

            $checkUserFollow = InteractiveUtility::checkUserFollow(InteractiveUtility::TYPE_USER, $user->id, $userId);

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

    // Check content is can edit
    // $type = post or comment
    public static function checkContentIsCanEdit(string $type, Carbon $createTime, int $stickyState, int $digestState, string $langTag, string $timezone): bool
    {
        $editConfig = ConfigHelper::fresnsConfigByItemKeys([
            "{$type}_edit",
            "{$type}_edit_time_limit",
            "{$type}_edit_sticky_limit",
            "{$type}_edit_digest_limit",
        ]);

        if (! $editConfig["{$type}_edit"]) {
            return false;
        }

        $checkContentEditPerm = static::checkContentEditPerm($createTime, $editConfig["{$type}_edit_time_limit"], $timezone, $langTag);

        if (! $checkContentEditPerm['editableStatus']) {
            return false;
        }

        if ($digestState != 1) {
            if (! $editConfig["{$type}_edit_digest_limit"]) {
                return false;
            }
        }

        if ($type == 'post' && $stickyState != 1) {
            if (! $editConfig["{$type}_edit_sticky_limit"]) {
                return false;
            }
        }

        if ($type == 'comment' && $stickyState == 1) {
            if (! $editConfig["{$type}_edit_sticky_limit"]) {
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

    // Check content interval time
    public static function checkContentIntervalTime(int $userId, string $type): bool
    {
        $model = match ($type) {
            'post' => Post::where('user_id', $userId)->latest()->first(),
            'comment' => Comment::where('user_id', $userId)->latest()->first(),
        };

        if (! $model) {
            return true;
        }

        $rolePerm = PermissionUtility::getUserMainRolePerm($userId);
        $interval = $rolePerm["{$type}_minute_interval"] ?? 0;

        if ($interval == 0) {
            return true;
        }

        if ($model->created_at < now()->subMinutes($interval)) {
            return true;
        }

        return false;
    }
}
