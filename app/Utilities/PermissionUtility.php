<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\InteractionHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\AppUsage;
use App\Models\Comment;
use App\Models\Group;
use App\Models\Mention;
use App\Models\Post;
use App\Models\PostAuth;
use App\Models\User;
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

        $cacheKey = "fresns_user_{$userId}_main_role";
        $cacheTag = 'fresnsUsers';

        $mainRoleConfig = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($mainRoleConfig)) {
            $defaultRoleId = ConfigHelper::fresnsConfigByItemKey('default_role');

            $userRole = UserRole::where('user_id', $userId)->where('is_main', true)->first();

            $roleId = $userRole?->role_id;
            $defaultRoleId = $userRole?->restore_role_id ?? $defaultRoleId;

            $expiryDateTime = $userRole?->expired_at;

            if ($expiryDateTime && $userRole?->expired_at?->isPast()) {
                $roleId = $defaultRoleId;
                $expiryDateTime = null;
            }

            $mainRoleConfig = [
                'id' => $roleId ?? $defaultRoleId,
                'expiryDateTime' => $expiryDateTime,
            ];

            CacheHelper::put($mainRoleConfig, $cacheKey, $cacheTag);
        }

        $mainRole = InteractionHelper::fresnsRoleInfo($mainRoleConfig['id'], $langTag);
        $mainRole['expiryDateTime'] = $mainRoleConfig['expiryDateTime'];

        return $mainRole;
    }

    // Get user roles
    public static function getUserRoles(?int $userId = null): array
    {
        if (empty($userId)) {
            return [];
        }

        $cacheKey = "fresns_user_{$userId}_roles";
        $cacheTag = 'fresnsUsers';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        $userRoles = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($userRoles)) {
            $roleArr1 = UserRole::where('user_id', $userId)->where('is_main', 0)->where('expired_at', '<', now());
            $roleArr2 = UserRole::where('user_id', $userId)->where('is_main', 0)->whereNull('expired_at');

            $roleArr = $roleArr1->union($roleArr2)->get();

            $userRoles = [];
            foreach ($roleArr as $role) {
                $item['id'] = $role->role_id;
                $item['expiryDateTime'] = $role->expired_at;

                $userRoles[] = $item;
            }

            CacheHelper::put($userRoles, $cacheKey, $cacheTag);
        }

        return $userRoles;
    }

    // Get group list filter ids
    public static function getGroupListFilterIdArr(?int $userId = null): ?array
    {
        if (empty($userId)) {
            return [];
        }

        $cacheKey = "fresns_group_list_filter_ids_by_user_{$userId}";
        $cacheTags = ['fresnsGroups', 'fresnsUsers'];

        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        $groupIds = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($groupIds)) {
            // private hidden groups
            $hiddenGroups = Group::where('privacy', Group::PRIVACY_PRIVATE)->where('visibility', Group::VISIBILITY_HIDDEN)->get();

            $userRole = PermissionUtility::getUserMainRole($userId);

            $privateIds = [];
            foreach ($hiddenGroups as $group) {
                $permissions = $group->permissions;

                $whitelistRoles = $permissions['private_whitelist_roles'] ?? [];

                if ($whitelistRoles && in_array($userRole['id'], $whitelistRoles)) {
                    continue;
                }

                $privateIds[] = $group->id;
            }

            // block groups
            $blockIds = UserFollow::markType(UserFollow::MARK_TYPE_BLOCK)->type(UserFollow::TYPE_GROUP)->where('user_id', $userId)->pluck('follow_id')->toArray();

            $groupIds = array_values(array_unique(array_merge($privateIds, $blockIds)));

            if ($groupIds) {
                // follow groups
                $followIds = InteractionUtility::getFollowIdArr(UserFollow::TYPE_GROUP, $userId);

                $groupIds = array_values(array_diff($groupIds, $followIds));
            }

            CacheHelper::put($groupIds, $cacheKey, $cacheTags);
        }

        return $groupIds;
    }

    // Get group content filter ids
    public static function getGroupContentFilterIdArr(?int $userId = null): ?array
    {
        $privateIds = InteractionUtility::getPrivateGroupIdArr();

        if (empty($userId)) {
            return $privateIds;
        }

        $cacheKey = "fresns_group_content_filter_ids_by_user_{$userId}";
        $cacheTags = ['fresnsGroups', 'fresnsUsers'];

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        $groupIds = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($groupIds)) {
            $privateGroupIdArr = [];

            if ($privateIds) {
                $groups = Group::whereIn('id', $privateIds)->get();

                $userRole = PermissionUtility::getUserMainRole($userId);

                foreach ($groups as $group) {
                    $permissions = $group->permissions;

                    $whitelistRoles = $permissions['private_whitelist_roles'] ?? [];

                    if ($whitelistRoles && in_array($userRole['id'], $whitelistRoles)) {
                        continue;
                    }

                    $privateGroupIdArr[] = $group->id;
                }
            }

            $blockGroupIds = InteractionUtility::getBlockIdArr(UserFollow::TYPE_GROUP, $userId);
            $filterGroupIdsArr = array_values(array_unique(array_merge($blockGroupIds, $privateGroupIdArr)));

            $followGroupIds = InteractionUtility::getFollowIdArr(UserFollow::TYPE_GROUP, $userId);

            $groupIds = array_values(array_diff($filterGroupIdsArr, $followGroupIds));

            CacheHelper::put($groupIds, $cacheKey, $cacheTags);
        }

        return $groupIds;
    }

    // get group content date limit
    public static function getGroupContentDateLimit(?int $groupId = null, ?int $authUserId = null): array
    {
        $checkResp = [
            'code' => 0,
            'datetime' => null,
        ];

        if (empty($groupId)) {
            return $checkResp;
        }

        $group = PrimaryHelper::fresnsModelById('group', $groupId);

        if ($group->privacy == Group::PRIVACY_PUBLIC) {
            return $checkResp;
        }

        if (empty($authUserId)) {
            $checkResp['code'] = 37103;

            return $checkResp;
        }

        $userRole = PermissionUtility::getUserMainRole($authUserId);
        $whitelistRoles = $group->permissions['private_whitelist_roles'] ?? [];

        if ($whitelistRoles && in_array($userRole['id'], $whitelistRoles)) {
            return $checkResp;
        }

        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_GROUP, $groupId, $authUserId);
        if (! $interactionStatus['followStatus']) {
            $checkResp['code'] = 37103;

            return $checkResp;
        }

        if ($group->private_end_after == Group::PRIVATE_OPTION_UNRESTRICTED) {
            return $checkResp;
        }

        if ($group->private_end_after == Group::PRIVATE_OPTION_HIDE_ALL) {
            if (! $interactionStatus['followExpiryDateTime']) {
                $checkResp['code'] = 37105;

                return $checkResp;
            }

            $now = time();
            $expiryTime = strtotime($interactionStatus['followExpiryDateTime']);
            if ($expiryTime < $now) {
                $checkResp['code'] = 37105;

                return $checkResp;
            }
        }

        $checkResp['datetime'] = $interactionStatus['followExpiryDateTime'];

        return $checkResp;
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
        return in_array($userId, $permUserIds);
    }

    // Check user role permissions
    public static function checkUserRolePerm(int $userId, ?array $permRoleIds = []): bool
    {
        if (empty($permRoleIds)) {
            return false;
        }

        $userRolesByExpired = UserRole::where('user_id', $userId)->where('expired_at', '<=', now())->pluck('role_id')->toArray();
        $userRolesByExpiredNull = UserRole::where('user_id', $userId)->whereNull('expired_at')->pluck('role_id')->toArray();

        $userRoles = array_merge($userRolesByExpired, $userRolesByExpiredNull);

        return array_intersect($userRoles, $permRoleIds) ? true : false;
    }

    // Check user role private whitelist
    public static function checkUserRolePrivateWhitelist(int $userId): bool
    {
        $whitelist = ConfigHelper::fresnsConfigByItemKey('site_private_whitelist_roles');
        if (empty($whitelist)) {
            return false;
        }

        $userRole = PermissionUtility::getUserMainRole($userId);

        return in_array($userRole['id'], $whitelist);
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
        if (! $receiveUser->is_enabled) {
            return 35202;
        }

        $authUserRolePerm = PermissionUtility::getUserMainRole($authUserId, $langTag)['permissions'];
        $conversationConfig = $authUserRolePerm['conversation'] ?? false;

        // Current role has no conversation message permission
        if (! $conversationConfig) {
            return 36116;
        }

        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_USER, $authUserId, $receiveUser->id);
        $checkFollow = $interactionStatus['followStatus'];
        $checkBlock = $interactionStatus['blockStatus'];

        // The other party has set the conversation off function
        if ($receiveUser->conversation_policy == User::POLICY_NO_ONE_IS_ALLOWED || $checkBlock) {
            return 36608;
        }

        $authUser = PrimaryHelper::fresnsModelById('user', $authUserId);

        // The user has set that only the users he follows and the verified users can send messages
        if ($receiveUser->conversation_policy == User::POLICY_PEOPLE_YOU_FOLLOW_OR_VERIFIED && ! $checkFollow && ! $authUser?->verified_status) {
            return 36607;
        }

        // The user has set that only the users he follows can send messages
        if ($receiveUser->conversation_policy == User::POLICY_PEOPLE_YOU_FOLLOW && ! $checkFollow) {
            return 36606;
        }

        return 0;
    }

    // Check if the user is a group administrator
    public static function checkUserGroupAdmin(int $groupId, ?int $userId = null): bool
    {
        if (empty($userId)) {
            return false;
        }

        $group = PrimaryHelper::fresnsModelById('group', $groupId);
        $groupAdminArr = $group->admins->pluck('id')->toArray();

        return in_array($userId, $groupAdminArr);
    }

    // Check if the user has group publishing permissions
    public static function checkUserGroupPublishPerm(int $groupId, array $permissions, ?int $userId = null): array
    {
        $permConfig = [
            'can_publish' => $permissions['can_publish'] ?? true,
            'publish_post' => (int) ($permissions['publish_post'] ?? 1),
            'publish_post_roles' => $permissions['publish_post_roles'] ?? [],
            'publish_post_review' => $permissions['publish_post_review'] ?? false,
            'publish_comment' => (int) ($permissions['publish_comment'] ?? 1),
            'publish_comment_roles' => $permissions['publish_comment_roles'] ?? [],
            'publish_comment_review' => $permissions['publish_comment_review'] ?? false,
        ];

        $perm['canPublish'] = false;
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

        $checkFollowGroup = false;
        if ($permConfig['publish_post'] == 2 || $permConfig['publish_comment'] == 2) {
            $checkFollowGroup = (bool) UserFollow::where('user_id', $userId)->markType(UserFollow::MARK_TYPE_FOLLOW)->type(UserFollow::TYPE_GROUP)->where('follow_id', $groupId)->first();
        }

        $allowPost = match ($permConfig['publish_post']) {
            1 => true,
            2 => $checkFollowGroup,
            3 => static::checkUserRolePerm($userId, $permConfig['publish_post_roles']),
            4 => false,
            default => false,
        };

        $allowComment = match ($permConfig['publish_comment']) {
            1 => true,
            2 => $checkFollowGroup,
            3 => static::checkUserRolePerm($userId, $permConfig['publish_comment_roles']),
            4 => false,
            default => false,
        };

        $perms['allowPost'] = $allowPost;
        $perms['allowComment'] = $allowComment;

        return $perms;
    }

    // Check post auth
    public static function checkPostAuth(int $postId, ?int $userId = null): bool
    {
        if (empty($userId)) {
            return false;
        }

        $cacheKey = "fresns_user_post_auth_{$postId}_{$userId}";
        $cacheTag = 'fresnsUsers';

        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return false;
        }

        // get cache
        $checkPostAuth = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($checkPostAuth)) {
            $allowUsers = PostAuth::where('post_id', $postId)->where('type', 1)->pluck('target_id')->toArray();
            $checkUser = PermissionUtility::checkUserPerm($userId, $allowUsers);

            if ($checkUser) {
                $checkPostAuth = true;
            } else {
                $allowRoles = PostAuth::where('post_id', $postId)->where('type', 2)->pluck('target_id')->toArray();

                $checkPostAuth = PermissionUtility::checkUserRolePerm($userId, $allowRoles);
            }

            CacheHelper::put($checkPostAuth, $cacheKey, $cacheTag);
        }

        return $checkPostAuth;
    }

    // Check post comment perm
    public static function checkPostCommentPerm(?string $pidOrPostId = null, ?int $userId = null): array
    {
        $commentPerm['status'] = false;
        $commentPerm['code'] = 37400;

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

        if ($post->user_id == $userId) {
            return [
                'status' => true,
                'code' => 0,
            ];
        }

        $postPermissions = $post->permissions;
        $postCommentPolicy = $postPermissions['commentConfig']['policy'] ?? User::POLICY_EVERYONE;

        if ($postCommentPolicy == User::POLICY_NO_ONE_IS_ALLOWED) {
            $commentPerm['code'] = 38108;

            return $commentPerm;
        }

        if ($postCommentPolicy == User::POLICY_ONLY_USERS_YOU_MENTION) {
            $postMention = Mention::where('user_id', $post->user_id)->where('mention_type', Mention::TYPE_POST)->where('mention_id', $post->id)->where('mention_user_id', $userId)->first();

            if ($postMention) {
                return [
                    'status' => true,
                    'code' => 0,
                ];
            }

            $commentPerm['code'] = 38212;

            return $commentPerm;
        }

        $user = PrimaryHelper::fresnsModelById('user', $post->user_id);

        $userCommentPolicy = $user?->comment_policy ?? User::POLICY_EVERYONE;

        $commentPolicy = ($userCommentPolicy == User::POLICY_EVERYONE) ? $postCommentPolicy : $userCommentPolicy;

        if ($commentPolicy != User::POLICY_EVERYONE) {
            if ($commentPolicy == User::POLICY_NO_ONE_IS_ALLOWED) {
                $commentPerm['code'] = 38211;

                return $commentPerm;
            }

            $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_USER, $post->user_id, $userId);
            if (! $interactionStatus['followStatus']) {
                $commentPerm['code'] = 38209;

                return $commentPerm;
            }

            $checkUserVerified = PrimaryHelper::fresnsModelById('user', $userId)?->verified_status;
            if ($commentPolicy == User::POLICY_PEOPLE_YOU_FOLLOW_OR_VERIFIED && ! $checkUserVerified) {
                $commentPerm['code'] = 38210;

                return $commentPerm;
            }
        }

        $commentPerm['status'] = true;
        $commentPerm['code'] = 0;

        return $commentPerm;
    }

    // Check content is can delete
    // $type = post or comment
    public static function checkContentIsCanDelete(string $type, int $digestState, int $stickyState): bool
    {
        $deleteConfig = ConfigHelper::fresnsConfigByItemKeys([
            "{$type}_delete",
            "{$type}_delete_sticky_limit",
            "{$type}_delete_digest_limit",
        ]);

        if (! $deleteConfig["{$type}_delete"]) {
            return false;
        }

        if ($digestState != Post::STICKY_NO && ! $deleteConfig["{$type}_delete_digest_limit"]) {
            return false;
        }

        if ($type == 'post' && $stickyState != Post::STICKY_NO && ! $deleteConfig["{$type}_delete_sticky_limit"]) {
            return false;
        }

        if ($type == 'comment' && $stickyState == Comment::STICKY_YES && ! $deleteConfig["{$type}_delete_sticky_limit"]) {
            return false;
        }

        return true;
    }

    // Check content is can edit
    // $type = post or comment
    public static function checkContentIsCanEdit(string $type, Carbon $createdDatetime, int $digestState, int $stickyState, ?string $timezone = null, ?string $langTag = null): bool
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

        $checkContentEditPerm = static::checkContentEditPerm($createdDatetime, $editConfig["{$type}_edit_time_limit"], $timezone, $langTag);

        if (! $checkContentEditPerm['editableStatus']) {
            return false;
        }

        if ($digestState != Post::STICKY_NO && ! $editConfig["{$type}_edit_digest_limit"]) {
            return false;
        }

        if ($type == 'post' && $stickyState != Post::STICKY_NO && ! $editConfig["{$type}_edit_sticky_limit"]) {
            return false;
        }

        if ($type == 'comment' && $stickyState == Comment::STICKY_YES && ! $editConfig["{$type}_edit_sticky_limit"]) {
            return false;
        }

        return true;
    }

    // Check content edit perm
    public static function checkContentEditPerm(Carbon $createdDatetime, int $editTimeConfig, ?string $timezone = null, ?string $langTag = null): array
    {
        $editableDateTime = $createdDatetime->addMinutes($editTimeConfig);
        $editableSeconds = $editableDateTime->timestamp - time();
        $editableTimeMinutes = intval($editableSeconds / 60);
        $editableTimeSeconds = $editableSeconds % 60;

        $editableStatus = true;
        if ($editableTimeMinutes <= 0) {
            $editableStatus = false;
            $editableTimeMinutes = '00';
            $editableTimeSeconds = '00';
        }

        $editableTime = "{$editableTimeMinutes}:{$editableTimeSeconds}";
        if ($editableTimeMinutes > 60) {
            $editableHours = floor($editableSeconds / 3600);
            $editableMinutes = floor(($editableSeconds % 3600) / 60);
            $editableSeconds = $editableSeconds % 60;

            $editableTime = "{$editableHours}:{$editableMinutes}:{$editableSeconds}";
        }

        $perm['editableStatus'] = $editableStatus;
        $perm['editableTime'] = $editableTime;
        $perm['deadlineTime'] = DateHelper::fresnsDateTimeByTimezone($editableDateTime->format('Y-m-d H:i:s'), $timezone, $langTag);

        return $perm;
    }

    // Check content edit
    public static function checkContentEdit(string $type, string $createdDatetime, int $stickyState, int $digestState): int
    {
        $editConfig = ConfigHelper::fresnsConfigByItemKeys([
            "{$type}_edit",
            "{$type}_edit_time_limit",
            "{$type}_edit_sticky_limit",
            "{$type}_edit_digest_limit",
        ]);

        $config = [
            'edit' => $editConfig["{$type}_edit"],
            'timeLimit' => $editConfig["{$type}_edit_time_limit"],
            'stickyLimit' => $editConfig["{$type}_edit_sticky_limit"],
            'digestLimit' => $editConfig["{$type}_edit_digest_limit"],
        ];

        // check edit
        if (! $config['edit']) {
            return ($type == 'post') ? 36305 : 36306;
        }

        // check time
        $timeDiff = Carbon::parse($createdDatetime)->diffInMinutes(now());
        if ($timeDiff > $config['timeLimit']) {
            return 36309;
        }

        // check sticky
        $noSticky = match ($type) {
            'post' => Post::STICKY_NO,
            'comment' => false,
        };
        if (! $config['stickyLimit'] && $stickyState != $noSticky) {
            return 36307;
        }

        // check digest
        $noDigest = match ($type) {
            'post' => Post::DIGEST_NO,
            'comment' => Comment::DIGEST_NO,
        };
        if (! $config['digestLimit'] && $digestState != $noDigest) {
            return 36308;
        }

        return 0;
    }

    // Check content interval time
    public static function checkContentIntervalTime(int $userId, string $type): bool
    {
        $rolePerm = PermissionUtility::getUserMainRole($userId)['permissions'];
        $interval = $rolePerm["{$type}_second_interval"] ?? 0;

        if ($interval == 0) {
            return true;
        }

        $model = match ($type) {
            'post' => Post::where('user_id', $userId)->latest()->first(),
            'comment' => Comment::where('user_id', $userId)->latest()->first(),
        };

        if (! $model) {
            return true;
        }

        if ($model->created_at->addSeconds($interval) < now()) {
            return true;
        }

        return false;
    }

    // Check content publish count rules
    public static function checkContentPublishCountRules(int $userId, string $type): bool
    {
        $rolePerm = PermissionUtility::getUserMainRole($userId)['permissions'];
        $dailyCount = $rolePerm["{$type}_daily_count"] ?? 0;

        if ($dailyCount == 0) {
            return true;
        }

        $dayDate = Carbon::today()->format('Y-m-d');

        $modelCount = match ($type) {
            'post' => Post::where('user_id', $userId)->whereDate('created_at', $dayDate)->count(),
            'comment' => Comment::where('user_id', $userId)->whereDate('created_at', $dayDate)->count(),
        };

        if ($modelCount < $dailyCount) {
            return true;
        }

        return false;
    }

    // Check extend perm
    public static function checkExtendPerm(string $fskey, int $usageType, ?int $groupId = null, ?int $userId = null): bool
    {
        if (empty($userId)) {
            return false;
        }

        // get usage list
        if ($usageType == AppUsage::TYPE_GROUP && empty($groupId)) {
            return false;
        }

        // check group admin
        $checkGroupAdmin = self::checkExtendPermByGroupAdmin($fskey, $usageType, $groupId, $userId);

        if ($checkGroupAdmin) {
            return true;
        }

        // check role
        $checkRole = self::checkExtendPermByRole($fskey, $usageType, $groupId, $userId);

        return $checkRole;
    }

    private static function checkExtendPermByGroupAdmin(string $fskey, int $usageType, ?int $groupId = null, ?int $userId = null): bool
    {
        if (empty($groupId) || $groupId == 0) {
            return false;
        }

        // get usage list
        if ($usageType == AppUsage::TYPE_GROUP) {
            $usages = AppUsage::where('usage_type', $usageType)
                ->where('app_fskey', $fskey)
                ->where('group_id', $groupId)
                ->where('is_group_admin', true)
                ->where('is_enabled', true)
                ->get();
        } else {
            $usages = AppUsage::where('usage_type', $usageType)
                ->where('app_fskey', $fskey)
                ->where('is_group_admin', true)
                ->where('is_enabled', true)
                ->get();
        }

        if ($usages->isEmpty()) {
            return false;
        }

        $checkGroupAdmin = PermissionUtility::checkUserGroupAdmin($groupId, $userId);

        if (! $checkGroupAdmin) {
            return false;
        }

        return true;
    }

    private static function checkExtendPermByRole(string $fskey, int $usageType, ?int $groupId = null, ?int $userId = null): bool
    {
        // get usage list
        if ($usageType == AppUsage::TYPE_GROUP) {
            $usages = AppUsage::where('usage_type', $usageType)
                ->where('app_fskey', $fskey)
                ->where('group_id', $groupId)
                ->where('is_group_admin', false)
                ->where('is_enabled', true)
                ->get();
        } else {
            $usages = AppUsage::where('usage_type', $usageType)
                ->where('app_fskey', $fskey)
                ->where('is_group_admin', false)
                ->where('is_enabled', true)
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
            $userRoleIdArr = array_column($userRoleArr, 'id');

            $intersect = array_intersect($roleArr, $userRoleIdArr);

            return empty($intersect) ? false : true;
        }

        return true;
    }
}
