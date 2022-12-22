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
use App\Models\ArchiveUsage;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\Mention;
use App\Models\OperationUsage;
use App\Models\User;
use App\Utilities\ArrUtility;
use App\Utilities\ConfigUtility;
use App\Utilities\ContentUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\PermissionUtility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class UserService
{
    public function userData(?User $user, string $langTag, ?string $timezone = null, ?int $authUserId = null)
    {
        if (! $user) {
            return null;
        }

        $cacheKey = "fresns_api_user_{$user->uid}_{$langTag}";

        $userData = Cache::get($cacheKey);

        if (empty($userData)) {
            $userProfile = $user->getUserProfile();
            $userMainRole = $user->getUserMainRole($langTag);

            $userProfile['nickname'] = ContentUtility::replaceBlockWords('user', $userProfile['nickname']);
            $userProfile['bio'] = ContentUtility::replaceBlockWords('user', $userProfile['bio']);

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

            $item['stats'] = UserService::getUserStats($user, $langTag);
            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST, $user->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $user->id, $langTag);
            $item['extends'] = ExtendUtility::getContentExtends(ExtendUsage::TYPE_USER, $user->id, $langTag);
            $item['roles'] = PermissionUtility::getUserRoles($user->id, $langTag);

            if ($item['operations']['diversifyImages']) {
                $decorate = ArrUtility::pull($item['operations']['diversifyImages'], 'code', 'decorate');
                $verifiedIcon = ArrUtility::pull($item['operations']['diversifyImages'], 'code', 'verified');

                $userProfile['decorate'] = $decorate['imageUrl'] ?? null;
                $userProfile['verifiedIcon'] = $verifiedIcon['imageUrl'] ?? null;
            }

            $userData = array_merge($userProfile, $userMainRole, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);
            CacheHelper::put($userData, $cacheKey, ['fresnsUsers', 'fresnsUserData'], null, $cacheTime);
        }

        $userData['stats'] = UserService::getUserStats($user, $langTag);

        $interactionConfig = InteractionHelper::fresnsUserInteraction($langTag);
        $interactionStatus = InteractionUtility::getInteractionStatus(InteractionUtility::TYPE_USER, $user->id, $authUserId);
        $userData['interaction'] = array_merge($interactionConfig, $interactionStatus);

        $userData['conversation'] = PermissionUtility::checkUserConversationPerm($user->id, $authUserId, $langTag);

        if ($timezone) {
            return UserService::handleUserDate($userData, $timezone, $langTag);
        }

        return $userData;
    }

    // get user stats
    public static function getUserStats(User $user, string $langTag, ?int $authUserId = null)
    {
        $stats = $user->getUserStats($langTag);

        if ($user->id === $authUserId) {
            $statConfig = ConfigHelper::fresnsConfigByItemKeys([
                'user_liker_count', 'user_disliker_count', 'user_follower_count', 'user_blocker_count',
                'my_liker_count', 'my_disliker_count', 'my_follower_count', 'my_blocker_count',
            ], $langTag);

            if (! $statConfig['user_liker_count']) {
                $stats['likeMeCount'] = $statConfig['my_liker_count'] ? $stats['likeMeCount'] : null;
            }

            if (! $statConfig['user_disliker_count']) {
                $stats['dislikeMeCount'] = $statConfig['my_disliker_count'] ? $stats['dislikeMeCount'] : null;
            }

            if (! $statConfig['user_follower_count']) {
                $stats['followMeCount'] = $statConfig['my_follower_count'] ? $stats['followMeCount'] : null;
            }

            if (! $statConfig['user_blocker_count']) {
                $stats['blockMeCount'] = $statConfig['my_blocker_count'] ? $stats['blockMeCount'] : null;
            }
        } else {
            $statConfig = ConfigHelper::fresnsConfigByItemKeys([
                'it_posts', 'it_comments',
                'it_like_users', 'it_like_groups', 'it_like_hashtags', 'it_like_posts', 'it_like_comments',
                'it_dislike_users', 'it_dislike_groups', 'it_dislike_hashtags', 'it_dislike_posts', 'it_dislike_comments',
                'it_follow_users', 'it_follow_groups', 'it_follow_hashtags', 'it_follow_posts', 'it_follow_comments',
                'it_block_users', 'it_block_groups', 'it_block_hashtags', 'it_block_posts', 'it_block_comments',

                'user_liker_count', 'user_disliker_count', 'user_follower_count', 'user_blocker_count',
            ], $langTag);

            $stats['likeUserCount'] = $statConfig['it_like_users'] ? $stats['likeUserCount'] : null;
            $stats['likeGroupCount'] = $statConfig['it_like_groups'] ? $stats['likeGroupCount'] : null;
            $stats['likeHashtagCount'] = $statConfig['it_like_hashtags'] ? $stats['likeHashtagCount'] : null;
            $stats['likePostCount'] = $statConfig['it_like_posts'] ? $stats['likePostCount'] : null;
            $stats['likeCommentCount'] = $statConfig['it_like_comments'] ? $stats['likeCommentCount'] : null;

            $stats['dislikeUserCount'] = $statConfig['it_dislike_users'] ? $stats['dislikeUserCount'] : null;
            $stats['dislikeGroupCount'] = $statConfig['it_dislike_groups'] ? $stats['dislikeGroupCount'] : null;
            $stats['dislikeHashtagCount'] = $statConfig['it_dislike_hashtags'] ? $stats['dislikeHashtagCount'] : null;
            $stats['dislikePostCount'] = $statConfig['it_dislike_posts'] ? $stats['dislikePostCount'] : null;
            $stats['dislikeCommentCount'] = $statConfig['it_dislike_comments'] ? $stats['dislikeCommentCount'] : null;

            $stats['followUserCount'] = $statConfig['it_follow_users'] ? $stats['followUserCount'] : null;
            $stats['followGroupCount'] = $statConfig['it_follow_groups'] ? $stats['followGroupCount'] : null;
            $stats['followHashtagCount'] = $statConfig['it_follow_hashtags'] ? $stats['followHashtagCount'] : null;
            $stats['followPostCount'] = $statConfig['it_follow_posts'] ? $stats['followPostCount'] : null;
            $stats['followCommentCount'] = $statConfig['it_follow_comments'] ? $stats['followCommentCount'] : null;

            $stats['blockUserCount'] = $statConfig['it_block_users'] ? $stats['blockUserCount'] : null;
            $stats['blockGroupCount'] = $statConfig['it_block_groups'] ? $stats['blockGroupCount'] : null;
            $stats['blockHashtagCount'] = $statConfig['it_block_hashtags'] ? $stats['blockHashtagCount'] : null;
            $stats['blockPostCount'] = $statConfig['it_block_posts'] ? $stats['blockPostCount'] : null;
            $stats['blockCommentCount'] = $statConfig['it_block_comments'] ? $stats['blockCommentCount'] : null;

            $stats['likeMeCount'] = $statConfig['user_liker_count'] ? $stats['likeMeCount'] : null;
            $stats['dislikeMeCount'] = $statConfig['user_disliker_count'] ? $stats['dislikeMeCount'] : null;
            $stats['followMeCount'] = $statConfig['user_follower_count'] ? $stats['followMeCount'] : null;
            $stats['blockMeCount'] = $statConfig['user_blocker_count'] ? $stats['blockMeCount'] : null;

            if (! $statConfig['it_posts']) {
                $stats['postPublishCount'] = null;
                $stats['postDigestCount'] = null;
                $stats['postLikeCount'] = null;
                $stats['postDislikeCount'] = null;
                $stats['postFollowCount'] = null;
                $stats['postBlockCount'] = null;
            }

            if (! $statConfig['it_comments']) {
                $stats['commentPublishCount'] = null;
                $stats['commentDigestCount'] = null;
                $stats['commentLikeCount'] = null;
                $stats['commentDislikeCount'] = null;
                $stats['commentFollowCount'] = null;
                $stats['commentBlockCount'] = null;
            }

            $stats['extcredits1'] = ($stats['extcredits1Status'] == 3) ? $stats['extcredits1'] : null;
            $stats['extcredits2'] = ($stats['extcredits2Status'] == 3) ? $stats['extcredits2'] : null;
            $stats['extcredits3'] = ($stats['extcredits3Status'] == 3) ? $stats['extcredits3'] : null;
            $stats['extcredits4'] = ($stats['extcredits4Status'] == 3) ? $stats['extcredits4'] : null;
            $stats['extcredits5'] = ($stats['extcredits5Status'] == 3) ? $stats['extcredits5'] : null;
        }

        return $stats;
    }

    // handle user data date
    public static function handleUserDate(?array $userData, string $timezone, string $langTag)
    {
        if (empty($userData)) {
            return $userData;
        }

        $userData['birthday'] = DateHelper::fresnsDateTimeByTimezone($userData['birthday'], $timezone, $langTag);

        $userData['verifiedDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData['verifiedDateTime'], $timezone, $langTag);

        $userData['expiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData['expiryDateTime'], $timezone, $langTag);

        $userData['lastPublishPost'] = DateHelper::fresnsDateTimeByTimezone($userData['lastPublishPost'], $timezone, $langTag);
        $userData['lastPublishComment'] = DateHelper::fresnsDateTimeByTimezone($userData['lastPublishComment'], $timezone, $langTag);
        $userData['lastEditUsername'] = DateHelper::fresnsDateTimeByTimezone($userData['lastEditUsername'], $timezone, $langTag);
        $userData['lastEditNickname'] = DateHelper::fresnsDateTimeByTimezone($userData['lastEditNickname'], $timezone, $langTag);

        $userData['registerDate'] = DateHelper::fresnsDateTimeByTimezone($userData['registerDate'], $timezone, $langTag);

        $userData['waitDeleteDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData['waitDeleteDateTime'], $timezone, $langTag);
        $userData['deactivateTime'] = DateHelper::fresnsDateTimeByTimezone($userData['deactivateTime'], $timezone, $langTag);

        $userData['roleExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData['roleExpiryDateTime'], $timezone, $langTag);

        $userData['interaction']['followExpiryDateTime'] = DateHelper::fresnsDateTimeByTimezone($userData['interaction']['followExpiryDateTime'], $timezone, $langTag);

        return $userData;
    }

    // check content view permission
    public static function checkUserContentViewPerm(string $dateTime, ?int $authUserId = null)
    {
        if (empty($authUserId)) {
            return;
        }

        $modeConfig = ConfigHelper::fresnsConfigByItemKey('site_mode');
        if ($modeConfig == 'public') {
            return;
        }

        $authUser = PrimaryHelper::fresnsModelById('user', $authUserId);

        $contentCreateTime = strtotime($dateTime);
        $dateLimit = strtotime($authUser->expired_at);

        if ($contentCreateTime > $dateLimit) {
            throw new ApiException(35304);
        }
    }

    // get content date limit
    public static function getContentDateLimit(?int $authUserId = null)
    {
        if (empty($authUserId)) {
            return null;
        }

        $modeConfig = ConfigHelper::fresnsConfigByItemKey('site_mode');

        if ($modeConfig == 'public') {
            return null;
        }

        $authUser = PrimaryHelper::fresnsModelById('user', $authUserId);

        return $authUser?->expired_at;
    }

    // check publish perm
    // $type = post / comment
    public function checkPublishPerm(string $type, int $authUserId, ?int $mainId = null, ?string $langTag = null, ?string $timezone = null)
    {
        // Check time limit
        $contentInterval = PermissionUtility::checkContentIntervalTime($authUserId, $type);
        if (! $contentInterval && ! $mainId) {
            throw new ApiException(36119);
        }

        $publishConfig = ConfigUtility::getPublishConfigByType($authUserId, $type, $langTag, $timezone);

        // Check publication requirements
        if (! $publishConfig['perm']['publish']) {
            throw new ApiException(36104, 'Fresns', $publishConfig['perm']['tips']);
        }

        // Check additional requirements
        if ($publishConfig['limit']['status']) {
            switch ($publishConfig['limit']['type']) {
                // period Y-m-d H:i:s
                case 1:
                    $dbDateTime = DateHelper::fresnsDatabaseCurrentDateTime();
                    $newDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dbDateTime);
                    $periodStart = Carbon::createFromFormat('Y-m-d H:i:s', $publishConfig['limit']['periodStart']);
                    $periodEnd = Carbon::createFromFormat('Y-m-d H:i:s', $publishConfig['limit']['periodEnd']);

                    $isInTime = $newDateTime->between($periodStart, $periodEnd);
                    if ($isInTime) {
                        throw new ApiException(36304);
                    }
                break;

                // cycle H:i
                case 2:
                    $dbDateTime = DateHelper::fresnsDatabaseCurrentDateTime();
                    $newDateTime = Carbon::createFromFormat('Y-m-d H:i:s', $dbDateTime);
                    $dbDate = date('Y-m-d', $dbDateTime);
                    $cycleStart = "{$dbDate} {$publishConfig['limit']['cycleStart']}:00"; // Y-m-d H:i:s
                    $cycleEnd = "{$dbDate} {$publishConfig['limit']['cycleEnd']}:00"; // Y-m-d H:i:s

                    $periodStart = Carbon::createFromFormat('Y-m-d H:i:s', $cycleStart); // 2022-07-01 22:30:00
                    $periodEnd = Carbon::createFromFormat('Y-m-d H:i:s', $cycleEnd); // 2022-07-01 08:30:00

                    if ($periodEnd->lt($periodStart)) {
                        // next day 2022-07-02 08:30:00
                        $periodEnd = $periodEnd->addDay();
                    }

                    $isInTime = $newDateTime->between($periodStart, $periodEnd);
                    if ($isInTime) {
                        throw new ApiException(36304);
                    }
                break;
            }
        }
    }
}
