<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Models\Post;
use App\Models\UserBlock;
use App\Models\UserFollow;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Arr;

class PostFollowService
{
    // get post list by follow all
    public function getPostListByFollowAll(int $authUserId, ?string $contentType = null, ?string $dateLimit = null)
    {
        $followUserIds = UserFollow::type(UserFollow::TYPE_USER)->where('user_id', $authUserId)->pluck('follow_id')->toArray();
        $allUserIds = Arr::prepend($followUserIds, $authUserId);
        $followGroupIds = UserFollow::type(UserFollow::TYPE_GROUP)->where('user_id', $authUserId)->pluck('follow_id')->toArray();
        $followHashtagIds = UserFollow::type(UserFollow::TYPE_HASHTAG)->where('user_id', $authUserId)->pluck('follow_id')->toArray();

        $blockUserIds = UserBlock::type(UserBlock::TYPE_USER)->where('user_id', $authUserId)->pluck('block_id')->toArray();
        $blockGroupIds = UserBlock::type(UserBlock::TYPE_GROUP)->where('user_id', $authUserId)->pluck('block_id')->toArray();
        $blockHashtagIds = UserBlock::type(UserBlock::TYPE_HASHTAG)->where('user_id', $authUserId)->pluck('block_id')->toArray();
        $blockPostIds = UserBlock::type(UserBlock::TYPE_POST)->where('user_id', $authUserId)->pluck('block_id')->toArray();

        $filterGroupIds = PermissionUtility::getGroupPostFilterIds($authUserId);
        $filterGroupIdsArr = Arr::prepend($blockGroupIds, $filterGroupIds);

        // follow user post
        $userPostQuery = Post::with('hashtags')
            ->whereIn('user_id', $allUserIds)
            ->where(function ($query) use ($blockPostIds, $filterGroupIdsArr) {
                $query
                    ->whereNotIn('id', $blockPostIds)
                    ->orWhereNotIn('group_id', $filterGroupIdsArr);
            })
            ->isEnable()
            ->latest();
        $userPostQuery->whereHas('hashtags', function ($query) use ($blockHashtagIds) {
            $query->whereNotIn('id', $blockHashtagIds);
        });

        // follow group post
        $groupPostQuery = Post::with('hashtags')
            ->where(function ($query) use ($blockPostIds, $allUserIds, $blockUserIds) {
                $uniqueFilterUserIds = array_unique(array_merge($allUserIds, $blockUserIds));

                $query
                    ->whereNotIn('id', $blockPostIds)
                    ->orWhereNotIn('user_id', $uniqueFilterUserIds);
            })
            ->whereIn('group_id', $followGroupIds)
            ->whereIn('digest_state', [2, 3])
            ->isEnable()
            ->latest();
        $groupPostQuery->whereHas('hashtags', function ($query) use ($blockHashtagIds) {
            $query->whereNotIn('id', $blockHashtagIds);
        });

        // follow hashtag post
        $hashtagPostQuery = Post::with('hashtags')
            ->where(function ($query) use ($blockPostIds, $allUserIds, $blockUserIds, $followGroupIds, $filterGroupIdsArr) {
                $uniqueFilterUserIds = array_unique(array_merge($allUserIds, $blockUserIds));
                $uniqueFilterGroupIds = array_unique(array_merge($followGroupIds, $filterGroupIdsArr));

                $query
                    ->whereNotIn('id', $blockPostIds)
                    ->orWhereNotIn('user_id', $uniqueFilterUserIds)
                    ->orWhereNotIn('group_id', $uniqueFilterGroupIds);
            })
            ->whereIn('digest_state', [2, 3])
            ->isEnable()
            ->latest();
        $hashtagPostQuery->whereHas('hashtags', function ($query) use ($followHashtagIds) {
            $query->whereIn('id', $followHashtagIds);
        });

        // digest post query
        $digestPostQuery = Post::with('hashtags')
            ->where(function ($query) use ($blockPostIds, $allUserIds, $followGroupIds, $filterGroupIdsArr) {
                $uniqueFilterGroupIds = array_unique(array_merge($followGroupIds, $filterGroupIdsArr));

                $query
                    ->whereNotIn('id', $blockPostIds)
                    ->orWhereNotIn('user_id', $allUserIds)
                    ->orWhereNotIn('group_id', $uniqueFilterGroupIds);
            })
            ->where('digest_state', 3)
            ->latest();
        $digestPostQuery->whereHas('hashtags', function ($query) use ($followHashtagIds) {
            $query->whereNotIn('id', $followHashtagIds);
        });

        if (! empty($contentType)) {
            $userPostQuery->where('types', 'like', "%$contentType%");
            $groupPostQuery->where('types', 'like', "%$contentType%");
            $hashtagPostQuery->where('types', 'like', "%$contentType%");
            $digestPostQuery->where('types', 'like', "%$contentType%");
        }

        if (! empty($dateLimit)) {
            $userPostQuery->where('created_at', '<=', $dateLimit);
            $groupPostQuery->where('created_at', '<=', $dateLimit);
            $hashtagPostQuery->where('created_at', '<=', $dateLimit);
            $digestPostQuery->where('created_at', '<=', $dateLimit);
        }

        $posts = $userPostQuery
            ->union($groupPostQuery)
            ->union($hashtagPostQuery)
            ->union($digestPostQuery)
            ->latest()
            ->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    // get post list by follow users
    public function getPostListByFollowUsers(int $authUserId, ?string $contentType = null, ?string $dateLimit = null)
    {
        $followUserIds = UserFollow::type(UserFollow::TYPE_USER)->where('user_id', $authUserId)->pluck('follow_id')->toArray();
        $allUserIds = Arr::prepend($followUserIds, $authUserId);
        $filterGroupIds = PermissionUtility::getGroupPostFilterIds($authUserId);
        $blockPostIds = UserBlock::type(UserBlock::TYPE_POST)->where('user_id', $authUserId)->pluck('block_id')->toArray();

        $postQuery = Post::whereIn('user_id', $allUserIds)
            ->whereNotIn('id', $blockPostIds)
            ->orWhereNotIn('group_id', $filterGroupIds)
            ->where('is_anonymous', 0)
            ->isEnable()
            ->latest();

        if (! empty($contentType)) {
            $postQuery->where('types', 'like', "%$contentType%");
        }

        if (! empty($dateLimit)) {
            $postQuery->where('created_at', '<=', $dateLimit);
        }

        $posts = $postQuery->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    // get post list by follow groups
    public function getPostListByFollowGroups(int $authUserId, ?string $contentType = null, ?string $dateLimit = null)
    {
        $followGroupIds = UserFollow::type(UserFollow::TYPE_GROUP)->where('user_id', $authUserId)->pluck('follow_id')->toArray();
        $blockUserIds = UserBlock::type(UserBlock::TYPE_USER)->where('user_id', $authUserId)->pluck('block_id')->toArray();
        $blockHashtagIds = UserBlock::type(UserBlock::TYPE_HASHTAG)->where('user_id', $authUserId)->pluck('block_id')->toArray();
        $blockPostIds = UserBlock::type(UserBlock::TYPE_POST)->where('user_id', $authUserId)->pluck('block_id')->toArray();

        $postQuery = Post::whereIn('group_id', $followGroupIds)
            ->where(function ($query) use ($blockPostIds, $blockUserIds) {
                $query
                    ->whereNotIn('id', $blockPostIds)
                    ->orWhereNotIn('user_id', $blockUserIds);
            })
            ->isEnable()
            ->latest();
        $postQuery->whereHas('hashtags', function ($query) use ($blockHashtagIds) {
            $query->whereNotIn('id', $blockHashtagIds);
        });

        if (! empty($contentType)) {
            $postQuery->where('types', 'like', "%$contentType%");
        }

        if (! empty($dateLimit)) {
            $postQuery->where('created_at', '<=', $dateLimit);
        }

        $posts = $postQuery->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    // get post list by follow hashtags
    public function getPostListByFollowHashtags(int $authUserId, ?string $contentType = null, ?string $dateLimit = null)
    {
        $followHashtagIds = UserFollow::type(UserFollow::TYPE_HASHTAG)->where('user_id', $authUserId)->pluck('follow_id')->toArray();
        $blockUserIds = UserBlock::type(UserBlock::TYPE_USER)->where('user_id', $authUserId)->pluck('block_id')->toArray();
        $blockGroupIds = UserBlock::type(UserBlock::TYPE_GROUP)->where('user_id', $authUserId)->pluck('block_id')->toArray();
        $modeGroupIds = PermissionUtility::getGroupPostFilterIds($authUserId);
        $filterGroupIds = Arr::prepend($blockGroupIds, $modeGroupIds);
        $blockPostIds = UserBlock::type(UserBlock::TYPE_POST)->where('user_id', $authUserId)->pluck('block_id')->toArray();

        $postQuery = Post::with('hashtags')
            ->where(function ($query) use ($blockPostIds, $blockUserIds, $filterGroupIds) {
                $query
                    ->whereNotIn('id', $blockPostIds)
                    ->orWhereNotIn('user_id', $blockUserIds)
                    ->orWhereNotIn('group_id', $filterGroupIds);
            })
            ->isEnable()
            ->latest();
        $postQuery->whereHas('hashtags', function ($query) use ($followHashtagIds) {
            $query->whereIn('id', $followHashtagIds);
        });

        if (! empty($contentType)) {
            $postQuery->where('types', 'like', "%$contentType%");
        }

        if (! empty($dateLimit)) {
            $postQuery->where('created_at', '<=', $dateLimit);
        }

        $posts = $postQuery->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    // check follow type
    public static function checkFollowType(int $creatorId, ?int $groupId = null, ?array $hashtagIds = null, ?int $authUserId = null): bool
    {
        if (empty($authUserId)) {
            return null;
        }

        $checkFollowUser = UserFollow::where('user_id', $authUserId)
            ->type(UserFollow::TYPE_USER)
            ->where('follow_id', $creatorId)
            ->first();

        if ($checkFollowUser) {
            return 'user';
        }

        if (! empty($groupId)) {
            $checkFollowGroup = UserFollow::where('user_id', $authUserId)
                ->type(UserFollow::TYPE_GROUP)
                ->where('follow_id', $groupId)
                ->first();

            if ($checkFollowGroup) {
                return 'group';
            }
        }

        if (! empty($hashtagIds)) {
            $checkFollowHashtag = UserFollow::where('user_id', $authUserId)
                ->type(UserFollow::TYPE_HASHTAG)
                ->whereIn('follow_id', $hashtagIds)
                ->first();

            if ($checkFollowHashtag) {
                return 'hashtag';
            }
        }

        return 'digest';
    }
}
