<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\InteractionHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Comment;
use App\Models\Group;
use App\Models\PluginUsage;
use App\Models\Post;
use App\Models\PostAllow;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserFollow;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class PermissionUtility
{
    // Get user main role
    public static function getUserMainRole(int $userId, ?string $langTag = null): array
    {
        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_user_{$userId}_main_role_{$langTag}";
        $cacheTags = ['fresnsUsers', 'fresnsUserRoles'];

        $mainRoleConfig = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($mainRoleConfig)) {
            $defaultRoleId = ConfigHelper::fresnsConfigByItemKey('default_role');

            $userRole = UserRole::where('user_id', $userId)->where('is_main', 1)->first();

            $roleId = $userRole?->role_id;
            $restoreRoleId = $userRole?->restore_role_id ?? $defaultRoleId;
            $expiryDateTime = $userRole?->expired_at;

            $expireTime = strtotime($expiryDateTime);
            $now = time();

            if ($expireTime && $expireTime < $now) {
                $roleId = $restoreRoleId;
                $expiryDateTime = null;
            }

            $mainRoleConfig = [
                'rid' => $roleId ?? $defaultRoleId,
                'expiryDateTime' => $expiryDateTime,
            ];

            CacheHelper::put($mainRoleConfig, $cacheKey, $cacheTags);
        }

        $mainRole = InteractionHelper::fresnsRoleInfo($mainRoleConfig['rid'], $langTag);
        $mainRole['isMain'] = true;
        $mainRole['expiryDateTime'] = $mainRoleConfig['expiryDateTime'];

        return $mainRole;
    }

    // Get user roles
    public static function getUserRoles(?int $userId = null, ?string $langTag = null): array
    {
        if (empty($userId)) {
            return [];
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_user_{$userId}_roles_{$langTag}";
        $cacheTags = ['fresnsUsers', 'fresnsUserRoles'];

        $roleAllConfig = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($roleAllConfig)) {
            $roleArr1 = UserRole::where('user_id', $userId)->where('is_main', 0)->where('expired_at', '<', now());
            $roleArr2 = UserRole::where('user_id', $userId)->where('is_main', 0)->whereNull('expired_at');

            $roleArr = $roleArr1->union($roleArr2)->get();

            $roleList = [];
            foreach ($roleArr as $role) {
                $item['rid'] = $role->role_id;
                $item['expiryDateTime'] = $role->expired_at;

                $roleList[] = $item;
            }

            $roleAllConfig = $roleList;

            CacheHelper::put($roleAllConfig, $cacheKey, $cacheTags);
        }

        $roleListArr = [];
        foreach ($roleAllConfig as $config) {
            $role = InteractionHelper::fresnsRoleInfo($config['rid'], $langTag);
            $role['expiryDateTime'] = $config['expiryDateTime'];

            unset($role['permissions']);

            $roleListArr[] = $role;
        }

        $mainRole = PermissionUtility::getUserMainRole($userId, $langTag);
        unset($mainRole['permissions']);

        $mainRoleArr = [$mainRole];

        $roleAllList = array_merge($mainRoleArr, $roleListArr);

        return $roleAllList;
    }

    // Get group filter ids
    public static function getGroupFilterIds(?int $userId = null): array
    {
        $guestCacheKey = 'fresns_filter_groups_by_guest';
        $userCacheKey = "fresns_filter_groups_by_user_{$userId}";
        $cacheTags = ['fresnsGroups', 'fresnsGroupConfigs', 'fresnsUsers', 'fresnsUserInteractions'];

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($guestCacheKey);
        if ($isKnownEmpty) {
            $hiddenGroupIds = [];
        } else {
            // get cache
            $hiddenGroupIds = CacheHelper::get($guestCacheKey, $cacheTags);

            if (empty($hiddenGroupIds)) {
                $hiddenGroupIds = Group::where('type_find', Group::FIND_HIDDEN)->pluck('id')->toArray();

                CacheHelper::put($hiddenGroupIds, $guestCacheKey, $cacheTags);
            }
        }

        if (empty($userId)) {
            return $hiddenGroupIds;
        }

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($userCacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        // get cache
        $filterIds = CacheHelper::get($userCacheKey, $cacheTags);

        if (empty($filterIds)) {
            $followGroupIds = UserFollow::type(UserFollow::TYPE_GROUP)->where('user_id', $userId)->pluck('follow_id')->toArray();

            $filterIds = array_values(array_diff($hiddenGroupIds, $followGroupIds));

            CacheHelper::put($filterIds, $userCacheKey, $cacheTags);
        }

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

    // Check user conversation permission
    public static function checkUserConversationPerm(int $receiveUserId, ?int $authUserId = null, ?string $langTag = null): int
    {
        // User not logged in, Unable to use
        if (empty($authUserId)) {
            return 31601;
        }

        $conversationStatus = ConfigHelper::fresnsConfigByItemKey('conversation_status');

        // Conversation function is not enabled and cannot be used
        if (! $conversationStatus) {
            return 36600;
        }

        $receiveUser = PrimaryHelper::fresnsModelById('user', $receiveUserId);

        // Wrong user or record not exist
        if ($receiveUser->id == $authUserId) {
            return 31602;
        }

        // The user has been logged out
        if (! is_null($receiveUser->deleted_at)) {
            return 35203;
        }

        // Current user has been banned
        if (! $receiveUser->is_enable) {
            return 35202;
        }

        $authUserRolePerm = PermissionUtility::getUserMainRole($authUserId, $langTag)['permissions'];
        $conversationConfig = $authUserRolePerm['conversation'] ?? false;

        // Current role has no conversation message permission
        if (! $conversationConfig) {
            return 36116;
        }

        $checkBlock = InteractionUtility::checkUserBlock(InteractionUtility::TYPE_USER, $authUserId, $receiveUser->id);

        // The other party has set the conversation off function
        if ($receiveUser->conversation_limit == 4 || $checkBlock) {
            return 36608;
        }

        $checkFollow = InteractionUtility::checkUserFollow(InteractionUtility::TYPE_USER, $receiveUser->id, $authUserId);
        $authUserVerifiedStatus = User::where('id', $authUserId)->value('verified_status') ?? 0;

        // The user has set that only the users he follows and the verified users can send messages
        if ($receiveUser->conversation_limit == 3 && ! $checkFollow && ! $authUserVerifiedStatus) {
            return 36607;
        }

        // The user has set that only the users he follows can send messages
        if ($receiveUser->conversation_limit == 2 && ! $checkFollow) {
            return 36606;
        }

        return 0;
    }

    // Check if the user is a group administrator
    public static function checkUserGroupAdmin(int $groupId, ?int $userId = null)
    {
        if (empty($userId)) {
            return false;
        }

        $group = PrimaryHelper::fresnsModelById('group', $groupId);
        $groupAdminArr = $group->admins->pluck('id')->toArray();

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
            2 => InteractionUtility::checkUserFollow(InteractionUtility::TYPE_GROUP, $groupId, $userId),
            3 => static::checkUserRolePerm($userId, $permConfig['publish_post_roles']),
            4 => false,
            default => false,
        };

        $allowComment = match ($permConfig['publish_comment']) {
            1 => true,
            2 => InteractionUtility::checkUserFollow(InteractionUtility::TYPE_GROUP, $groupId, $userId),
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

        $uid = PrimaryHelper::fresnsModelById('user', $userId)?->uid;
        $pid = PrimaryHelper::fresnsModelById('post', $postId)?->pid;

        if (empty($uid) || empty($pid)) {
            return false;
        }

        $cacheKey = "fresns_api_post_{$pid}_allow_{$uid}";
        $cacheTags = ['fresnsPosts', 'fresnsPostData', 'fresnsUsers', 'fresnsUserData'];

        // get cache
        $checkPostAllow = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($checkPostAllow)) {
            $allowUsers = PostAllow::where('post_id', $postId)->where('type', 1)->pluck('object_id')->toArray();
            $checkUser = PermissionUtility::checkUserPerm($userId, $allowUsers);

            if ($checkUser) {
                $checkPostAllow = true;
            } else {
                $allowRoles = PostAllow::where('post_id', $postId)->where('type', 2)->pluck('object_id')->toArray();

                $checkPostAllow = PermissionUtility::checkUserRolePerm($userId, $allowRoles);
            }

            CacheHelper::put($checkPostAllow, $cacheKey, $cacheTags);
        }

        return $checkPostAllow;
    }

    // Check post comment perm
    public static function checkPostCommentPerm(?string $pidOrPostId = null, ?int $userId = null): array
    {
        $commentPerm['status'] = false;
        $commentPerm['code'] = 37300;

        if (empty($pidOrPostId)) {
            return $commentPerm;
        }

        if (empty($userId)) {
            $commentPerm['code'] = 31602;

            return $commentPerm;
        }

        if (StrHelper::isPureInt($pidOrPostId)) {
            $post = PrimaryHelper::fresnsModelById('post', $pidOrPostId);
        } else {
            $post = PrimaryHelper::fresnsModelByFsid('post', $pidOrPostId);
        }

        if (empty($post)) {
            return $commentPerm;
        }

        if (! $post->postAppend->is_comment) {
            $commentPerm['code'] = 38108;

            return $commentPerm;
        }

        $user = PrimaryHelper::fresnsModelById('user', $post->user_id);

        if ($userId != $post->user_id && $user->comment_limit != 1) {
            if ($user->comment_limit == 4) {
                $commentPerm['code'] = 38211;

                return $commentPerm;
            }

            $checkUserFollow = InteractionUtility::checkUserFollow(InteractionUtility::TYPE_USER, $post->user_id, $userId);
            if (! $checkUserFollow) {
                $commentPerm['code'] = 38209;

                return $commentPerm;
            }

            $checkUserVerified = PrimaryHelper::fresnsModelById('user', $userId)->verified_status;
            if ($user->comment_limit == 3 && ! $checkUserVerified) {
                $commentPerm['code'] = 38210;

                return $commentPerm;
            }
        }

        $commentPerm['status'] = true;
        $commentPerm['code'] = 0;

        return $commentPerm;
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

        $rolePerm = PermissionUtility::getUserMainRole($userId)['permissions'];
        $interval = $rolePerm["{$type}_second_interval"] ?? 0;

        if ($interval == 0) {
            return true;
        }

        if ($model->created_at->addSeconds($interval) < now()) {
            return true;
        }

        return false;
    }

    // Check extend perm
    public static function checkExtendPerm(string $unikey, string $scene, ?int $groupId = null, ?int $userId = null): bool
    {
        $usageType = match ($scene) {
            'postEditor' => PluginUsage::TYPE_EDITOR,
            'commentEditor' => PluginUsage::TYPE_EDITOR,
            'manage' => PluginUsage::TYPE_MANAGE,
            'groupExtension' => PluginUsage::TYPE_GROUP,
            'profileExtension' => PluginUsage::TYPE_PROFILE,
            'featureExtension' => PluginUsage::TYPE_FEATURE,
            default => null,
        };

        if (empty($usageType) || empty($userId)) {
            return false;
        }

        // get usage list
        if ($usageType == PluginUsage::TYPE_GROUP && empty($groupId)) {
            return false;
        }

        // check group admin
        $checkGroupAdmin = self::checkExtendPermByGroupAdmin($unikey, $usageType, $groupId, $userId);

        if ($checkGroupAdmin) {
            return true;
        }

        // check role
        $checkRole = self::checkExtendPermByRole($unikey, $usageType, $groupId, $userId);

        return $checkRole;
    }

    private static function checkExtendPermByGroupAdmin(string $unikey, int $usageType, ?int $groupId = null, ?int $userId = null): bool
    {
        if (empty($groupId)) {
            return false;
        }

        // get usage list
        if ($usageType == PluginUsage::TYPE_GROUP) {
            $usages = PluginUsage::where('usage_type', $usageType)
                ->where('plugin_unikey', $unikey)
                ->where('group_id', $groupId)
                ->where('is_group_admin', 1)
                ->where('is_enable', 1)
                ->get();
        } else {
            $usages = PluginUsage::where('usage_type', $usageType)
                ->where('plugin_unikey', $unikey)
                ->where('is_group_admin', 1)
                ->where('is_enable', 1)
                ->get();
        }

        if (empty($usages)) {
            return false;
        }

        $checkGroupAdmin = PermissionUtility::checkUserGroupAdmin($groupId, $userId);

        if (! $checkGroupAdmin) {
            return false;
        }

        return true;
    }

    private static function checkExtendPermByRole(string $unikey, int $usageType, ?int $groupId = null, ?int $userId = null): bool
    {
        // get usage list
        if ($usageType == PluginUsage::TYPE_GROUP) {
            $usages = PluginUsage::where('usage_type', $usageType)
                ->where('plugin_unikey', $unikey)
                ->where('group_id', $groupId)
                ->where('is_group_admin', 0)
                ->where('is_enable', 1)
                ->get();
        } else {
            $usages = PluginUsage::where('usage_type', $usageType)
                ->where('plugin_unikey', $unikey)
                ->where('is_group_admin', 0)
                ->where('is_enable', 1)
                ->get();
        }

        if (empty($usages)) {
            return false;
        }

        // check role
        $roles = [];
        foreach ($usages as $usage) {
            if (empty($usage->roles)) {
                continue;
            }

            $roles[] = explode(',', $usage->roles);
        }

        $roleArr = array_unique(Arr::collapse($roles));

        if ($roleArr) {
            $userRoleArr = PermissionUtility::getUserRoles($userId);
            $userRoleIdArr = array_column($userRoleArr, 'rid');

            $intersect = array_intersect($roleArr, $userRoleIdArr);

            return empty($intersect) ? false : true;
        }

        return true;
    }
}
