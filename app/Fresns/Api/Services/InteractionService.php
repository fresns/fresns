<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Exceptions\ApiException;
use App\Helpers\ConfigHelper;
use App\Models\UserBlock;
use App\Models\UserFollow;
use App\Models\UserLike;
use App\Utilities\ArrUtility;

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
            case 'like':
                $interactionQuery = UserLike::markType(UserLike::MARK_TYPE_LIKE)->where('like_id', $markId);
                break;

            case 'dislike':
                $interactionQuery = UserLike::markType(UserLike::MARK_TYPE_DISLIKE)->where('like_id', $markId);
                break;

            case 'follow':
                $interactionQuery = UserFollow::where('follow_id', $markId);
                break;

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

            $paginateData[] = $service->userData($interaction->creator, 'list', $langTag, $timezone, $authUserId);
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
            case 'like':
                $markQuery = UserLike::markType(UserLike::MARK_TYPE_LIKE);
                break;

            case 'dislike':
                $markQuery = UserLike::markType(UserLike::MARK_TYPE_DISLIKE);
                break;

            case 'follow':
                $markQuery = UserFollow::query();
                break;

            case 'block':
                $markQuery = UserBlock::query();
                break;
        }

        $markType = match ($markTypeName) {
            'user' => InteractionService::TYPE_USER,
            'group' => InteractionService::TYPE_GROUP,
            'hashtag' => InteractionService::TYPE_HASHTAG,
            'post' => InteractionService::TYPE_POST,
            'comment' => InteractionService::TYPE_COMMENT,

            'users' => InteractionService::TYPE_USER,
            'groups' => InteractionService::TYPE_GROUP,
            'hashtags' => InteractionService::TYPE_HASHTAG,
            'posts' => InteractionService::TYPE_POST,
            'comments' => InteractionService::TYPE_COMMENT,
        };

        $markData = $markQuery->with('user')
            ->where('user_id', $userId)
            ->type($markType)
            ->orderBy('created_at', $orderDirection)
            ->paginate(\request()->get('pageSize', 15));

        // filter
        $filterKeys = \request()->get('whitelistKeys') ?? \request()->get('blacklistKeys');
        $filter = [
            'type' => \request()->get('whitelistKeys') ? 'whitelist' : 'blacklist',
            'keys' => array_filter(explode(',', $filterKeys)),
        ];

        // data
        $paginateData = [];

        switch ($markTypeName) {
            case 'users':
                $service = new UserService();
                foreach ($markData as $mark) {
                    if (empty($mark->user)) {
                        continue;
                    }

                    $itemData = $service->userData($mark->user, 'list', $langTag, $timezone, $authUserId);

                    if ($filter['keys']) {
                        $itemData = ArrUtility::filter($itemData, $filter['type'], $filter['keys']);
                    }

                    $paginateData[] = $itemData;
                }
                break;

            case 'groups':
                $service = new GroupService();
                foreach ($markData as $mark) {
                    if (empty($mark->group)) {
                        continue;
                    }

                    $itemData = $service->groupData($mark->group, $langTag, $timezone, $authUserId);

                    if ($filter['keys']) {
                        $itemData = ArrUtility::filter($itemData, $filter['type'], $filter['keys']);
                    }

                    $paginateData[] = $itemData;
                }
                break;

            case 'hashtags':
                $service = new HashtagService();
                foreach ($markData as $mark) {
                    if (empty($mark->hashtag)) {
                        continue;
                    }

                    $itemData = $service->hashtagData($mark->hashtag, $langTag, $timezone, $authUserId);

                    if ($filter['keys']) {
                        $itemData = ArrUtility::filter($itemData, $filter['type'], $filter['keys']);
                    }

                    $paginateData[] = $itemData;
                }
                break;

            case 'posts':
                $service = new PostService();
                foreach ($markData as $mark) {
                    if (empty($mark->post)) {
                        continue;
                    }

                    $itemData = $service->postData($mark->post, 'list', $langTag, $timezone, false, $authUserId);

                    if ($filter['keys']) {
                        $itemData = ArrUtility::filter($itemData, $filter['type'], $filter['keys']);
                    }

                    $paginateData[] = $itemData;
                }
                break;

            case 'comments':
                $service = new CommentService();
                foreach ($markData as $mark) {
                    if (empty($mark->comment)) {
                        continue;
                    }

                    $itemData = $service->commentData($mark->comment, 'list', $langTag, $timezone, true, $authUserId);

                    if ($filter['keys']) {
                        $itemData = ArrUtility::filter($itemData, $filter['type'], $filter['keys']);
                    }

                    $paginateData[] = $itemData;
                }
                break;
        }

        return [
            'paginateData' => $paginateData,
            'markData' => $markData,
        ];
    }
}
