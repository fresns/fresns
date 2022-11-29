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
use App\Helpers\InteractionHelper;
use App\Helpers\PrimaryHelper;
use App\Models\ArchiveUsage;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\Group;
use App\Models\OperationUsage;
use App\Models\UserFollow;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Facades\Cache;

class GroupService
{
    public function groupData(?Group $group, string $langTag, string $timezone, ?int $authUserId = null)
    {
        if (! $group) {
            return null;
        }

        $cacheKey = "fresns_api_group_{$group->gid}_{$langTag}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);

        $groupInfo = Cache::remember($cacheKey, $cacheTime, function () use ($group, $langTag) {
            $groupInfo = $group->getGroupInfo($langTag);

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_GROUP, $group->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_GROUP, $group->id, $langTag);
            $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_GROUP, $group->id, $langTag);

            $userService = new UserService;

            $item['creator'] = null;
            if ($group?->creator) {
                $item['creator'] = $userService->userData($group->creator, $langTag);
            }

            $adminList = [];
            foreach ($group->admins as $admin) {
                $adminList[] = $userService->userData($admin, $langTag);
            }
            $item['admins'] = $adminList;

            return array_merge($groupInfo, $item);
        });

        $item['publishRule'] = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions, $authUserId);

        $interactionConfig = InteractionHelper::fresnsGroupInteraction($langTag);
        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_GROUP, $group->id, $authUserId);

        $item['interaction'] = array_merge($interactionConfig, $interactionStatus);

        $data = array_merge($groupInfo, $item);

        return GroupService::handleGroupDate($data, $timezone, $langTag);
    }

    // handle group data date
    public static function handleGroupDate(?array $groupData, string $timezone, string $langTag)
    {
        if (empty($groupData)) {
            return $groupData;
        }

        $groupData['createDate'] = DateHelper::fresnsDateTimeByTimezone($groupData['createDate'], $timezone, $langTag);

        $groupData['creator'] = UserService::handleUserDate($groupData['creator'], $timezone, $langTag);

        $adminList = [];
        foreach ($groupData['admins'] as $admin) {
            $adminList[] = UserService::handleUserDate($admin, $timezone, $langTag);
        }
        $groupData['admins'] = $adminList;

        $groupData['interaction']['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($groupData['interaction']['followExpiryDateTime'], $timezone, $langTag);

        return $groupData;
    }

    // get group content date limit
    public static function getGroupContentDateLimit(int $groupId, ?int $authUserId = null)
    {
        $group = PrimaryHelper::fresnsModelById('group', $groupId);

        if ($group->type_mode == 1) {
            return null;
        }

        if (empty($authUserId)) {
            throw new ApiException(37103);
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
            throw new ApiException(37103);
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
