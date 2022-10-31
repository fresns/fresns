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
use App\Helpers\InteractiveHelper;
use App\Models\ArchiveUsage;
use App\Models\ExtendUsage;
use App\Models\File;
use App\Models\OperationUsage;
use App\Models\User;
use App\Utilities\ArrUtility;
use App\Utilities\ContentUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractiveUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Facades\Cache;

class UserService
{
    public function userData(?User $user, string $langTag, string $timezone, ?int $authUserId = null)
    {
        if (! $user) {
            return null;
        }

        $cacheKey = "fresns_api_user_{$user->id}_{$langTag}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);

        $userProfile = Cache::remember($cacheKey, $cacheTime, function () use ($user, $langTag, $timezone) {
            $userProfile = $user->getUserProfile($langTag, $timezone);
            $userMainRole = $user->getUserMainRole($langTag, $timezone);

            $userProfile['nickname'] = ContentUtility::replaceBlockWords('user', $userProfile['nickname']);
            $userProfile['bio'] = ContentUtility::replaceBlockWords('user', $userProfile['bio']);

            $item['stats'] = $user->getUserStats($langTag);
            $item['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST, $user->id, $langTag);
            $item['operations'] = ExtendUtility::getOperations(OperationUsage::TYPE_USER, $user->id, $langTag);
            $item['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_USER, $user->id, $langTag);
            $item['roles'] = $user->getUserRoles($langTag, $timezone);

            if ($item['operations']['diversifyImages']) {
                $decorate = ArrUtility::pull($item['operations']['diversifyImages'], 'code', 'decorate');
                $verifiedIcon = ArrUtility::pull($item['operations']['diversifyImages'], 'code', 'verified');

                $userProfile['decorate'] = $decorate['imageUrl'] ?? null;
                $userProfile['verifiedIcon'] = $verifiedIcon['imageUrl'] ?? null;
            }

            return array_merge($userProfile, $userMainRole, $item);
        });

        $item['stats'] = UserService::getUserStats($user, $langTag, $authUserId);

        $interactiveConfig = InteractiveHelper::fresnsUserInteractive($langTag);
        $interactiveStatus = InteractiveUtility::getInteractiveStatus(InteractiveUtility::TYPE_USER, $user->id, $authUserId);
        $followMeStatus['followMeStatus'] = InteractiveUtility::checkUserFollow(InteractiveUtility::TYPE_USER, $authUserId, $user->id);
        $blockMeStatus['blockMeStatus'] = InteractiveUtility::checkUserBlock(InteractiveUtility::TYPE_USER, $authUserId, $user->id);
        $item['interactive'] = array_merge($interactiveConfig, $interactiveStatus, $followMeStatus, $blockMeStatus);

        $item['dialog'] = PermissionUtility::checkUserDialogPerm($user->id, $authUserId, $langTag);

        $data = array_merge($userProfile, $item);

        return $data;
    }

    // check content view perm permission
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

    // check content view perm permission
    public static function checkUserContentViewPerm(string $dateTime, ?int $authUserId = null)
    {
        $userContentViewPerm = PermissionUtility::getUserContentViewPerm($authUserId);

        if ($userContentViewPerm['type'] == 2) {
            $dateLimit = strtotime($userContentViewPerm['dateLimit']);
            $contentCreateTime = strtotime($dateTime);

            if ($dateLimit < $contentCreateTime) {
                throw new ApiException(35304);
            }
        }
    }
}
