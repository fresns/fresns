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
use App\Helpers\FileHelper;
use App\Helpers\InteractionHelper;
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Account;
use App\Models\ArchiveUsage;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\Geotag;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Mention;
use App\Models\OperationUsage;
use App\Models\Post;
use App\Models\PostLog;
use App\Models\User;
use App\Models\UserLike;
use Illuminate\Support\Str;

class DetailUtility
{
    // accountDetail
    public static function accountDetail(Account|int|string $accountOrAid = null, ?string $langTag = null, ?string $timezone = null, ?array $options = []): ?array
    {
        // $options = [
        //     'viewType' => '', // list, detail, quoted
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];

        $account = $accountOrAid;
        if (is_string($accountOrAid)) {
            $account = PrimaryHelper::fresnsModelByFsid('account', $accountOrAid);
        }

        if (empty($account)) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_detail_account_{$account->id}_{$langTag}";
        $cacheTag = 'fresnsAccounts';

        $accountDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($accountDetail)) {
            $accountData = $account->getAccountInfo($langTag);

            $item['connects'] = $account->getAccountConnects();
            $item['wallet'] = $account->getAccountWallet($langTag);

            $userList = [];
            foreach ($account->users as $user) {
                $userList[] = $user->uid;
            }

            $item['users'] = $userList;

            $accountDetail = array_merge($accountData, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($accountDetail, $cacheKey, $cacheTag, $cacheTime);
        }

        $users = [];
        foreach ($accountDetail['users'] as $user) {
            $users[] = self::userDetail($user, $langTag, $timezone, null, $options);
        }
        $accountDetail['users'] = $users;

        // handle date
        $accountDetail['waitDeleteDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountDetail['waitDeleteDateTime'], $timezone, $langTag);

        return $accountDetail;
    }

    // userDetail
    public static function userDetail(User|int|string $userOrUid = null, ?string $langTag = null, ?string $timezone = null, ?int $authUserId = null, ?array $options = []): ?array
    {
        // $options = [
        //     'viewType' => '', // list, detail, quoted
        //     'isLiveStats' => false,
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];

        $user = $userOrUid;
        if (is_numeric($userOrUid)) {
            $user = PrimaryHelper::fresnsModelByFsid('user', $userOrUid);
        }

        if (empty($user)) {
            return InteractionHelper::fresnsUserSubstitutionProfile('deactivate');
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_detail_user_{$user->id}_{$langTag}";
        $cacheTag = 'fresnsUsers';

        $userDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($userDetail)) {
            $userProfile = $user->getUserProfile();
            $userMainRole = $user->getUserMainRole($langTag);
            $userRoles = $user->getUserRoles($langTag);

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

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_USER, $user->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $user->id, $langTag);

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
            CacheHelper::put($userDetail, $cacheKey, $cacheTag, $cacheTime);
        }

        // archives
        if ($userDetail['archives'] && $user->id != $authUserId) {
            $archives = [];
            foreach ($userDetail['archives'] as $archive) {
                $item = $archive;
                $item['value'] = $archive['isPrivate'] ? null : $archive['value'];

                $archives[] = $item;
            }

            $userDetail['archives'] = $archives;
        }

        // user stats
        $isLiveStats = $options['isLiveStats'] ?? false;
        if ($isLiveStats) {
            $userStats = $user->getUserStats($langTag, $authUserId);
        } else {
            $cacheStatsKey = "fresns_detail_user_stats_{$user->id}";
            $userStats = CacheHelper::get($cacheStatsKey, $cacheTag);
            if (empty($userStats)) {
                $userStats = $user->getUserStats($langTag);

                CacheHelper::put($userStats, $cacheStatsKey, $cacheTag, 15, 15);
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
    public static function groupDetail(Group|int|string $groupOrGid = null, ?string $langTag = null, ?string $timezone = null, ?int $authUserId = null, ?array $options = []): ?array
    {
        // $options = [
        //     'viewType' => '', // list, detail, quoted
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];

        $group = $groupOrGid;
        if (is_string($groupOrGid)) {
            $group = PrimaryHelper::fresnsModelByFsid('group', $groupOrGid);
        }

        if (empty($group)) {
            return null;
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

            // interaction
            $item['interaction'] = InteractionHelper::fresnsInteraction('group', $langTag, $group->follow_method, $group->follow_app_fskey);

            $groupDetail = array_merge($groupInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($groupDetail, $cacheKey, $cacheTag, $cacheTime);
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

        // handle date and count
        $groupDetail = self::handleDetailDate('group', $groupDetail, $timezone, $langTag);
        $result = self::handleDetailCount('group', $groupDetail, $group);

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
    public static function hashtagDetail(Hashtag|int|string $hashtagOrHtid = null, ?string $langTag = null, ?string $timezone = null, ?int $authUserId = null, ?array $options = []): ?array
    {
        // $options = [
        //     'viewType' => '', // list, detail, quoted
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];

        $hashtag = $hashtagOrHtid;
        if (is_string($hashtagOrHtid)) {
            $hashtag = PrimaryHelper::fresnsModelByFsid('hashtag', $hashtagOrHtid);
        }

        if (empty($hashtag)) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_detail_hashtag_{$hashtag->id}_{$langTag}";
        $cacheTag = 'fresnsHashtags';

        $hashtagDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($hashtagDetail)) {
            $hashtagInfo = $hashtag->getHashtagInfo($langTag);

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_HASHTAG, $hashtag->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_HASHTAG, $hashtag->id, $langTag);

            // interaction
            $item['interaction'] = InteractionHelper::fresnsInteraction('hashtag', $langTag);

            $hashtagDetail = array_merge($hashtagInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($hashtagDetail, $cacheKey, $cacheTag, $cacheTime);
        }

        if ($authUserId) {
            $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_HASHTAG, $hashtag->id, $authUserId);

            $hashtagDetail['interaction'] = array_replace($hashtagDetail['interaction'], $interactionStatus);
        }

        // handle date and count
        $hashtagDetail = self::handleDetailDate('hashtag', $hashtagDetail, $timezone, $langTag);
        $result = self::handleDetailCount('hashtag', $hashtagDetail, $hashtag);

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

    // geotagDetail
    public static function geotagDetail(Geotag|int|string $geotagOrGtid = null, ?string $langTag = null, ?string $timezone = null, ?int $authUserId = null, ?array $options = []): ?array
    {
        // $options = [
        //     'viewType' => '', // list, detail, quoted
        //     'location' => [
        //         'mapId' => '',
        //         'longitude' => '',
        //         'latitude' => '',
        //     ],
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];

        $geotag = $geotagOrGtid;
        if (is_string($geotagOrGtid)) {
            $geotag = PrimaryHelper::fresnsModelByFsid('geotag', $geotagOrGtid);
        }

        if (empty($geotag)) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_detail_geotag_{$geotag->id}_{$langTag}";
        $cacheTag = 'fresnsGeotags';

        $geotagDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($geotagDetail)) {
            $geotagInfo = $geotag->getGeotagInfo($langTag);

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_GEOTAG, $geotag->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_GEOTAG, $geotag->id, $langTag);

            // distance
            $item['distance'] = null;
            $item['unit'] = ConfigHelper::fresnsConfigLengthUnit($langTag);

            // interaction
            $item['interaction'] = InteractionHelper::fresnsInteraction('geotag', $langTag);

            $geotagDetail = array_merge($geotagInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($geotagDetail, $cacheKey, $cacheTag, $cacheTime);
        }

        // distance
        $mapId = $options['location']['mapId'] ?? null;
        $longitude = $options['location']['longitude'] ?? null;
        $latitude = $options['location']['latitude'] ?? null;
        if ($longitude && $latitude) {
            $geotagDetail['distance'] = GeneralUtility::distanceOfLocation(
                $langTag,
                $geotag->map_longitude,
                $geotag->map_latitude,
                $longitude,
                $latitude,
                $geotag->map_id,
                $mapId,
            );
        }

        if ($authUserId) {
            $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_GEOTAG, $geotag->id, $authUserId);

            $geotagDetail['interaction'] = array_replace($geotagDetail['interaction'], $interactionStatus);
        }

        // handle date and count
        $geotagDetail = self::handleDetailDate('geotag', $geotagDetail, $timezone, $langTag);
        $result = self::handleDetailCount('geotag', $geotagDetail, $geotag);

        // subscribe
        $viewType = $options['viewType'] ?? null;
        if ($viewType && $viewType != 'quoted') {
            SubscribeUtility::notifyViewContent('geotag', $geotag->gtid, $viewType, $authUserId);
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

    // postDetail
    public static function postDetail(Post|int|string $postOrPid = null, ?string $langTag = null, ?string $timezone = null, ?int $authUserId = null, ?array $options = []): ?array
    {
        // $options = [
        //     'viewType' => '', // list, detail, quoted
        //     'contentFormat' => '', // html
        //     'location' => [
        //         'mapId' => '',
        //         'longitude' => '',
        //         'latitude' => '',
        //     ],
        //     'checkPermissions' => false,
        //     'isPreviewLikeUsers' => false,
        //     'isPreviewComments' => false,
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterGroup' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterHashtag' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterGeotag' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterAuthor' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterQuotedPost' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterPreviewLikeUser' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterPreviewComment' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];
        $viewType = $options['viewType'] ?? 'quoted';

        $post = $postOrPid;
        if (is_string($postOrPid)) {
            $post = PrimaryHelper::fresnsModelByFsid('post', $postOrPid);
        }

        if (empty($post)) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_detail_post_{$post->id}_{$langTag}";
        $cacheTag = 'fresnsPosts';

        $postDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($postDetail)) {
            $postInfo = $post->getPostInfo($langTag);
            $permissions = $post->permissions;

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST, $post->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_POST, $post->id, $langTag);

            // file
            $item['files'] = FileHelper::fresnsFileInfoListByTableColumn('posts', 'id', $post->id);

            // extends
            $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_POST, $post->id, $langTag);

            // group
            $item['group'] = $post->group?->gid;

            // hashtags
            $item['hashtags'] = [];
            if ($post->hashtags?->isNotEmpty()) {
                foreach ($post->hashtags as $hashtag) {
                    $hashtagItem[] = $hashtag->slug;
                }

                $item['hashtags'] = $hashtagItem;
            }

            // geotag
            $item['geotag'] = $post->geotag?->gtid;

            // author
            $item['author'] = $post->author?->uid;

            // quoted post
            $quotedPost = $post->quotedPost;
            $item['isMultiLevelQuote'] = (bool) $quotedPost?->quoted_post_id;
            $item['quotedPost'] = $quotedPost?->pid;

            $item['previewLikeUsers'] = [];
            $item['previewComments'] = [];

            // interaction
            $item['interaction'] = InteractionHelper::fresnsInteraction('post', $langTag);

            $item['manages'] = [];

            $item['controls'] = [
                'isAuthor' => false,
                'canEdit' => false,
                'canDelete' => $permissions['canDelete'] ?? true,
            ];

            // handle post detail content
            $newContent = ContentUtility::handleAndReplaceAll($postInfo['content'], $post->is_markdown, $post->user_id, Mention::TYPE_POST, $post->id);

            $detailContent = [
                'content' => $newContent,
                'files' => $item['files'],
            ];

            $fidArr = ContentUtility::extractFile($postInfo['content']);
            if ($fidArr) {
                $detailContent['content'] = ContentUtility::replaceFile($newContent);

                $detailContent['files'] = [
                    'images' => ArrUtility::forget($item['files']['images'], 'fid', $fidArr),
                    'videos' => ArrUtility::forget($item['files']['videos'], 'fid', $fidArr),
                    'audios' => ArrUtility::forget($item['files']['audios'], 'fid', $fidArr),
                    'documents' => ArrUtility::forget($item['files']['documents'], 'fid', $fidArr),
                ];
            }

            $briefLength = ConfigHelper::fresnsConfigByItemKey('post_brief_length');
            if ($postInfo['contentLength'] > $briefLength) {
                $postContent = Str::limit($postInfo['content'], $briefLength);
                $postContent = strip_tags($postContent);

                $postInfo['content'] = ContentUtility::handleAndReplaceAll($postContent, $post->is_markdown, $post->user_id, Mention::TYPE_POST, $post->id);
                $postInfo['isBrief'] = true;
            } else {
                $postInfo['content'] = $newContent;
            }

            $item['detailContent'] = $detailContent;

            // handle post permissions
            $readConfigContent = [
                'listContent' => null,
                'listIsBrief' => false,
                'detailContent' => null,
            ];
            if ($postInfo['readConfig']['isReadLocked']) {
                $previewPercentage = $postInfo['readConfig']['previewPercentage'] / 100;
                $readLength = intval($postInfo['contentLength'] * $previewPercentage);

                $readContent = Str::limit($post->content, $readLength);
                $readContentLength = Str::length($readContent);

                $newReadContent = ContentUtility::handleAndReplaceAll($readContent, $post->is_markdown, $post->user_id, Mention::TYPE_POST, $post->id);

                $listPreviewContent = $newReadContent;
                $listIsBrief = false;
                $detailPreviewContent = $newReadContent;
                if ($readContentLength > $briefLength) {
                    $previewContent = Str::limit($readContent, $briefLength);
                    $previewContent = strip_tags($previewContent);

                    $listPreviewContent = ContentUtility::handleAndReplaceAll($previewContent, $post->is_markdown, $post->user_id, Mention::TYPE_POST, $post->id);
                    $listIsBrief = true;
                }

                $readConfigContent = [
                    'listContent' => $listPreviewContent,
                    'listIsBrief' => $listIsBrief,
                    'detailContent' => $detailPreviewContent,
                ];
            }

            $item['readConfigContent'] = $readConfigContent;

            $postDetail = array_merge($postInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($postDetail, $cacheKey, $cacheTag, $cacheTime);
        }

        // group
        if ($postDetail['group']) {
            $groupOptions = [
                'viewType' => 'quoted',
                'filter' => [
                    'type' => $options['filterGroup']['type'] ?? null,
                    'keys' => $options['filterGroup']['keys'] ?? null,
                ],
            ];

            $postDetail['group'] = self::groupDetail($postDetail['group'], $langTag, null, $authUserId, $groupOptions);
        }

        // hashtags
        if ($postDetail['hashtags']) {
            $hashtagOptions = [
                'viewType' => 'quoted',
                'filter' => [
                    'type' => $options['filterHashtag']['type'] ?? null,
                    'keys' => $options['filterHashtag']['keys'] ?? null,
                ],
            ];

            $hashtags = [];
            foreach ($postDetail['hashtags'] as $hashtag) {
                $hashtags[] = self::hashtagDetail($hashtag, $langTag, null, $authUserId, $hashtagOptions);
            }
            $postDetail['hashtags'] = $hashtags;
        }

        // geotag
        if ($postDetail['geotag']) {
            $geotagOptions = [
                'viewType' => 'quoted',
                'location' => [
                    'mapId' => $options['location']['mapId'] ?? null,
                    'longitude' => $options['location']['longitude'] ?? null,
                    'latitude' => $options['location']['latitude'] ?? null,
                ],
                'filter' => [
                    'type' => $options['filterGeotag']['type'] ?? null,
                    'keys' => $options['filterGeotag']['keys'] ?? null,
                ],
            ];

            $postDetail['geotag'] = self::geotagDetail($postDetail['geotag'], $langTag, null, $authUserId, $geotagOptions);
        }

        // author
        $authorOptions = [
            'viewType' => 'quoted',
            'isLiveStats' => false,
            'filter' => [
                'type' => $options['filterAuthor']['type'] ?? null,
                'keys' => $options['filterAuthor']['keys'] ?? null,
            ],
        ];
        if ($postDetail['author']) {
            $postDetail['author'] = $postDetail['isAnonymous'] ? InteractionHelper::fresnsUserSubstitutionProfile('anonymous', $authorOptions['filter']['type'], $authorOptions['filter']['keys']) : self::userDetail($postDetail['author'], $langTag, null, $authUserId, $authorOptions);
        } else {
            $postDetail['author'] = InteractionHelper::fresnsUserSubstitutionProfile('deactivate', $authorOptions['filter']['type'], $authorOptions['filter']['keys']);
        }

        // quoted post
        if ($postDetail['quotedPost'] && $viewType != 'quoted') {
            $quotedPostOptions = [
                'viewType' => 'quoted',
                'isPreviewLikeUsers' => false,
                'isPreviewComments' => false,
                'filter' => [
                    'type' => $options['filterQuotedPost']['type'] ?? null,
                    'keys' => $options['filterQuotedPost']['keys'] ?? null,
                ],
            ];

            $postDetail['quotedPost'] = self::postDetail($postDetail['geotag'], $langTag, null, $authUserId, $quotedPostOptions);
        }

        // get preview configs
        $previewConfig = ConfigHelper::fresnsConfigByItemKeys([
            'preview_post_like_users',
            'preview_post_comments',
            'comment_visibility_rule',
        ]);

        // comment visibility rule
        if ($previewConfig['comment_visibility_rule'] > 0) {
            $visibilityTime = $post->created_at->addDay($previewConfig['comment_visibility_rule']);

            $postDetail['commentConfig']['visible'] = $visibilityTime->gt(now());
        }

        $isPreviewLikeUsers = $options['isPreviewLikeUsers'] ?? false;
        $isPreviewComments = $options['isPreviewComments'] ?? false;
        $publicComment = ($postDetail['commentConfig']['visible'] && $postDetail['commentConfig']['privacy'] == 'public');

        // get preview like users
        if ($isPreviewLikeUsers && $previewConfig['preview_post_like_users'] != 0) {
            $previewLikeUserOptions = [
                'viewType' => 'quoted',
                'filter' => [
                    'type' => $options['filterPreviewLikeUser']['type'] ?? null,
                    'keys' => $options['filterPreviewLikeUser']['keys'] ?? null,
                ],
            ];

            $postDetail['previewLikeUsers'] = self::getPreviewLikeUsers('post', $post->id, $post->like_count, $previewConfig['preview_post_like_users'], $langTag, $previewLikeUserOptions);
        }

        // get preview comments
        if ($isPreviewComments && $previewConfig['preview_post_comments'] != 0 && $publicComment) {
            $previewCommentOptions = [
                'viewType' => 'quoted',
                'filter' => [
                    'type' => $options['filterPreviewComment']['type'] ?? null,
                    'keys' => $options['filterPreviewComment']['keys'] ?? null,
                ],
            ];

            $postDetail['previewComments'] = self::getPostPreviewComments($post, $previewConfig['preview_post_comments'], $langTag, $previewCommentOptions);
        }

        // authUserId
        if ($authUserId) {
            // manages
            $postDetail['manages'] = ExtendUtility::getManageExtensions('post', $langTag, $authUserId, $post->group_id);

            if ($post->user_id == $authUserId) {
                $postDetail['controls']['isAuthor'] = true;
                $postDetail['controls']['canEdit'] = PermissionUtility::checkContentIsCanEdit('post', $post->created_at, $post->digest_state, $post->sticky_state, $timezone, $langTag);
                $postDetail['controls']['canDelete'] = $postDetail['controls']['canDelete'] ? PermissionUtility::checkContentIsCanDelete('post', $post->digest_state, $post->sticky_state) : false;
            } else {
                $postDetail['controls']['canDelete'] = false;
            }

            // interaction
            $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_POST, $post->id, $authUserId);
            $postDetail['interaction'] = array_replace($postDetail['interaction'], $interactionStatus);
        } else {
            $postDetail['controls']['canDelete'] = false;
        }

        // detail content
        if ($viewType == 'detail') {
            $postDetail['content'] = $postDetail['detailContent']['content'];
            $postDetail['isBrief'] = false;
            $postDetail['files'] = $postDetail['detailContent']['files'];
        }
        unset($postDetail['detailContent']);

        // checkPermissions
        $checkPermissions = $options['checkPermissions'] ?? false;
        if ($checkPermissions) {
            $postDetail = self::handlePostPermissions($postDetail, $post, $viewType, $authUserId);
        }

        // contentFormat
        $contentFormat = $options['contentFormat'] ?? null;
        if ($contentFormat == 'html' && $postDetail['content']) {
            $postDetail['content'] = $post->is_markdown ? Str::markdown($postDetail['content']) : nl2br($postDetail['content']);

            $searchArr = [
                '&lt;audio class=&quot;fresns_file_audio&quot; controls preload=&quot;metadata&quot; controlsList=&quot;nodownload&quot; src=&quot;',
                '&quot;&gt;</audio>',
            ];
            $replaceArr = [
                '<audio class="fresns_file_audio" controls preload="metadata" controlsList="nodownload" src="',
                '"></audio>',
            ];

            $postDetail['content'] = str_replace($searchArr, $replaceArr, $postDetail['content']);
        }

        $postDetail = self::handlePostAndCommentDate($postDetail, $timezone, $langTag);
        $result = self::handlePostDetailCount($postDetail, $post);

        // subscribe
        if ($viewType && $viewType != 'quoted') {
            SubscribeUtility::notifyViewContent('post', $post->pid, $viewType, $authUserId);
        }

        unset($result['readConfigContent']);

        // filter
        $filterType = $options['filter']['type'] ?? null;
        $filterKeys = $options['filter']['keys'] ?? null;
        $filterKeysArr = $filterKeys ? array_filter(explode(',', $filterKeys)) : [];

        if ($filterType && $filterKeysArr) {
            return ArrUtility::filter($result, $filterType, $filterKeysArr);
        }

        return $result;
    }

    // commentDetail
    public static function commentDetail(Comment|int|string $commentOrCid = null, ?string $langTag = null, ?string $timezone = null, ?int $authUserId = null, ?array $options = []): ?array
    {
        // $options = [
        //     'viewType' => '', // list, detail, quoted
        //     'contentFormat' => '', // html
        //     'location' => [
        //         'mapId' => '',
        //         'longitude' => '',
        //         'latitude' => '',
        //     ],
        //     'checkPermissions' => false,
        //     'isPreviewLikeUsers' => false,
        //     'isPreviewComments' => false,
        //     'outputReplyToPost' => false,
        //     'outputReplyToComment' => false,
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterHashtag' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterGeotag' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterAuthor' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterPreviewLikeUser' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterPreviewComment' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterReplyToPost' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterReplyToComment' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];
        $viewType = $options['viewType'] ?? 'quoted';

        $comment = $commentOrCid;
        if (is_string($commentOrCid)) {
            $comment = PrimaryHelper::fresnsModelByFsid('comment', $commentOrCid);
        }

        if (empty($comment)) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_detail_comment_{$comment->id}_{$langTag}";
        $cacheTag = 'fresnsComments';

        $commentDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($commentDetail)) {
            $commentInfo = $comment->getCommentInfo($langTag);

            $permissions = $comment->permissions;

            $post = $comment->post;

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_COMMENT, $comment->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_COMMENT, $comment->id, $langTag);

            // file
            $item['files'] = FileHelper::fresnsFileInfoListByTableColumn('comments', 'id', $comment->id);

            // extends
            $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_COMMENT, $comment->id, $langTag);

            // hashtags
            $item['hashtags'] = [];
            if ($comment->hashtags?->isNotEmpty()) {
                foreach ($comment->hashtags as $hashtag) {
                    $hashtagItem[] = $hashtag->slug;
                }

                $item['hashtags'] = $hashtagItem;
            }

            // geotag
            $item['geotag'] = $comment->geotag?->gtid;

            // author
            $item['author'] = $comment->author?->uid;
            $item['isPostAuthor'] = $comment->user_id == $post?->user_id ? true : false;

            $item['previewLikeUsers'] = [];
            $item['previewComments'] = [];

            // interaction
            $item['interaction'] = InteractionHelper::fresnsInteraction('comment', $langTag);

            // reply info
            $item['replyToPost'] = null;
            $item['replyToComment'] = null;
            $item['replyInfo'] = [
                'post' => $post?->pid,
                'comment' => $comment->parentComment?->cid,
                // 'comment' => ($comment->user_id == $comment->parentComment?->user_id) ? null : $comment->parentComment?->cid,
            ];

            $item['manages'] = [];

            $item['controls'] = [
                'isAuthor' => false,
                'canEdit' => false,
                'canDelete' => $permissions['canDelete'] ?? true,
            ];

            // handle post detail content
            $newContent = ContentUtility::handleAndReplaceAll($commentInfo['content'], $comment->is_markdown, $comment->user_id, Mention::TYPE_COMMENT, $comment->id);

            $detailContent = [
                'content' => $newContent,
                'files' => $item['files'],
            ];

            $fidArr = ContentUtility::extractFile($commentInfo['content']);
            if ($fidArr) {
                $detailContent['content'] = ContentUtility::replaceFile($newContent);

                $detailContent['files'] = [
                    'images' => ArrUtility::forget($item['files']['images'], 'fid', $fidArr),
                    'videos' => ArrUtility::forget($item['files']['videos'], 'fid', $fidArr),
                    'audios' => ArrUtility::forget($item['files']['audios'], 'fid', $fidArr),
                    'documents' => ArrUtility::forget($item['files']['documents'], 'fid', $fidArr),
                ];
            }

            $briefLength = ConfigHelper::fresnsConfigByItemKey('comment_brief_length');
            if ($commentInfo['contentLength'] > $briefLength) {
                $commentContent = Str::limit($commentInfo['content'], $briefLength);
                $commentContent = strip_tags($commentContent);

                $commentInfo['content'] = ContentUtility::handleAndReplaceAll($commentContent, $comment->is_markdown, $comment->user_id, Mention::TYPE_COMMENT, $comment->id);
                $commentInfo['isBrief'] = true;
            } else {
                $commentInfo['content'] = $newContent;
            }

            $item['detailContent'] = $detailContent;

            $commentDetail = array_merge($commentInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($commentDetail, $cacheKey, $cacheTag, $cacheTime);
        }

        // hashtags
        if ($commentDetail['hashtags']) {
            $hashtagOptions = [
                'viewType' => 'quoted',
                'filter' => [
                    'type' => $options['filterHashtag']['type'] ?? null,
                    'keys' => $options['filterHashtag']['keys'] ?? null,
                ],
            ];

            $hashtags = [];
            foreach ($commentDetail['hashtags'] as $hashtag) {
                $hashtags[] = self::hashtagDetail($hashtag, $langTag, null, $authUserId, $hashtagOptions);
            }
            $commentDetail['hashtags'] = $hashtags;
        }

        // geotag
        if ($commentDetail['geotag']) {
            $geotagOptions = [
                'viewType' => 'quoted',
                'location' => [
                    'mapId' => $options['location']['mapId'] ?? null,
                    'longitude' => $options['location']['longitude'] ?? null,
                    'latitude' => $options['location']['latitude'] ?? null,
                ],
                'filter' => [
                    'type' => $options['filterGeotag']['type'] ?? null,
                    'keys' => $options['filterGeotag']['keys'] ?? null,
                ],
            ];

            $commentDetail['geotag'] = self::geotagDetail($commentDetail['geotag'], $langTag, null, $authUserId, $geotagOptions);
        }

        // author
        $authorOptions = [
            'viewType' => 'quoted',
            'isLiveStats' => false,
            'filter' => [
                'type' => $options['filterAuthor']['type'] ?? null,
                'keys' => $options['filterAuthor']['keys'] ?? null,
            ],
        ];
        if ($commentDetail['author']) {
            $commentDetail['author'] = $commentDetail['isAnonymous'] ? InteractionHelper::fresnsUserSubstitutionProfile('anonymous', $authorOptions['filter']['type'], $authorOptions['filter']['keys']) : self::userDetail($commentDetail['author'], $langTag, null, $authUserId, $authorOptions);
        } else {
            $commentDetail['author'] = InteractionHelper::fresnsUserSubstitutionProfile('deactivate', $authorOptions['filter']['type'], $authorOptions['filter']['keys']);
        }

        $commentDetail['author']['isPostAuthor'] = $commentDetail['isAnonymous'] ? false : $commentDetail['isPostAuthor'];
        unset($commentDetail['isPostAuthor']);

        // get preview configs
        $previewConfig = ConfigHelper::fresnsConfigByItemKeys([
            'preview_comment_like_users',
            'preview_comment_replies',
        ]);

        $isPreviewLikeUsers = $options['isPreviewLikeUsers'] ?? false;
        $isPreviewComments = $options['isPreviewComments'] ?? false;

        // get preview like users
        if ($isPreviewLikeUsers && $previewConfig['preview_comment_like_users'] != 0) {
            $previewLikeUserOptions = [
                'viewType' => 'quoted',
                'filter' => [
                    'type' => $options['filterPreviewLikeUser']['type'] ?? null,
                    'keys' => $options['filterPreviewLikeUser']['keys'] ?? null,
                ],
            ];

            $commentDetail['previewLikeUsers'] = self::getPreviewLikeUsers('comment', $comment->id, $comment->like_count, $previewConfig['preview_comment_like_users'], $langTag, $previewLikeUserOptions);
        }

        // get preview comments
        if ($isPreviewComments && $previewConfig['preview_comment_replies'] != 0 && $commentDetail['privacy'] == 'public') {
            $previewCommentOptions = [
                'viewType' => 'quoted',
                'filter' => [
                    'type' => $options['filterPreviewComment']['type'] ?? null,
                    'keys' => $options['filterPreviewComment']['keys'] ?? null,
                ],
            ];

            $commentDetail['previewComments'] = self::getCommentPreviewComments($comment, $previewConfig['preview_comment_replies'], $langTag, $previewCommentOptions);
        }

        // detail content
        if ($viewType == 'detail') {
            $commentDetail['content'] = $commentDetail['detailContent']['content'];
            $commentDetail['isBrief'] = false;
            $commentDetail['files'] = $commentDetail['detailContent']['files'];
        }
        unset($commentDetail['detailContent']);

        // public or private
        $commentPrivacy = $commentDetail['privacy'];

        // authUserId
        if ($authUserId) {
            $commentModel = PrimaryHelper::fresnsModelByFsid('comment', $commentDetail['cid']); // use with (post)

            // manages
            $commentDetail['manages'] = ExtendUtility::getManageExtensions('comment', $langTag, $authUserId, $commentModel?->post?->group_id);

            if ($comment->user_id == $authUserId) {
                $commentPrivacy = 'public';

                $commentDetail['controls']['isAuthor'] = true;
                $commentDetail['controls']['canEdit'] = PermissionUtility::checkContentIsCanEdit('comment', $comment->created_at, $comment->digest_state, $comment->is_sticky, $timezone, $langTag);
                $commentDetail['controls']['canDelete'] = $commentDetail['controls']['canDelete'] ? PermissionUtility::checkContentIsCanDelete('comment', $comment->digest_state, $comment->is_sticky) : false;
            } else {
                $commentDetail['controls']['canDelete'] = false;
            }

            if ($commentModel?->post?->user_id == $authUserId) {
                $commentPrivacy = 'public';
            }
        } else {
            $commentDetail['controls']['canDelete'] = false;
        }

        // interaction
        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_COMMENT, $comment->id, $authUserId, true);
        $commentDetail['interaction'] = array_replace($commentDetail['interaction'], $interactionStatus);

        // checkPermissions
        $checkPermissions = $options['checkPermissions'] ?? false;
        if ($checkPermissions && $commentPrivacy == 'private') {
            $newContent = [
                'content' => null,
                'isBrief' => false,
                'archives' => [],
                'extends' => [
                    'texts' => [],
                    'infos' => [],
                    'actions' => [],
                ],
                'files' => [
                    'images' => [],
                    'videos' => [],
                    'audios' => [],
                    'documents' => [],
                ],
            ];

            $commentDetail = array_replace($commentDetail, $newContent);
        }

        $commentDetail['privacy'] = $commentPrivacy;

        $outputReplyToPost = $options['outputReplyToPost'] ?? false;
        $outputReplyToComment = $options['outputReplyToComment'] ?? false;

        if ($outputReplyToPost && $commentDetail['replyInfo']['post']) {
            $replyToPostOptions = [
                'viewType' => 'quoted',
                'filter' => [
                    'type' => $options['filterReplyToPost']['type'] ?? null,
                    'keys' => $options['filterReplyToPost']['keys'] ?? null,
                ],
            ];

            $commentDetail['replyToPost'] = self::postDetail($commentDetail['replyInfo']['post'], $langTag, null, $authUserId, $replyToPostOptions);
        }
        if ($outputReplyToComment && $commentDetail['replyInfo']['comment']) {
            $replyToCommentOptions = [
                'viewType' => 'quoted',
                'filter' => [
                    'type' => $options['filterReplyToComment']['type'] ?? null,
                    'keys' => $options['filterReplyToComment']['keys'] ?? null,
                ],
            ];

            $commentDetail['replyToComment'] = self::commentDetail($commentDetail['replyInfo']['comment'], $langTag, null, $authUserId, $replyToCommentOptions);
        }

        unset($commentDetail['replyInfo']);

        // contentFormat
        $contentFormat = $options['contentFormat'] ?? null;
        if ($contentFormat == 'html' && $commentDetail['content']) {
            $commentDetail['content'] = $comment->is_markdown ? Str::markdown($commentDetail['content']) : nl2br($commentDetail['content']);

            $searchArr = [
                '&lt;audio class=&quot;fresns_file_audio&quot; controls preload=&quot;metadata&quot; controlsList=&quot;nodownload&quot; src=&quot;',
                '&quot;&gt;</audio>',
            ];
            $replaceArr = [
                '<audio class="fresns_file_audio" controls preload="metadata" controlsList="nodownload" src="',
                '"></audio>',
            ];

            $commentDetail['content'] = str_replace($searchArr, $replaceArr, $commentDetail['content']);
        }

        $commentDetail = self::handlePostAndCommentDate($commentDetail, $timezone, $langTag);
        $result = self::handleCommentDetailCount($commentDetail, $comment);

        // subscribe
        if ($viewType && $viewType != 'quoted') {
            SubscribeUtility::notifyViewContent('comment', $comment->cid, $viewType, $authUserId);
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

    // postHistoryDetail
    public static function postHistoryDetail(PostLog|int|string $postLogOrHpid = null, ?string $langTag = null, ?string $timezone = null, ?int $authUserId = null, ?array $options = []): ?array
    {
        // $options = [
        //     'viewType' => '', // list, detail
        //     'contentFormat' => '', // html
        //     'checkPermissions' => false,
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterAuthor' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];
        $viewType = $options['viewType'] ?? 'list';

        $postLog = $postLogOrHpid;
        if (is_string($postLogOrHpid)) {
            $postLog = PrimaryHelper::fresnsModelByFsid('postLog', $postLogOrHpid);
        }

        if (empty($postLog)) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_detail_post_history_{$postLog->id}_{$langTag}";
        $cacheTag = 'fresnsPosts';

        $historyDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($historyDetail)) {
            $historyInfo = $postLog->getPostHistoryInfo();

            $post = $postLog->post;

            if (empty($post)) {
                return null;
            }

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST_LOG, $postLog->id, $langTag);

            // file
            $item['files'] = FileHelper::fresnsFileInfoListByTableColumn('post_logs', 'id', $postLog->id);

            // extends
            $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_POST_LOG, $postLog->id, $langTag);

            // author
            $item['author'] = $postLog->author?->uid;

            // handle post detail content
            $newContent = ContentUtility::handleAndReplaceAll($historyInfo['content'], $postLog->is_markdown, $postLog->user_id, Mention::TYPE_POST, $post->id);

            $detailContent = [
                'content' => $newContent,
                'files' => $item['files'],
            ];

            $fidArr = ContentUtility::extractFile($historyInfo['content']);
            if ($fidArr) {
                $detailContent['content'] = ContentUtility::replaceFile($newContent);

                $detailContent['files'] = [
                    'images' => ArrUtility::forget($item['files']['images'], 'fid', $fidArr),
                    'videos' => ArrUtility::forget($item['files']['videos'], 'fid', $fidArr),
                    'audios' => ArrUtility::forget($item['files']['audios'], 'fid', $fidArr),
                    'documents' => ArrUtility::forget($item['files']['documents'], 'fid', $fidArr),
                ];
            }

            $briefLength = ConfigHelper::fresnsConfigByItemKey('post_brief_length');
            if ($historyInfo['contentLength'] > $briefLength) {
                $postContent = Str::limit($historyInfo['content'], $briefLength);
                $postContent = strip_tags($postContent);

                $historyInfo['content'] = ContentUtility::handleAndReplaceAll($postContent, $postLog->is_markdown, $postLog->user_id, Mention::TYPE_POST, $post->id);
                $historyInfo['isBrief'] = true;
            } else {
                $historyInfo['content'] = $newContent;
            }

            $item['detailContent'] = $detailContent;

            // handle post permissions
            $permissions = $post->permissions;
            $readConfigIsReadLocked = $permissions['readConfig']['isReadLocked'] ?? false;
            $readConfigPreviewPercentage = $permissions['readConfig']['previewPercentage'] ?? 0;

            $readConfigContent = [
                'listContent' => null,
                'listIsBrief' => false,
                'detailContent' => null,
            ];
            if ($readConfigIsReadLocked) {
                $previewPercentage = $readConfigPreviewPercentage / 100;
                $readLength = intval($historyInfo['contentLength'] * $previewPercentage);

                $readContent = Str::limit($postLog->content, $readLength);
                $readContentLength = Str::length($readContent);

                $newReadContent = ContentUtility::handleAndReplaceAll($readContent, $postLog->is_markdown, $postLog->user_id, Mention::TYPE_POST, $post->id);

                $listPreviewContent = $newReadContent;
                $listIsBrief = false;
                $detailPreviewContent = $newReadContent;
                if ($readContentLength > $briefLength) {
                    $previewContent = Str::limit($readContent, $briefLength);
                    $previewContent = strip_tags($previewContent);

                    $listPreviewContent = ContentUtility::handleAndReplaceAll($previewContent, $postLog->is_markdown, $postLog->user_id, Mention::TYPE_POST, $post->id);
                    $listIsBrief = true;
                }

                $readConfigContent = [
                    'listContent' => $listPreviewContent,
                    'listIsBrief' => $listIsBrief,
                    'detailContent' => $detailPreviewContent,
                ];
            }

            $item['readConfigContent'] = $readConfigContent;

            $historyDetail = array_merge($historyInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($historyDetail, $cacheKey, $cacheTag, $cacheTime);
        }

        // detail content
        if ($viewType == 'detail') {
            $historyDetail['content'] = $historyDetail['detailContent']['content'];
            $historyDetail['isBrief'] = false;
            $historyDetail['files'] = $historyDetail['detailContent']['files'];
        }
        unset($historyDetail['detailContent']);

        // checkPermissions
        if ($options['checkPermissions'] ?? false) {
            $postModel = PrimaryHelper::fresnsModelById('post', $postLog->post_id);

            $historyDetail = self::handlePostPermissions($historyDetail, $postModel, $viewType, $authUserId);
        }

        // author
        $authorOptions = [
            'viewType' => 'quoted',
            'isLiveStats' => false,
            'filter' => [
                'type' => $options['filterAuthor']['type'] ?? null,
                'keys' => $options['filterAuthor']['keys'] ?? null,
            ],
        ];
        if ($historyDetail['author']) {
            $historyDetail['author'] = $historyDetail['isAnonymous'] ? InteractionHelper::fresnsUserSubstitutionProfile('anonymous', $authorOptions['filter']['type'], $authorOptions['filter']['keys']) : self::userDetail($historyDetail['author'], $langTag, null, $authUserId, $authorOptions);
        } else {
            $historyDetail['author'] = InteractionHelper::fresnsUserSubstitutionProfile('deactivate', $authorOptions['filter']['type'], $authorOptions['filter']['keys']);
        }

        // datetime
        $historyDetail['createdTimeAgo'] = DateHelper::fresnsHumanReadableTime($historyDetail['createdDatetime'], $langTag);
        $historyDetail['createdDatetime'] = DateHelper::fresnsFormatDateTime($historyDetail['createdDatetime'], $timezone, $langTag);

        // contentFormat
        $contentFormat = $options['contentFormat'] ?? null;
        if ($contentFormat == 'html' && $historyDetail['content']) {
            $historyDetail['content'] = $postLog->is_markdown ? Str::markdown($historyDetail['content']) : nl2br($historyDetail['content']);

            $searchArr = [
                '&lt;audio class=&quot;fresns_file_audio&quot; controls preload=&quot;metadata&quot; controlsList=&quot;nodownload&quot; src=&quot;',
                '&quot;&gt;</audio>',
            ];
            $replaceArr = [
                '<audio class="fresns_file_audio" controls preload="metadata" controlsList="nodownload" src="',
                '"></audio>',
            ];

            $historyDetail['content'] = str_replace($searchArr, $replaceArr, $historyDetail['content']);
        }

        unset($historyDetail['readConfigContent']);

        // filter
        $filterType = $options['filter']['type'] ?? null;
        $filterKeys = $options['filter']['keys'] ?? null;
        $filterKeysArr = $filterKeys ? array_filter(explode(',', $filterKeys)) : [];

        if ($filterType && $filterKeysArr) {
            return ArrUtility::filter($historyDetail, $filterType, $filterKeysArr);
        }

        return $historyDetail;
    }

    // commentHistoryDetail
    public static function commentHistoryDetail(CommentLog|int|string $commentLogOrHcid = null, ?string $langTag = null, ?string $timezone = null, ?int $authUserId = null, ?array $options = []): ?array
    {
        // $options = [
        //     'viewType' => '', // list, detail
        //     'contentFormat' => '', // html
        //     'checkPermissions' => false,
        //     'filter' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        //     'filterAuthor' => [
        //         'type' => '', // whitelist or blacklist
        //         'keys' => '',
        //     ],
        // ];
        $viewType = $options['viewType'] ?? 'list';

        $commentLog = $commentLogOrHcid;
        if (is_string($commentLogOrHcid)) {
            $commentLog = PrimaryHelper::fresnsModelByFsid('commentLog', $commentLogOrHcid);
        }

        if (empty($commentLog)) {
            return null;
        }

        $langTag = $langTag ?: ConfigHelper::fresnsConfigDefaultLangTag();

        $cacheKey = "fresns_detail_comment_history_{$commentLog->id}_{$langTag}";
        $cacheTag = 'fresnsComments';

        $historyDetail = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($historyDetail)) {
            $historyInfo = $commentLog->getCommentHistoryInfo();

            $comment = $commentLog->comment;

            if (empty($comment)) {
                return null;
            }

            $post = $commentLog->post;
            $postPermissions = $post->permissions;

            $item['privacy'] = $postPermissions['commentConfig']['privacy'] ?? 'public';

            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_COMMENT_LOG, $commentLog->id, $langTag);

            // file
            $item['files'] = FileHelper::fresnsFileInfoListByTableColumn('comment_logs', 'id', $commentLog->id);

            // extends
            $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_COMMENT_LOG, $commentLog->id, $langTag);

            // author
            $item['author'] = $commentLog->author?->uid;

            // handle post detail content
            $newContent = ContentUtility::handleAndReplaceAll($historyInfo['content'], $commentLog->is_markdown, $commentLog->user_id, Mention::TYPE_COMMENT, $comment->id);

            $detailContent = [
                'content' => $newContent,
                'files' => $item['files'],
            ];

            $fidArr = ContentUtility::extractFile($historyInfo['content']);
            if ($fidArr) {
                $detailContent['content'] = ContentUtility::replaceFile($newContent);

                $detailContent['files'] = [
                    'images' => ArrUtility::forget($item['files']['images'], 'fid', $fidArr),
                    'videos' => ArrUtility::forget($item['files']['videos'], 'fid', $fidArr),
                    'audios' => ArrUtility::forget($item['files']['audios'], 'fid', $fidArr),
                    'documents' => ArrUtility::forget($item['files']['documents'], 'fid', $fidArr),
                ];
            }

            $briefLength = ConfigHelper::fresnsConfigByItemKey('comment_brief_length');
            if ($historyInfo['contentLength'] > $briefLength) {
                $commentContent = Str::limit($historyInfo['content'], $briefLength);
                $commentContent = strip_tags($commentContent);

                $commentInfo['content'] = ContentUtility::handleAndReplaceAll($commentContent, $commentLog->is_markdown, $commentLog->user_id, Mention::TYPE_COMMENT, $comment->id);
                $commentInfo['isBrief'] = true;
            } else {
                $commentInfo['content'] = $newContent;
            }

            $item['detailContent'] = $detailContent;

            $historyDetail = array_merge($historyInfo, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($historyDetail, $cacheKey, $cacheTag, $cacheTime);
        }

        // author
        $authorOptions = [
            'viewType' => 'quoted',
            'isLiveStats' => false,
            'filter' => [
                'type' => $options['filterAuthor']['type'] ?? null,
                'keys' => $options['filterAuthor']['keys'] ?? null,
            ],
        ];
        if ($historyDetail['author']) {
            $historyDetail['author'] = $historyDetail['isAnonymous'] ? InteractionHelper::fresnsUserSubstitutionProfile('anonymous', $authorOptions['filter']['type'], $authorOptions['filter']['keys']) : self::userDetail($historyDetail['author'], $langTag, null, $authUserId, $authorOptions);
        } else {
            $historyDetail['author'] = InteractionHelper::fresnsUserSubstitutionProfile('deactivate', $authorOptions['filter']['type'], $authorOptions['filter']['keys']);
        }

        // detail content
        if ($viewType == 'detail') {
            $historyDetail['content'] = $historyDetail['detailContent']['content'];
            $historyDetail['isBrief'] = false;
            $historyDetail['files'] = $historyDetail['detailContent']['files'];
        }
        unset($historyDetail['detailContent']);

        // public or private
        $commentPrivacy = $historyDetail['privacy'];

        // authUserId
        if ($authUserId) {
            $commentLogModel = PrimaryHelper::fresnsModelByFsid('commentLog', $historyDetail['hcid']); // use with (post)

            if ($commentLog->user_id == $authUserId || $commentLogModel?->post?->user_id == $authUserId) {
                $commentPrivacy = 'public';
            }
        }

        // checkPermissions
        if ($options['checkPermissions'] ?? false && $commentPrivacy == 'private') {
            $newContent = [
                'content' => null,
                'isBrief' => false,
                'archives' => [],
                'extends' => [
                    'texts' => [],
                    'infos' => [],
                    'actions' => [],
                ],
                'files' => [
                    'images' => [],
                    'videos' => [],
                    'audios' => [],
                    'documents' => [],
                ],
            ];

            $historyDetail = array_replace($historyDetail, $newContent);
        }

        $historyDetail['privacy'] = $commentPrivacy;

        // datetime
        $historyDetail['createdTimeAgo'] = DateHelper::fresnsHumanReadableTime($historyDetail['createdDatetime'], $langTag);
        $historyDetail['createdDatetime'] = DateHelper::fresnsFormatDateTime($historyDetail['createdDatetime'], $timezone, $langTag);

        // contentFormat
        $contentFormat = $options['contentFormat'] ?? null;
        if ($contentFormat == 'html' && $historyDetail['content']) {
            $historyDetail['content'] = $commentLog->is_markdown ? Str::markdown($historyDetail['content']) : nl2br($historyDetail['content']);

            $searchArr = [
                '&lt;audio class=&quot;fresns_file_audio&quot; controls preload=&quot;metadata&quot; controlsList=&quot;nodownload&quot; src=&quot;',
                '&quot;&gt;</audio>',
            ];
            $replaceArr = [
                '<audio class="fresns_file_audio" controls preload="metadata" controlsList="nodownload" src="',
                '"></audio>',
            ];

            $historyDetail['content'] = str_replace($searchArr, $replaceArr, $historyDetail['content']);
        }

        // filter
        $filterType = $options['filter']['type'] ?? null;
        $filterKeys = $options['filter']['keys'] ?? null;
        $filterKeysArr = $filterKeys ? array_filter(explode(',', $filterKeys)) : [];

        if ($filterType && $filterKeysArr) {
            return ArrUtility::filter($historyDetail, $filterType, $filterKeysArr);
        }

        return $historyDetail;
    }

    /**
     * handle detail date and count.
     */
    // handle user data date
    private static function handleUserDate(array $userDetail, ?string $timezone = null, ?string $langTag = null): array
    {
        $userDetail['verifiedDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['verifiedDateTime'], $timezone, $langTag);

        $userDetail['expiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['expiryDateTime'], $timezone, $langTag);
        $userDetail['registerDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['registerDateTime'], $timezone, $langTag);

        $userDetail['lastPublishPostTimeAgo'] = DateHelper::fresnsHumanReadableTime($userDetail['lastPublishPostDateTime'], $langTag);
        $userDetail['lastPublishPostDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['lastPublishPostDateTime'], $timezone, $langTag);
        $userDetail['lastPublishCommentTimeAgo'] = DateHelper::fresnsHumanReadableTime($userDetail['lastPublishCommentDateTime'], $langTag);
        $userDetail['lastPublishCommentDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['lastPublishCommentDateTime'], $timezone, $langTag);
        $userDetail['lastEditUsernameDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['lastEditUsernameDateTime'], $timezone, $langTag);
        $userDetail['lastEditNicknameDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['lastEditNicknameDateTime'], $timezone, $langTag);

        $userDetail['waitDeleteDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['waitDeleteDateTime'], $timezone, $langTag);

        $userDetail['roleExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['roleExpiryDateTime'], $timezone, $langTag);

        $userDetail['interaction']['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userDetail['interaction']['followExpiryDateTime'], $timezone, $langTag);

        return $userDetail;
    }

    // handle data date (group, hashtag, geotag)
    private static function handleDetailDate(string $type, array $detail, ?string $timezone = null, ?string $langTag = null): array
    {
        $detail['createdTimeAgo'] = DateHelper::fresnsHumanReadableTime($detail['createdDatetime'], $langTag);
        $detail['createdDatetime'] = DateHelper::fresnsDateTimeByTimezone($detail['createdDatetime'], $timezone, $langTag);

        $detail['lastPublishPostTimeAgo'] = DateHelper::fresnsHumanReadableTime($detail['lastPublishPostDateTime'], $langTag);
        $detail['lastPublishPostDateTime'] = DateHelper::fresnsDateTimeByTimezone($detail['lastPublishPostDateTime'], $timezone, $langTag);

        $detail['lastPublishCommentTimeAgo'] = DateHelper::fresnsHumanReadableTime($detail['lastPublishCommentDateTime'], $langTag);
        $detail['lastPublishCommentDateTime'] = DateHelper::fresnsDateTimeByTimezone($detail['lastPublishCommentDateTime'], $timezone, $langTag);

        if ($type == 'group') {
            $detail['interaction']['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($detail['interaction']['followExpiryDateTime'], $timezone, $langTag);
        }

        return $detail;
    }

    // handle data count (group, hashtag, geotag)
    private static function handleDetailCount(string $type, array $detail, Group|Hashtag|Geotag $model): array
    {
        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            "{$type}_like_public_count",
            "{$type}_dislike_public_count",
            "{$type}_follow_public_count",
            "{$type}_block_public_count",
        ]);

        if ($type == 'group') {
            $detail['subgroupCount'] = $model->subgroup_count;
        }
        $detail['viewCount'] = $model->view_count;
        $detail['likeCount'] = $configKeys["{$type}_like_public_count"] ? $model->like_count : null;
        $detail['dislikeCount'] = $configKeys["{$type}_dislike_public_count"] ? $model->dislike_count : null;
        $detail['followCount'] = $configKeys["{$type}_follow_public_count"] ? $model->follow_count : null;
        $detail['blockCount'] = $configKeys["{$type}_block_public_count"] ? $model->block_count : null;
        $detail['postCount'] = $model->post_count;
        $detail['postDigestCount'] = $model->post_digest_count;
        $detail['commentCount'] = $model->comment_count;
        $detail['commentDigestCount'] = $model->comment_digest_count;

        return $detail;
    }

    // handle post and comment data date
    private static function handlePostAndCommentDate(array $detail, ?string $timezone = null, ?string $langTag = null): array
    {
        $detail['createdTimeAgo'] = DateHelper::fresnsHumanReadableTime($detail['createdDatetime'], $langTag);
        $detail['createdDatetime'] = DateHelper::fresnsFormatDateTime($detail['createdDatetime'], $timezone, $langTag);

        $detail['editedTimeAgo'] = DateHelper::fresnsHumanReadableTime($detail['editedDatetime'], $langTag);
        $detail['editedDatetime'] = DateHelper::fresnsFormatDateTime($detail['editedDatetime'], $timezone, $langTag);

        $detail['lastCommentTimeAgo'] = DateHelper::fresnsHumanReadableTime($detail['lastCommentDatetime'], $langTag);
        $detail['lastCommentDatetime'] = DateHelper::fresnsFormatDateTime($detail['lastCommentDatetime'], $timezone, $langTag);

        return $detail;
    }

    // handle post detail count
    private static function handlePostDetailCount(array $postDetail, Post $post): array
    {
        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'post_like_public_count',
            'post_dislike_public_count',
            'post_follow_public_count',
            'post_block_public_count',
            'comment_like_public_count',
            'comment_dislike_public_count',
            'comment_follow_public_count',
            'comment_block_public_count',
        ]);

        $postDetail['viewCount'] = $post->view_count;
        $postDetail['likeCount'] = $configKeys['post_like_public_count'] ? $post->like_count : null;
        $postDetail['dislikeCount'] = $configKeys['post_dislike_public_count'] ? $post->dislike_count : null;
        $postDetail['followCount'] = $configKeys['post_follow_public_count'] ? $post->follow_count : null;
        $postDetail['blockCount'] = $configKeys['post_block_public_count'] ? $post->block_count : null;
        $postDetail['commentCount'] = $post->comment_count;
        $postDetail['commentDigestCount'] = $post->comment_digest_count;
        $postDetail['commentLikeCount'] = $configKeys['comment_like_public_count'] ? $post->comment_like_count : null;
        $postDetail['commentDislikeCount'] = $configKeys['comment_dislike_public_count'] ? $post->comment_dislike_count : null;
        $postDetail['commentFollowCount'] = $configKeys['comment_follow_public_count'] ? $post->comment_follow_count : null;
        $postDetail['commentBlockCount'] = $configKeys['comment_block_public_count'] ? $post->comment_block_count : null;
        $postDetail['quoteCount'] = $post->quote_count;
        $postDetail['editedCount'] = $post->edit_count;

        return $postDetail;
    }

    // handle post permissions
    private static function handlePostPermissions(array $postDetail, Post $post, string $viewType, ?int $authUserId = null): array
    {
        $newContent = [
            'content' => null,
            'isBrief' => false,
            'archives' => [],
            'extends' => [
                'texts' => [],
                'infos' => [],
                'actions' => [],
            ],
            'files' => [
                'images' => [],
                'videos' => [],
                'audios' => [],
                'documents' => [],
            ],
        ];

        // groupDateLimit
        $groupDateLimit = PermissionUtility::getGroupContentDateLimit($post->group_id, $authUserId);
        if ($groupDateLimit['code'] == 37104) {
            return array_replace($postDetail, $newContent);
        }

        if ($groupDateLimit['datetime']) {
            $postTime = strtotime($post->created_at);
            $dateLimit = strtotime($groupDateLimit['datetime']);

            if ($postTime > $dateLimit) {
                return array_replace($postDetail, $newContent);
            }
        }

        // readConfig
        if (! $postDetail['readConfig']['isReadLocked']) {
            return $postDetail;
        }

        $postPermissions = $post->permissions;
        $whitelistUsers = $postPermissions['readConfig']['whitelist']['users'] ?? [];
        $whitelistRoles = $postPermissions['readConfig']['whitelist']['roles'] ?? [];

        $checkPostAuth = PermissionUtility::checkPostAuth($post->id, $whitelistUsers, $whitelistRoles, $authUserId);

        if ($checkPostAuth) {
            $postDetail['readConfig']['isReadLocked'] = false;

            return $postDetail;
        }

        if ($viewType == 'detail') {
            $newContent['content'] = $postDetail['readConfigContent']['detailContent'];

            $postDetail = array_replace($postDetail, $newContent);

            return $postDetail;
        }

        $newContent['content'] = $postDetail['readConfigContent']['listContent'];
        $newContent['isBrief'] = $postDetail['readConfigContent']['listIsBrief'];

        $postDetail = array_replace($postDetail, $newContent);

        return $postDetail;
    }

    // handle comment detail count
    private static function handleCommentDetailCount(array $commentDetail, Comment $comment): array
    {
        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'comment_like_public_count',
            'comment_dislike_public_count',
            'comment_follow_public_count',
            'comment_block_public_count',
        ]);

        $commentDetail['viewCount'] = $comment->view_count;
        $commentDetail['likeCount'] = $configKeys['comment_like_public_count'] ? $comment->like_count : null;
        $commentDetail['dislikeCount'] = $configKeys['comment_dislike_public_count'] ? $comment->dislike_count : null;
        $commentDetail['followCount'] = $configKeys['comment_follow_public_count'] ? $comment->follow_count : null;
        $commentDetail['blockCount'] = $configKeys['comment_block_public_count'] ? $comment->block_count : null;
        $commentDetail['commentCount'] = $comment->comment_count;
        $commentDetail['commentDigestCount'] = $comment->comment_digest_count;
        $commentDetail['commentLikeCount'] = $configKeys['comment_like_public_count'] ? $comment->comment_like_count : null;
        $commentDetail['commentDislikeCount'] = $configKeys['comment_dislike_public_count'] ? $comment->comment_dislike_count : null;
        $commentDetail['commentFollowCount'] = $configKeys['comment_follow_public_count'] ? $comment->comment_follow_count : null;
        $commentDetail['commentBlockCount'] = $configKeys['comment_block_public_count'] ? $comment->comment_block_count : null;
        $commentDetail['editedCount'] = $comment->edit_count;

        return $commentDetail;
    }

    /**
     * handle preview data.
     */
    // get preview like users
    private static function getPreviewLikeUsers(string $type, int $id, int $likeCount, int $limit, string $langTag, ?array $options = []): ?array
    {
        $cacheKey = "fresns_detail_{$type}_{$id}_preview_like_users_{$langTag}";
        $cacheTag = ($type == 'post') ? 'fresnsPosts' : 'fresnsComments';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        // get cache
        $userList = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($userList)) {
            $likeType = ($type == 'post') ? UserLike::TYPE_POST : UserLike::TYPE_COMMENT;

            $userLikes = UserLike::with('creator')
                ->has('creator')
                ->markType(UserLike::MARK_TYPE_LIKE)
                ->type($likeType)
                ->where('like_id', $id)
                ->limit($limit)
                ->oldest()
                ->get();

            $userList = [];
            foreach ($userLikes as $like) {
                $userList[] = self::userDetail($like->creator, $langTag);
            }

            CacheHelper::put($userList, $cacheKey, $cacheTag, 10, 10);
        }

        $userCount = count($userList);
        if ($userCount > 0 && $userCount < $likeCount) {
            CacheHelper::forgetFresnsMultilingual("fresns_detail_{$type}_{$id}_preview_like_users", $cacheTag);
        }

        // filter
        $filterType = $options['filter']['type'] ?? null;
        $filterKeys = $options['filter']['keys'] ?? null;
        $filterKeysArr = $filterKeys ? array_filter(explode(',', $filterKeys)) : [];

        if ($filterType && $filterKeysArr) {
            $newUserList = [];
            foreach ($userList as $user) {
                $newUserList[] = ArrUtility::filter($user, $filterType, $filterKeysArr);
            }

            return $newUserList;
        }

        return $userList;
    }

    // get post preview comments
    private static function getPostPreviewComments(Post $post, int $limit, string $langTag, ?array $options = []): ?array
    {
        $previewConfig = ConfigHelper::fresnsConfigByItemKeys([
            'preview_post_comments_type',
            'preview_post_comments_threshold',
        ]);

        if ($previewConfig['preview_post_comments_type'] == 'like' && $post->comment_like_count < $previewConfig['preview_post_comments_threshold']) {
            return [];
        }

        $cacheKey = "fresns_detail_post_{$post->id}_preview_comments_{$langTag}";
        $cacheTag = 'fresnsComments';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        // get cache
        $commentList = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($commentList)) {
            $commentQuery = Comment::has('author')
                ->where('post_id', $post->id)
                ->where('top_parent_id', 0)
                ->isEnabled()
                ->limit($limit);

            if ($previewConfig['preview_post_comments_type'] == 'like') {
                $commentQuery->orderByDesc('like_count');
            }

            if ($previewConfig['preview_post_comments_type'] == 'comment') {
                $commentQuery->where('comment_count', '>', $previewConfig['preview_post_comments_threshold'])->orderByDesc('comment_count');
            }

            if ($previewConfig['preview_post_comments_type'] == 'oldest') {
                $commentQuery->oldest();
            }

            if ($previewConfig['preview_post_comments_type'] == 'latest') {
                $commentQuery->latest();
            }

            $comments = $commentQuery->get();

            $options = [
                'viewType' => 'quoted',
                'isPreviewLikeUsers' => false,
                'isPreviewComments' => false,
                'outputReplyToPost' => false,
                'outputReplyToComment' => false,
            ];

            $commentList = [];
            foreach ($comments as $comment) {
                $commentList[] = self::commentDetail($comment, $langTag);
            }

            CacheHelper::put($commentList, $cacheKey, $cacheTag, 10, 10);
        }

        // filter
        $filterType = $options['filter']['type'] ?? null;
        $filterKeys = $options['filter']['keys'] ?? null;
        $filterKeysArr = $filterKeys ? array_filter(explode(',', $filterKeys)) : [];

        if ($filterType && $filterKeysArr) {
            $newCommentList = [];
            foreach ($commentList as $comment) {
                $newCommentList[] = ArrUtility::filter($comment, $filterType, $filterKeysArr);
            }

            return $newCommentList;
        }

        return $commentList;
    }

    // get comment preview comments
    private static function getCommentPreviewComments(Comment $comment, int $limit, string $langTag, ?array $options = []): ?array
    {
        $cacheKey = "fresns_detail_comment_{$comment->id}_preview_comments_{$langTag}";
        $cacheTag = 'fresnsComments';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return [];
        }

        // get cache
        $commentList = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($commentList)) {
            $previewConfig = ConfigHelper::fresnsConfigByItemKey('preview_comment_replies_type');

            $commentQuery = Comment::has('author')->where('top_parent_id', $comment->id)->isEnabled();

            if ($previewConfig == 'like') {
                $commentQuery->orderByDesc('like_count');
            }

            if ($previewConfig == 'oldest') {
                $commentQuery->oldest();
            }

            if ($previewConfig == 'latest') {
                $commentQuery->latest();
            }

            $comments = $commentQuery->limit($limit)->get();

            $options = [
                'viewType' => 'quoted',
                'isPreviewLikeUsers' => false,
                'isPreviewComments' => false,
                'outputReplyToPost' => false,
                'outputReplyToComment' => false,
            ];

            $commentList = [];
            foreach ($comments as $comment) {
                $commentList[] = self::commentDetail($comment, $langTag);
            }

            CacheHelper::put($commentList, $cacheKey, $cacheTag, 10, 10);
        }

        // filter
        $filterType = $options['filter']['type'] ?? null;
        $filterKeys = $options['filter']['keys'] ?? null;
        $filterKeysArr = $filterKeys ? array_filter(explode(',', $filterKeys)) : [];

        if ($filterType && $filterKeysArr) {
            $newCommentList = [];
            foreach ($commentList as $comment) {
                $newCommentList[] = ArrUtility::filter($comment, $filterType, $filterKeysArr);
            }

            return $newCommentList;
        }

        return $commentList;
    }
}
