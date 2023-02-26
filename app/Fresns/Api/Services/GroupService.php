<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Exceptions\ApiException;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\InteractionHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Archive;
use App\Models\ArchiveUsage;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\Group;
use App\Models\OperationUsage;
use App\Utilities\ArrUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\PermissionUtility;

class GroupService
{
    public function groupData(?Group $group, string $langTag, string $timezone, ?int $authUserId = null)
    {
        if (! $group) {
            return null;
        }

        $cacheKey = "fresns_api_group_{$group->gid}_{$langTag}";
        $cacheTag = 'fresnsGroups';

        // get cache
        $groupInfo = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($groupInfo)) {
            $groupInfo = $group->getGroupInfo($langTag);

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_GROUP, $group->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_GROUP, $group->id, $langTag);
            $item['extends'] = ExtendUtility::getContentExtends(ExtendUsage::TYPE_GROUP, $group->id, $langTag);

            $postArchiveData = Archive::type(Archive::TYPE_GROUP)
                ->where('usage_group_id', $group->id)
                ->where('usage_group_content_type', 1)
                ->isEnable()
                ->orderBy('rating')
                ->get();
            $contentMetaPost = [];
            foreach ($postArchiveData as $archive) {
                $contentMetaPost[] = $archive->getArchiveInfo($langTag);
            }

            $commentArchiveData = Archive::type(Archive::TYPE_GROUP)
                ->where('usage_group_id', $group->id)
                ->where('usage_group_content_type', 2)
                ->isEnable()
                ->orderBy('rating')
                ->get();
            $contentMetaComment = [];
            foreach ($commentArchiveData as $archive) {
                $contentMetaComment[] = $archive->getArchiveInfo($langTag);
            }

            $item['contentMeta'] = [
                'post' => $contentMetaPost,
                'comment' => $contentMetaComment,
            ];

            $userService = new UserService;

            $item['creator'] = null;
            if ($group?->creator) {
                $item['creator'] = $userService->userData($group->creator, 'list', $langTag);
            }

            $adminList = [];
            foreach ($group->admins as $admin) {
                $adminList[] = $userService->userData($admin, 'list', $langTag);
            }
            $item['admins'] = $adminList;

            $groupInfo = array_merge($groupInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($groupInfo, $cacheKey, $cacheTag, null, $cacheTime);
        }

        $item['publishRule'] = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions, $authUserId);

        $interactionConfig = InteractionHelper::fresnsGroupInteraction($langTag);
        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_GROUP, $group->id, $authUserId);

        $item['interaction'] = array_merge($interactionConfig, $interactionStatus);

        $data = array_merge($groupInfo, $item);

        $groupData = self::handleGroupCount($group, $data);
        $result = self::handleGroupDate($groupData, $timezone, $langTag);

        // filter
        $filterKeys = \request()->get('whitelistKeys') ?? \request()->get('blacklistKeys');
        $filter = [
            'type' => \request()->get('whitelistKeys') ? 'whitelist' : 'blacklist',
            'keys' => array_filter(explode(',', $filterKeys)),
        ];

        if (empty($filter['keys'])) {
            return $result;
        }

        $currentRouteName = \request()->route()->getName();
        $filterRouteList = [
            'api.group.tree',
            'api.group.list',
            'api.group.detail',
        ];

        if (! in_array($currentRouteName, $filterRouteList)) {
            return $result;
        }

        if ($currentRouteName == 'api.group.tree') {
            if ($filter['type'] == 'whitelist') {
                $filter['keys'] = array_merge($filter['keys'], ['gid', 'parentGid']);
            } else {
                $filter['keys'] = array_diff($filter['keys'], ['gid', 'parentGid']);
            }
        }

        return ArrUtility::filter($result, $filter['type'], $filter['keys']);
    }

    // handle group data count
    public static function handleGroupCount(?Group $group, ?array $groupData)
    {
        if (empty($group) || empty($groupData)) {
            return $groupData;
        }

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'group_liker_count',
            'group_disliker_count',
            'group_follower_count',
            'group_blocker_count',
        ]);

        $groupData['likeCount'] = $configKeys['group_liker_count'] ? $group->like_count : null;
        $groupData['dislikeCount'] = $configKeys['group_disliker_count'] ? $group->dislike_count : null;
        $groupData['followCount'] = $configKeys['group_follower_count'] ? $group->follow_count : null;
        $groupData['blockCount'] = $configKeys['group_blocker_count'] ? $group->block_count : null;
        $groupData['postCount'] = $group->post_count;
        $groupData['postDigestCount'] = $group->post_digest_count;
        $groupData['commentCount'] = $group->comment_count;
        $groupData['commentDigestCount'] = $group->comment_digest_count;

        return $groupData;
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

        $checkResp = [
            'code' => 0,
            'datetime' => null,
        ];

        if ($group->type_mode == 1) {
            return $checkResp;
        }

        if (empty($authUserId)) {
            $checkResp['code'] = 37103;

            return $checkResp;
        }

        $follow = PrimaryHelper::fresnsFollowModelByType('group', $groupId, $authUserId);

        if (empty($follow)) {
            $checkResp['code'] = 37103;

            return $checkResp;
        }

        if ($group->type_mode_end_after == 1) {
            return $checkResp;
        }

        if (empty($follow?->expired_at) || $group->type_mode_end_after == 2) {
            $checkResp['code'] = 37105;

            return $checkResp;
        }

        $checkResp['datetime'] = $follow->expired_at;

        return $checkResp;
    }

    // check content view permission
    public static function checkGroupContentViewPerm(string $dateTime, ?int $groupId = null, ?int $authUserId = null)
    {
        if (empty($groupId) || $groupId == 0) {
            return;
        }

        $group = PrimaryHelper::fresnsModelById('group', $groupId);

        if ($group->type_mode == 1) {
            return;
        }

        if (empty($authUserId)) {
            throw new ApiException(37103);
        }

        $follow = PrimaryHelper::fresnsFollowModelByType('group', $groupId, $authUserId);

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
