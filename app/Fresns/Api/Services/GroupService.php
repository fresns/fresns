<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Exceptions\ApiException;
use App\Helpers\CacheHelper;
use App\Helpers\DateHelper;
use App\Helpers\InteractiveHelper;
use App\Helpers\PrimaryHelper;
use App\Models\ArchiveUsage;
use App\Models\ExtendUsage;
use App\Models\Group;
use App\Models\OperationUsage;
use App\Models\UserFollow;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Facades\Cache;

class GroupService
{
    public function groupData(?Group $group, string $langTag, string $timezone, ?int $authUserId = null)
    {
        if (! $group) {
            return null;
        }

        $groupInfo = $group->getGroupInfo($langTag);

        $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_GROUP, $group->id, $langTag);
        $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_GROUP, $group->id, $langTag);
        $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_GROUP, $group->id, $langTag);

        $userService = new UserService;

        $item['creator'] = null;
        if (! empty($group?->creator)) {
            $item['creator'] = $userService->userData($group->creator, $langTag, $timezone);
        }

        $item['publishRule'] = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions, $authUserId);

        $adminList = [];
        foreach ($group->admins as $admin) {
            $adminList[] = $userService->userData($admin, $langTag, $timezone);
        }
        $item['admins'] = $adminList;

        $interactiveConfig = InteractiveHelper::fresnsGroupInteractive($langTag);
        $interactiveStatus = InteractiveUtility::getInteractiveStatus(InteractiveUtility::TYPE_GROUP, $group->id, $authUserId);
        $interactiveStatus['followIsExpiry'] = false;
        $interactiveStatus['followExpiryDateTime'] = null;

        $cacheKey = "fresns_user_follow_group_model_{$authUserId}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();
        $follow = Cache::remember($cacheKey, $cacheTime, function () use ($authUserId, $group) {
            return UserFollow::where('user_id', $authUserId)->where('follow_type', UserFollow::TYPE_GROUP)->where('follow_id', $group->id)->first();
        });

        if ($group->type_mode == 2 && $group->type_mode_end_after != 1 && $follow) {
            $now = time();
            $expireTime = strtotime($follow?->expired_at);

            $interactiveStatus['followIsExpiry'] = ($expireTime < $now) ? true : false;
            $interactiveStatus['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($follow?->expired_at, $timezone, $langTag);
        }

        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus);

        $data = array_merge($groupInfo, $item);

        return $data;
    }

    // get group content date limit
    public static function getGroupContentDateLimit(int $groupId, ?int $authUserId = null)
    {
        $group = PrimaryHelper::fresnsModelById('group', $groupId);

        if ($group->type_mode == 1) {
            return null;
        }

        if (empty($authUserId)) {
            throw new ApiException(31601);
        }

        $cacheKey = "fresns_user_follow_group_model_{$authUserId}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $follow = Cache::remember($cacheKey, $cacheTime, function () use ($authUserId, $group) {
            return UserFollow::where('user_id', $authUserId)->where('follow_type', UserFollow::TYPE_GROUP)->where('follow_id', $group->id)->first();
        });

        if (empty($follow)) {
            throw new ApiException(37103);
        }

        if ($group->type_mode_end_after == 1) {
            return null;
        }

        if (empty($follow?->expired_at) || $group->type_mode_end_after == 2) {
            throw new ApiException(37105);
        }

        return $follow->expired_at;
    }

    // check content view permission
    public static function checkGroupContentViewPerm(string $dateTime, ?int $groupId = null, ?int $authUserId = null)
    {
        if (empty($groupId)) {
            return;
        }

        $group = PrimaryHelper::fresnsModelById('group', $groupId);

        if ($group->type_mode == 1) {
            return;
        }

        if (empty($authUserId)) {
            throw new ApiException(31601);
        }

        $cacheKey = "fresns_user_follow_group_model_{$authUserId}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $follow = Cache::remember($cacheKey, $cacheTime, function () use ($authUserId, $group) {
            return UserFollow::where('user_id', $authUserId)->where('follow_type', UserFollow::TYPE_GROUP)->where('follow_id', $group->id)->first();
        });

        if (empty($follow)) {
            throw new ApiException(37103);
        }

        if ($group->type_mode_end_after == 1) {
            return;
        }

        if ($group->type_mode_end_after == 2) {
            throw new ApiException(37105);
        }

        $contentCreateTime = strtotime($dateTime);
        $dateLimit = strtotime($follow->expired_at);

        if ($contentCreateTime > $dateLimit) {
            throw new ApiException(37106);
        }
    }
}
