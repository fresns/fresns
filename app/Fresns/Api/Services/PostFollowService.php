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
        $blockHashtagIds = UserBlock::type(UserBlock::TYPE_HASHTAG)->where('user_id', $authUserId)->pluck('block_id')->toArray();
        $blockPostIds = UserBlock::type(UserBlock::TYPE_POST)->where('user_id', $authUserId)->pluck('block_id')->toArray();

        $filterGroupIdsArr = PermissionUtility::getPostFilterByGroupIds($authUserId);

        // follow user post
        $userPostQuery = Post::with(['creator', 'group', 'hashtags'])
            ->whereIn('user_id', $allUserIds)
            ->where(function ($query) use ($blockPostIds, $filterGroupIdsArr) {
                $query
                    ->whereNotIn('id', $blockPostIds)
                    ->orWhereNotIn('group_id', $filterGroupIdsArr);
            })
            ->isEnable()
            ->latest();

        if ($blockHashtagIds) {
            $userPostQuery->whereHas('hashtags', function ($query) use ($blockHashtagIds) {
                $query->whereNotIn('hashtag_id', $blockHashtagIds);
            });
        }

        // follow group post
        $groupPostQuery = Post::with(['creator', 'group', 'hashtags'])
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

        if ($blockHashtagIds) {
            $groupPostQuery->whereHas('hashtags', function ($query) use ($blockHashtagIds) {
                $query->whereNotIn('hashtag_id', $blockHashtagIds);
            });
        }

        // follow hashtag post
        $hashtagPostQuery = Post::with(['creator', 'group', 'hashtags'])
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
            $query->whereIn('hashtags.id', $followHashtagIds);
        });

        // digest post query
        $digestPostQuery = Post::with(['creator', 'group', 'hashtags'])
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
            $query->whereNotIn('hashtag_id', $followHashtagIds);
        });

        if ($contentType && $contentType != 'all') {
            if ($contentType == 'text') {
                $userPostQuery->whereNull('types');
                $groupPostQuery->whereNull('types');
                $hashtagPostQuery->whereNull('types');
                $digestPostQuery->whereNull('types');
            } else {
                $userPostQuery->where('types', 'like', "%$contentType%");
                $groupPostQuery->where('types', 'like', "%$contentType%");
                $hashtagPostQuery->where('types', 'like', "%$contentType%");
                $digestPostQuery->where('types', 'like', "%$contentType%");
            }
        }

        if ($dateLimit) {
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
        $filterGroupIds = PermissionUtility::getPostFilterByGroupIds($authUserId);
        $blockPostIds = UserBlock::type(UserBlock::TYPE_POST)->where('user_id', $authUserId)->pluck('block_id')->toArray();

        $postQuery = Post::with(['creator', 'group', 'hashtags'])
            ->whereIn('user_id', $allUserIds)
            ->whereNotIn('id', $blockPostIds)
            ->orWhereNotIn('group_id', $filterGroupIds)
            ->where('is_anonymous', 0)
            ->isEnable()
            ->latest();

        if ($contentType && $contentType != 'all') {
            if ($contentType == 'text') {
                $postQuery->whereNull('types');
            } else {
                $postQuery->where('types', 'like', "%$contentType%");
            }
        }

        if ($dateLimit) {
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

        $postQuery = Post::with(['creator', 'group', 'hashtags'])
            ->whereIn('group_id', $followGroupIds)
            ->where(function ($query) use ($blockPostIds, $blockUserIds) {
                $query
                    ->whereNotIn('id', $blockPostIds)
                    ->orWhereNotIn('user_id', $blockUserIds);
            })
            ->isEnable()
            ->latest();

        if ($blockHashtagIds) {
            $postQuery->whereHas('hashtags', function ($query) use ($blockHashtagIds) {
                $query->whereNotIn('hashtag_id', $blockHashtagIds);
            });
        }

        if ($contentType && $contentType != 'all') {
            if ($contentType == 'text') {
                $postQuery->whereNull('types');
            } else {
                $postQuery->where('types', 'like', "%$contentType%");
            }
        }

        if ($dateLimit) {
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
        $filterGroupIds = PermissionUtility::getPostFilterByGroupIds($authUserId);
        $blockPostIds = UserBlock::type(UserBlock::TYPE_POST)->where('user_id', $authUserId)->pluck('block_id')->toArray();

        $postQuery = Post::with(['creator', 'group', 'hashtags'])
            ->where(function ($query) use ($blockPostIds, $blockUserIds, $filterGroupIds) {
                $query
                    ->whereNotIn('id', $blockPostIds)
                    ->orWhereNotIn('user_id', $blockUserIds)
                    ->orWhereNotIn('group_id', $filterGroupIds);
            })
            ->isEnable()
            ->latest();

        $postQuery->whereHas('hashtags', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtag_id', $followHashtagIds);
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

    // get follow type
    public function getFollowType(int $creatorId, ?int $groupId = null, ?array $hashtags = null, ?int $authUserId = null): string
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

        if (! empty($hashtags)) {
            $hashtagIds = array_column($hashtags, 'id');

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
