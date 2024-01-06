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
use App\Models\Account;
use App\Models\ArchiveUsage;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Mention;
use App\Models\OperationUsage;
use App\Models\User;

class DetailUtility
{
    // accountDetail
    public static function accountDetail(Account|string $accountOrAid = null, ?string $langTag = null, ?string $timezone = null, ?array $options = []): ?array
    {
        // $options = [
        //     'viewType' => '', // list, detail, quoted
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];

        if (! $accountOrAid) {
            return null;
        }

        $account = $accountOrAid;
        if (is_string($accountOrAid)) {
            $account = PrimaryHelper::fresnsModelByFsid('account', $accountOrAid);
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_detail_account_{$account->id}_{$langTag}";
        $cacheTag = 'fresnsAccounts';

        $accountDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($accountDetail)) {
            $accountData = $account->getAccountInfo();

            $item['connects'] = $account->getAccountConnects();
            $item['wallet'] = $account->getAccountWallet($langTag);

            $userList = [];
            foreach ($account->users as $user) {
                $userList[] = $user->uid;
            }

            $item['users'] = $userList;

            $accountDetail = array_merge($accountData, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($accountDetail, $cacheKey, $cacheTag, null, $cacheTime);
        }

        $users = [];
        foreach ($accountDetail['users'] as $user) {
            $users[] = self::userDetail($user, $langTag, $timezone, null, $options);
        }
        $accountDetail['users'] = $users;

        // handle date
        $accountDetail['verifyDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountDetail['verifyDateTime'], $timezone, $langTag);
        $accountDetail['registerDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountDetail['registerDateTime'], $timezone, $langTag);
        $accountDetail['waitDeleteDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountDetail['waitDeleteDateTime'], $timezone, $langTag);

        return $accountDetail;
    }

    // userDetail
    public static function userDetail(User|int $userOrUid = null, ?string $langTag = null, ?string $timezone = null, ?int $authUserId = null, ?array $options = []): ?array
    {
        // $options = [
        //     'viewType' => '', // list, detail, quoted
        //     'isLiveStats' => false,
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];

        if (empty($userOrUid)) {
            return InteractionHelper::fresnsUserSubstitutionProfile('deactivate');
        }

        $user = $userOrUid;
        if (is_numeric($userOrUid)) {
            $user = PrimaryHelper::fresnsModelByFsid('user', $userOrUid);
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_detail_user_{$user->id}_{$langTag}";
        $cacheTag = 'fresnsUsers';

        $userDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($userDetail)) {
            $userProfile = $user->getUserProfile();
            $userMainRole = $user->getUserMainRole($langTag);
            $userRoles = $user->getUserRoles($langTag);

            // birthday
            $userProfile['birthday'] = DateHelper::fresnsFormatConversion($userProfile['birthday'], $langTag);

            // bio
            $bioConfig = ConfigHelper::fresnsConfigByItemKeys([
                'bio_support_mention',
                'bio_support_link',
                'bio_support_hashtag',
            ]);
            $bioHtml = htmlentities($userProfile['bio']);
            if ($bioConfig['bio_support_mention']) {
                $bioHtml = ContentUtility::replaceMention($bioHtml, Mention::TYPE_USER, $user->id);
            }
            if ($bioConfig['bio_support_link']) {
                $bioHtml = ContentUtility::replaceLink($bioHtml);
            }
            if ($bioConfig['bio_support_hashtag']) {
                $bioHtml = ContentUtility::replaceHashtag($bioHtml);
            }
            $userProfile['bioHtml'] = ContentUtility::replaceSticker($bioHtml);

            // extend
            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_USER, $user->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $user->id, $langTag);
            $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_USER, $user->id, $langTag);

            // roles
            $item['roles'] = $userRoles;

            // interaction
            $item['interaction'] = InteractionHelper::fresnsInteraction('user', $langTag);

            // conversation
            $item['conversation'] = [
                'status' => false,
                'code' => 31601,
                'message' => ConfigUtility::getCodeMessage(31601, 'Fresns', $langTag),
            ];

            // diversify images
            if ($item['operations']['diversifyImages']) {
                $decorate = ArrUtility::pull($item['operations']['diversifyImages'], 'code', 'decorate', false);
                $verifiedIcon = ArrUtility::pull($item['operations']['diversifyImages'], 'code', 'verified', false);

                $userProfile['decorate'] = $decorate['image'] ?? null;
                $userProfile['verifiedIcon'] = $verifiedIcon['image'] ?? null;
            }

            $userDetail = array_merge($userProfile, $userMainRole, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($userDetail, $cacheKey, $cacheTag, null, $cacheTime);
        }

        // archives
        if ($user->id != $authUserId && $userDetail['archives']) {
            $archives = [];
            foreach ($userDetail['archives'] as $archive) {
                $item = $archive;
                $item['value'] = $archive['isPrivate'] ? null : $archive['value'];

                $archives[] = $item;
            }

            $userDetail['archives'] = $archives;
        }

        // user stats
        if ($options['isLiveStats'] ?? null) {
            $userStats = $user->getUserStats($langTag, $authUserId);
        } else {
            $cacheStatsKey = "fresns_detail_user_stats_{$user->id}";
            $userStats = CacheHelper::get($cacheStatsKey, $cacheTag);
            if (empty($userStats)) {
                $userStats = $user->getUserStats($langTag);

                CacheHelper::put($userStats, $cacheStatsKey, $cacheTag, 15, now()->addMinutes(15));
            }
        }

        $userDetail['stats'] = $userStats;

        // get interaction status
        if ($authUserId) {
            $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_USER, $user->id, $authUserId);
            $userDetail['interaction'] = array_replace($userDetail['interaction'], $interactionStatus);

            $conversationPermInt = PermissionUtility::checkUserConversationPerm($user->id, $authUserId, $langTag);
            $userDetail['conversation'] = [
                'status' => ($conversationPermInt != 0) ? false : true,
                'code' => $conversationPermInt,
                'message' => ConfigUtility::getCodeMessage($conversationPermInt, 'Fresns', $langTag),
            ];
        }

        $result = self::handleUserDate($userDetail, $timezone, $langTag);

        // subscribe
        $viewType = $options['viewType'] ?? null;
        if ($viewType && $viewType != 'quoted') {
            SubscribeUtility::notifyViewContent('user', $user->uid, $viewType, $authUserId);
        }

        // filter
        $filterType = $options['filter']['type'] ?? null;
        $filterKeys = $options['filter']['keys'] ?? null;
        $filterKeysArr = $filterKeys ? array_filter(explode(',', $filterKeys)) : [];

        if ($filterType && $filterKeysArr) {
            return ArrUtility::filter($result, $filterType, $filterKeysArr);
        }

        return $result;
    }

