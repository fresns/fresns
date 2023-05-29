<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
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
use App\Utilities\SubscribeUtility;

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
                ->isEnabled()
                ->orderBy('rating')
                ->get();
            $contentMetaPost = [];
            foreach ($postArchiveData as $archive) {
                $contentMetaPost[] = $archive->getArchiveInfo($langTag);
            }

            $commentArchiveData = Archive::type(Archive::TYPE_GROUP)
                ->where('usage_group_id', $group->id)
                ->where('usage_group_content_type', 2)
                ->isEnabled()
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

        $item['canViewContent'] = $groupInfo['mode'] == 1;
        $item['publishRule'] = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions, $authUserId);

        $interactionConfig = InteractionHelper::fresnsGroupInteraction($langTag, $group->type_follow);
        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_GROUP, $group->id, $authUserId);

        $item['interaction'] = array_merge($interactionConfig, $interactionStatus);

        SubscribeUtility::notifyViewContent('group', $group->gid, null, $authUserId);

        if ($groupInfo['mode'] == 2 && $authUserId) {
            $userRole = PermissionUtility::getUserMainRole($authUserId);
            $whitelistRoles = $group->permissions['mode_whitelist_roles'] ?? [];

            if ($whitelistRoles && in_array($userRole['rid'], $whitelistRoles)) {
                $item['canViewContent'] = true;
            } else {
                $item['canViewContent'] = $interactionStatus['followStatus'];
            }
        }

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

        $groupData['viewCount'] = $group->view_count;
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

        if ($group->type_mode == Group::MODE_PUBLIC) {
            return $checkResp;
        }

        if (empty($authUserId)) {
            $checkResp['code'] = 37103;

            return $checkResp;
        }

        $userRole = PermissionUtility::getUserMainRole($authUserId);
        $whitelistRoles = $group->permissions['mode_whitelist_roles'] ?? [];

        if ($whitelistRoles && in_array($userRole['rid'], $whitelistRoles)) {
            return $checkResp;
        }

        $follow = PrimaryHelper::fresnsFollowModelByType('group', $groupId, $authUserId);
        if (empty($follow)) {
            $checkResp['code'] = 37103;

            return $checkResp;
        }

        if ($group->type_mode_end_after == Group::PRIVATE_OPTION_UNRESTRICTED) {
            return $checkResp;
        }

        if ($group->type_mode_end_after == Group::PRIVATE_OPTION_HIDE_ALL) {
            if (empty($follow?->expired_at)) {
                $checkResp['code'] = 37105;

                return $checkResp;
            }

            $now = time();
            $expiryTime = strtotime($follow->expired_at);
            if ($expiryTime < $now) {
                $checkResp['code'] = 37105;

                return $checkResp;
            }
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

        if ($group->type_mode == Group::MODE_PUBLIC) {
            return;
        }

        if (empty($authUserId)) {
            throw new ApiException(37103);
        }

        $userRole = PermissionUtility::getUserMainRole($authUserId);
        $whitelistRoles = $group->permissions['mode_whitelist_roles'] ?? [];

        if ($whitelistRoles && in_array($userRole['rid'], $whitelistRoles)) {
            return;
        }

        $follow = PrimaryHelper::fresnsFollowModelByType('group', $groupId, $authUserId);

        if (empty($follow)) {
            throw new ApiException(37103);
        }

        if ($group->type_mode_end_after == Group::PRIVATE_OPTION_UNRESTRICTED) {
            return;
        }

        if ($group->type_mode_end_after == Group::PRIVATE_OPTION_HIDE_ALL) {
            throw new ApiException(37105);
        }

        $contentCreatedDatetime = strtotime($dateTime);
        $dateLimit = strtotime($follow->expired_at);

        if ($contentCreatedDatetime > $dateLimit) {
            throw new ApiException(37106);
        }
    }
}
