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

class InteractiveService
{
    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_POST = 4;
    const TYPE_COMMENT = 5;

    // check interactive setting
    public static function checkInteractiveSetting(string $interactiveType, string $markType)
    {
        $setKey = match ($interactiveType) {
            'like' => "{$markType}_likers",
            'dislike' => "{$markType}_dislikers",
            'follow' => "{$markType}_followers",
            'block' => "{$markType}_blockers",
        };

        $interactiveSet = ConfigHelper::fresnsConfigByItemKey($setKey);
        if (! $interactiveSet) {
            throw new ApiException(36201);
        }
    }

    // check my interactive setting
    public static function checkMyInteractiveSetting(string $interactiveType, string $markType)
    {
        $setKey = match ($interactiveType) {
            'like' => "{$markType}_likers",
            'dislike' => "{$markType}_dislikers",
            'follow' => "{$markType}_followers",
            'block' => "{$markType}_blockers",
        };

        $interactiveSet = ConfigHelper::fresnsConfigByItemKey($setKey);
        if ($interactiveSet) {
            return;
        }

        $mySetKey = match ($interactiveType) {
            'like' => 'my_likers',
            'dislike' => 'my_dislikers',
            'follow' => 'my_followers',
            'block' => 'my_blockers',
        };

        $myInteractiveSet = ConfigHelper::fresnsConfigByItemKey($mySetKey);
        if (! $myInteractiveSet) {
            throw new ApiException(36201);
        }
    }

    // get the users who marked it
    public function getUsersWhoMarkIt(string $getType, string $markType, int $markId, string $orderDirection, string $langTag, string $timezone, ?string $authUserId = null)
    {
        switch ($getType) {
                // like
            case 'like':
                $interactiveQuery = UserLike::markType(UserLike::MARK_TYPE_LIKE)->where('like_id', $markId);
                break;

                // dislike
            case 'dislike':
                $interactiveQuery = UserLike::markType(UserLike::MARK_TYPE_DISLIKE)->where('like_id', $markId);
                break;

                // follow
            case 'follow':
                $interactiveQuery = UserFollow::where('follow_id', $markId);
                break;

                // block
            case 'block':
                $interactiveQuery = UserBlock::where('block_id', $markId);
                break;
        }

        $interactiveData = $interactiveQuery->with('creator')
            ->type($markType)
            ->orderBy('created_at', $orderDirection)
            ->paginate(\request()->get('pageSize', 15));

        $service = new UserService();

        $paginateData = [];
        foreach ($interactiveData as $interactive) {
            $paginateData[] = $service->userList($interactive->creator, $langTag, $timezone, $authUserId);
        }

        return [
            'paginateData' => $paginateData,
            'interactiveData' => $interactiveData,
        ];
    }

    // get a list of the content it marks
    public function getItMarkList(string $getType, string $markTypeName, int $userId, string $orderDirection, string $langTag, string $timezone, ?string $authUserId = null)
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
                    $paginateData[] = $service->userList($mark->user, $langTag, $timezone, $authUserId);
                }
            break;

            // groups
            case 'groups':
                $service = new GroupService();
                foreach ($markData as $mark) {
                    $paginateData[] = $service->groupList($mark->group, $langTag, $timezone, $authUserId);
                }
            break;

            // hashtags
            case 'hashtags':
                $service = new HashtagService();
                foreach ($markData as $mark) {
                    $paginateData[] = $service->hashtagList($mark->hashtag, $langTag, $timezone, $authUserId);
                }
            break;

            // posts
            case 'posts':
                $service = new PostService();
                foreach ($markData as $mark) {
                    $paginateData[] = $service->postDetail($mark->post, 'list', $langTag, $timezone, $authUserId);
                }
            break;

            // comments
            case 'comments':
                $service = new CommentService();
                foreach ($markData as $mark) {
                    $paginateData[] = $service->commentDetail($mark->comment, 'list', $langTag, $timezone, $authUserId);
                }
            break;
        }

        return [
            'paginateData' => $paginateData,
            'markData' => $markData,
        ];
    }
}
