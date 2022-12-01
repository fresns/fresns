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
use App\Models\File;
use App\Models\PluginUsage;
use App\Models\UserBlock;
use App\Models\UserFollow;
use App\Models\UserLike;
use App\Utilities\ExtendUtility;
use Illuminate\Support\Facades\Cache;

class InteractionService
{
    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_POST = 4;
    const TYPE_COMMENT = 5;

    // check interaction setting
    public static function checkInteractionSetting(string $interactionType, string $markType)
    {
        $setKey = match ($interactionType) {
            'like' => "{$markType}_likers",
            'dislike' => "{$markType}_dislikers",
            'follow' => "{$markType}_followers",
            'block' => "{$markType}_blockers",
        };

        $interactionSet = ConfigHelper::fresnsConfigByItemKey($setKey);
        if (! $interactionSet) {
            throw new ApiException(36201);
        }
    }

    // check my interaction setting
    public static function checkMyInteractionSetting(string $interactionType, string $markType)
    {
        $setKey = match ($interactionType) {
            'like' => "{$markType}_likers",
            'dislike' => "{$markType}_dislikers",
            'follow' => "{$markType}_followers",
            'block' => "{$markType}_blockers",
        };

        $interactionSet = ConfigHelper::fresnsConfigByItemKey($setKey);
        if ($interactionSet) {
            return;
        }

        $mySetKey = match ($interactionType) {
            'like' => 'my_likers',
            'dislike' => 'my_dislikers',
            'follow' => 'my_followers',
            'block' => 'my_blockers',
        };

        $myInteractionSet = ConfigHelper::fresnsConfigByItemKey($mySetKey);
        if (! $myInteractionSet) {
            throw new ApiException(36201);
        }
    }

    // get the users who marked it
    public function getUsersWhoMarkIt(string $getType, string $markType, int $markId, string $orderDirection, string $langTag, string $timezone, ?int $authUserId = null)
    {
        switch ($getType) {
                // like
            case 'like':
                $interactionQuery = UserLike::markType(UserLike::MARK_TYPE_LIKE)->where('like_id', $markId);
            break;

                // dislike
            case 'dislike':
                $interactionQuery = UserLike::markType(UserLike::MARK_TYPE_DISLIKE)->where('like_id', $markId);
            break;

                // follow
            case 'follow':
                $interactionQuery = UserFollow::where('follow_id', $markId);
            break;

                // block
            case 'block':
                $interactionQuery = UserBlock::where('block_id', $markId);
            break;
        }

        $interactionData = $interactionQuery->with('creator')
            ->type($markType)
            ->orderBy('created_at', $orderDirection)
            ->paginate(\request()->get('pageSize', 15));

        $service = new UserService();

        $paginateData = [];
        foreach ($interactionData as $interaction) {
            if (empty($interaction->creator)) {
                continue;
            }

            $paginateData[] = $service->userData($interaction->creator, $langTag, $timezone, $authUserId);
        }

        return [
            'paginateData' => $paginateData,
            'interactionData' => $interactionData,
        ];
    }

    // get a list of the content it marks
    public function getItMarkList(string $getType, string $markTypeName, int $userId, string $orderDirection, string $langTag, string $timezone, ?int $authUserId = null)
    {
        switch ($getType) {
            // like
            case 'like':
                $markQuery = UserLike::markType(UserLike::MARK_TYPE_LIKE);
            break;

            // dislike
            case 'dislike':
                $markQuery = UserLike::markType(UserLike::MARK_TYPE_DISLIKE);
            break;

            // follow
            case 'follow':
                $markQuery = UserFollow::query();
            break;

            // block
            case 'block':
                $markQuery = UserBlock::query();
            break;
        }

        $markType = match ($markTypeName) {
            'user' => 1,
            'group' => 2,
            'hashtag' => 3,
            'post' => 4,
            'comment' => 5,

            'users' => 1,
            'groups' => 2,
            'hashtags' => 3,
            'posts' => 4,
            'comments' => 5,
        };

        $markData = $markQuery->with('user')
            ->where('user_id', $userId)
            ->type($markType)
            ->orderBy('created_at', $orderDirection)
            ->paginate(\request()->get('pageSize', 15));

        $paginateData = [];

        switch ($markTypeName) {
            // users
            case 'users':
                $service = new UserService();
                foreach ($markData as $mark) {
                    if (empty($mark->user)) {
                        continue;
                    }

                    $paginateData[] = $service->userData($mark->user, $langTag, $timezone, $authUserId);
                }
            break;

            // groups
            case 'groups':
                $service = new GroupService();
                foreach ($markData as $mark) {
                    if (empty($mark->group)) {
                        continue;
                    }

                    $paginateData[] = $service->groupData($mark->group, $langTag, $timezone, $authUserId);
                }
            break;

            // hashtags
            case 'hashtags':
                $service = new HashtagService();
                foreach ($markData as $mark) {
                    if (empty($mark->hashtag)) {
                        continue;
                    }

                    $paginateData[] = $service->hashtagData($mark->hashtag, $langTag, $timezone, $authUserId);
                }
            break;

            // posts
            case 'posts':
                $service = new PostService();
                foreach ($markData as $mark) {
                    if (empty($mark->post)) {
                        continue;
                    }

                    $paginateData[] = $service->postData($mark->post, 'list', $langTag, $timezone, $authUserId);
                }
            break;

            // comments
            case 'comments':
                $service = new CommentService();
                foreach ($markData as $mark) {
                    if (empty($mark->comment)) {
                        continue;
                    }

                    $paginateData[] = $service->commentData($mark->comment, 'list', $langTag, $timezone, $authUserId);
                }
            break;
        }

        return [
            'paginateData' => $paginateData,
            'markData' => $markData,
        ];
    }

    // get manage extends
    public static function getManageExtends(string $type, string $langTag, ?int $authUserId = null)
    {
        $cacheKey = $authUserId ? "fresns_api_{$type}_manages_{$authUserId}_{$langTag}" : "fresns_api_guest_{$type}_manages_{$langTag}";
        $nullCacheKey = CacheHelper::getNullCacheKey($cacheKey);

        // null cache count
        if (Cache::get($nullCacheKey) > CacheHelper::NULL_CACHE_COUNT) {
            return [];
        }

        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);

        $scene = match ($type) {
            'post' => PluginUsage::SCENE_POST,
            'comment' => PluginUsage::SCENE_COMMENT,
            'user' => PluginUsage::SCENE_USER,
        };

        // Cache::tags(['fresnsApiExtensions'])
        $manages = Cache::remember($cacheKey, $cacheTime, function () use ($scene, $authUserId, $langTag) {
            return ExtendUtility::getPluginUsages(PluginUsage::TYPE_MANAGE, null, $scene, $authUserId, $langTag);
        });

        // null cache count
        if (empty($manages)) {
            CacheHelper::nullCacheCount($cacheKey, $nullCacheKey);
        }

        return $manages;
    }
}