    // groupDetail
    public static function groupDetail(Group|string $groupOrGid = null, ?string $langTag = null, ?string $timezone = null, ?int $authUserId = null, ?array $options = [])
    {
        // $options = [
        //     'viewType' => '', // list, detail, quoted
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterCreator' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterAdmin' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];

        if (! $groupOrGid) {
            return null;
        }

        $group = $groupOrGid;
        if (is_string($groupOrGid)) {
            $group = PrimaryHelper::fresnsModelByFsid('group', $groupOrGid);
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_detail_group_{$group->id}_{$langTag}";
        $cacheTag = 'fresnsGroups';

        // get cache
        $groupDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($groupDetail)) {
            $groupInfo = $group->getGroupInfo($langTag);

            $item['canViewContent'] = (bool) $group->privacy == 1;
            $item['publishRule'] = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions);

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_GROUP, $group->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_GROUP, $group->id, $langTag);
            $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_GROUP, $group->id, $langTag);

            // creator
            $item['creator'] = $group?->creator?->uid;

            // admins
            $adminList = [];
            foreach ($group->admins as $admin) {
                $adminList[] = $admin->uid;
            }
            $item['admins'] = $adminList;

            // interaction
            $item['interaction'] = InteractionHelper::fresnsInteraction('group', $langTag);

            $groupDetail = array_merge($groupInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($groupDetail, $cacheKey, $cacheTag, null, $cacheTime);
        }

        // creator
        if ($groupDetail['creator']) {
            $creatorOptions = [
                'viewType' => 'quoted',
                'isLiveStats' => false,
                'filter' => [
                    'type' => $options['filterCreator']['type'] ?? null,
                    'keys' => $options['filterCreator']['keys'] ?? null,
                ],
            ];

            $groupDetail['creator'] = self::userDetail($groupDetail['creator'], $langTag, $timezone, $authUserId, $creatorOptions);
        }

        // admins
        if ($groupDetail['admins']) {
            $adminOptions = [
                'viewType' => 'quoted',
                'isLiveStats' => false,
                'filter' => [
                    'type' => $options['filterAdmin']['type'] ?? null,
                    'keys' => $options['filterAdmin']['keys'] ?? null,
                ],
            ];

            $admins = [];
            foreach ($groupDetail['admins'] as $admin) {
                $admins[] = self::userDetail($admin, $langTag, $timezone, $authUserId, $adminOptions);
            }
            $groupDetail['admins'] = $admins;
        }

        if ($authUserId) {
            $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_GROUP, $group->id, $authUserId);

            if ($group->privacy == Group::PRIVACY_PRIVATE) {
                $userRole = PermissionUtility::getUserMainRole($authUserId);
                $whitelistRoles = $group->permissions['private_whitelist_roles'] ?? [];

                $groupDetail['canViewContent'] = $interactionStatus['followStatus'] ?: in_array($userRole['id'], $whitelistRoles);
            }

            $groupDetail['publishRule'] = PermissionUtility::checkUserGroupPublishPerm($group->id, $group->permissions, $authUserId);

            $groupDetail['interaction'] = array_replace($groupDetail['interaction'], $interactionStatus);
        }

        // handle date
        $groupDetail['createdDatetime'] = DateHelper::fresnsDateTimeByTimezone($groupDetail['createdDatetime'], $timezone, $langTag);
        $groupDetail['interaction']['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($groupDetail['interaction']['followExpiryDateTime'], $timezone, $langTag);

        $result = self::handleGroupCount($groupDetail, $group);

        // subscribe
        $viewType = $options['viewType'] ?? null;
        if ($viewType && $viewType != 'quoted') {
            SubscribeUtility::notifyViewContent('group', $group->gid, $viewType, $authUserId);
        }

        // filter
        $filterType = $options['filter']['type'] ?? null;
        $filterKeys = $options['filter']['keys'] ?? null;
        $filterKeysArr = $filterKeys ? array_filter(explode(',', $filterKeys)) : [];

        if ($filterType && $filterKeysArr) {
            return ArrUtility::filter($result, $filterType, $filterKeysArr);
        }

        return $result;
    }

    // hashtagDetail
    public static function hashtagDetail(Hashtag|string $hashtagOrHtid = null, ?string $langTag = null, ?string $timezone = null, ?int $authUserId = null, ?array $options = [])
    {
        // $options = [
        //     'viewType' => '', // list, detail, quoted
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];

        if (! $hashtagOrHtid) {
            return null;
        }

        $hashtag = $hashtagOrHtid;
        if (is_string($hashtagOrHtid)) {
            $hashtag = PrimaryHelper::fresnsModelByFsid('hashtag', $hashtagOrHtid);
        }

        $cacheKey = "fresns_detail_hashtag_{$hashtag->id}_{$langTag}";
        $cacheTag = 'fresnsHashtags';

        $hashtagDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($hashtagDetail)) {
            $hashtagInfo = $hashtag->getHashtagInfo($langTag);

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_HASHTAG, $hashtag->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_HASHTAG, $hashtag->id, $langTag);
            $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_HASHTAG, $hashtag->id, $langTag);

            // interaction
            $item['interaction'] = InteractionHelper::fresnsInteraction('hashtag', $langTag);

            $hashtagDetail = array_merge($hashtagInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($hashtagDetail, $cacheKey, $cacheTag, null, $cacheTime);
        }

        if ($authUserId) {
            $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_HASHTAG, $hashtag->id, $authUserId);

            $hashtagDetail['interaction'] = array_replace($hashtagDetail['interaction'], $interactionStatus);
        }

        // handle date
        $hashtagDetail['createdDatetime'] = DateHelper::fresnsDateTimeByTimezone($hashtagDetail['createdDatetime'], $timezone, $langTag);

        $result = self::handleHashtagCount($hashtagDetail, $hashtag);

        // subscribe
        $viewType = $options['viewType'] ?? null;
        if ($viewType && $viewType != 'quoted') {
            SubscribeUtility::notifyViewContent('hashtag', $hashtag->slug, $viewType, $authUserId);
        }

        // filter
        $filterType = $options['filter']['type'] ?? null;
        $filterKeys = $options['filter']['keys'] ?? null;
        $filterKeysArr = $filterKeys ? array_filter(explode(',', $filterKeys)) : [];

        if ($filterType && $filterKeysArr) {
            return ArrUtility::filter($result, $filterType, $filterKeysArr);
        }

        return $result;
    }

    /**
     * handle detail date and count.
     */

    // handle user data date
    private static function handleUserDate(array $userDetail, ?string $timezone = null, ?string $langTag = null): array
    {
        $userDetail['verifiedDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['verifiedDateTime'], $timezone, $langTag);

        $userDetail['expiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['expiryDateTime'], $timezone, $langTag);

        $userDetail['lastPublishPost'] = DateHelper::fresnsDateTimeByTimezone($userDetail['lastPublishPost'], $timezone, $langTag);
        $userDetail['lastPublishComment'] = DateHelper::fresnsDateTimeByTimezone($userDetail['lastPublishComment'], $timezone, $langTag);
        $userDetail['lastEditUsername'] = DateHelper::fresnsDateTimeByTimezone($userDetail['lastEditUsername'], $timezone, $langTag);
        $userDetail['lastEditNickname'] = DateHelper::fresnsDateTimeByTimezone($userDetail['lastEditNickname'], $timezone, $langTag);

        $userDetail['registerDate'] = DateHelper::fresnsDateTimeByTimezone($userDetail['registerDate'], $timezone, $langTag);

        $userDetail['waitDeleteDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['waitDeleteDateTime'], $timezone, $langTag);

        $userDetail['roleExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['roleExpiryDateTime'], $timezone, $langTag);

        $userDetail['interaction']['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['interaction']['followExpiryDateTime'], $timezone, $langTag);

        return $userDetail;
    }

    // handle group data count
    private static function handleGroupCount(array $groupDetail, Group $group): array
    {
        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'group_like_public_count',
            'group_dislike_public_count',
            'group_follow_public_count',
            'group_block_public_count',
        ]);

        $groupDetail['subgroupCount'] = $group->subgroup_count;
        $groupDetail['viewCount'] = $group->view_count;
        $groupDetail['likeCount'] = $configKeys['group_like_public_count'] ? $group->like_count : null;
        $groupDetail['dislikeCount'] = $configKeys['group_dislike_public_count'] ? $group->dislike_count : null;
        $groupDetail['followCount'] = $configKeys['group_follow_public_count'] ? $group->follow_count : null;
        $groupDetail['blockCount'] = $configKeys['group_block_public_count'] ? $group->block_count : null;
        $groupDetail['postCount'] = $group->post_count;
        $groupDetail['postDigestCount'] = $group->post_digest_count;
        $groupDetail['commentCount'] = $group->comment_count;
        $groupDetail['commentDigestCount'] = $group->comment_digest_count;

        return $groupDetail;
    }

    // handle hashtag data count
    private static function handleHashtagCount(array $hashtagDetail, Hashtag $hashtag): array
    {
        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'hashtag_like_public_count',
            'hashtag_dislike_public_count',
            'hashtag_follow_public_count',
            'hashtag_block_public_count',
        ]);

        $hashtagDetail['viewCount'] = $hashtag->view_count;
        $hashtagDetail['likeCount'] = $configKeys['hashtag_like_public_count'] ? $hashtag->like_count : null;
        $hashtagDetail['dislikeCount'] = $configKeys['hashtag_dislike_public_count'] ? $hashtag->dislike_count : null;
        $hashtagDetail['followCount'] = $configKeys['hashtag_follow_public_count'] ? $hashtag->follow_count : null;
        $hashtagDetail['blockCount'] = $configKeys['hashtag_block_public_count'] ? $hashtag->block_count : null;
        $hashtagDetail['postCount'] = $hashtag->post_count;
        $hashtagDetail['postDigestCount'] = $hashtag->post_digest_count;
        $hashtagDetail['commentCount'] = $hashtag->comment_count;
        $hashtagDetail['commentDigestCount'] = $hashtag->comment_digest_count;

        return $hashtagDetail;
    }
}
