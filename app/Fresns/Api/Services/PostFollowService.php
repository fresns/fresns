<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\FileHelper;
use App\Models\Post;
use App\Models\UserFollow;
use App\Utilities\InteractiveUtility;

class PostFollowService
{
    // get post list by follow all
    public function getPostListByFollowAll(int $authUserId, ?string $contentType = null, ?string $dateLimit = null)
    {
        $followUserIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_USER, $authUserId);
        $followGroupIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);
        $followHashtagIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

        $blockPostIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_POST, $authUserId);
        $blockUserIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_USER, $authUserId);
        $blockGroupIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);
        $blockHashtagIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

        // follow user post
        $userPostQuery = Post::with(['creator', 'group', 'hashtags'])->whereIn('user_id', $followUserIds)->where('is_anonymous', 0)->isEnable()->latest();
        // follow group post
        $groupPostQuery = Post::with(['creator', 'group', 'hashtags'])->whereIn('group_id', $followGroupIds)->where('digest_state', 2)->isEnable()->latest();
        // follow hashtag post
        $hashtagPostQuery = Post::with(['creator', 'group', 'hashtags'])->where('digest_state', 2)->isEnable()->latest();
        $hashtagPostQuery->whereHas('hashtags', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtags.id', $followHashtagIds);
        });
        // digest post query
        $digestPostQuery = Post::with(['creator', 'group', 'hashtags'])->where('digest_state', 3)->latest();

        // block
        if ($blockPostIds) {
            $userPostQuery->whereNotIn('id', $blockPostIds);
            $groupPostQuery->whereNotIn('id', $blockPostIds);
            $hashtagPostQuery->whereNotIn('id', $blockPostIds);
            $digestPostQuery->whereNotIn('id', $blockPostIds);
        }
        if ($blockUserIds) {
            $userPostQuery->whereNotIn('user_id', $blockUserIds);
            $groupPostQuery->whereNotIn('user_id', $blockUserIds);
            $hashtagPostQuery->whereNotIn('user_id', $blockUserIds);
            $digestPostQuery->whereNotIn('user_id', $blockUserIds);
        }
        if ($blockGroupIds) {
            $userPostQuery->whereNotIn('group_id', $blockGroupIds);
            $hashtagPostQuery->whereNotIn('group_id', $blockGroupIds);
            $digestPostQuery->whereNotIn('group_id', $blockGroupIds);
        }
        if ($blockHashtagIds) {
            $userPostQuery->where(function ($postQuery) use ($blockHashtagIds) {
                $postQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
            $groupPostQuery->where(function ($postQuery) use ($blockHashtagIds) {
                $postQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
            $digestPostQuery->where(function ($postQuery) use ($blockHashtagIds) {
                $postQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
        }

        // content type
        if ($contentType && $contentType != 'all') {
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($contentType == 'text') {
                $userPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $groupPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $hashtagPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $digestPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } elseif ($fileTypeNumber) {
                $userPostQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
                $groupPostQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
                $hashtagPostQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
                $digestPostQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
            } else {
                $userPostQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
                $groupPostQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
                $hashtagPostQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
                $digestPostQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
            }
        }

        // date limit
        if ($dateLimit) {
            $userPostQuery->where('created_at', '<=', $dateLimit);
            $groupPostQuery->where('created_at', '<=', $dateLimit);
            $hashtagPostQuery->where('created_at', '<=', $dateLimit);
            $digestPostQuery->where('created_at', '<=', $dateLimit);
        }

        $posts = $userPostQuery->union($groupPostQuery)->union($hashtagPostQuery)->union($digestPostQuery)->latest()->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    // get post list by follow users
    public function getPostListByFollowUsers(int $authUserId, ?string $contentType = null, ?string $dateLimit = null)
    {
        $followUserIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_USER, $authUserId);

        $blockPostIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_POST, $authUserId);
        $blockGroupIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);
        $blockHashtagIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

        $postQuery = Post::with(['creator', 'group', 'hashtags'])->whereIn('user_id', $followUserIds)->where('is_anonymous', 0)->isEnable()->latest();

        $postQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        $postQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereNotIn('group_id', $value);
        });

        if ($blockHashtagIds) {
            $postQuery->where(function ($postQuery) use ($blockHashtagIds) {
                $postQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
        }

        if ($contentType && $contentType != 'all') {
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($contentType == 'text') {
                $postQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } elseif ($fileTypeNumber) {
                $postQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
            } else {
                $postQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
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
        $followGroupIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);

        $blockPostIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_POST, $authUserId);
        $blockUserIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_USER, $authUserId);
        $blockHashtagIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

        $postQuery = Post::with(['creator', 'group', 'hashtags'])->whereIn('group_id', $followGroupIds)->isEnable()->latest();

        $postQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        $postQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        if ($blockHashtagIds) {
            $postQuery->where(function ($postQuery) use ($blockHashtagIds) {
                $postQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
        }

        if ($contentType && $contentType != 'all') {
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($contentType == 'text') {
                $postQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } elseif ($fileTypeNumber) {
                $postQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
            } else {
                $postQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
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
        $followHashtagIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

        $blockPostIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_POST, $authUserId);
        $blockUserIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_USER, $authUserId);
        $blockGroupIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);

        $postQuery = Post::with(['creator', 'group', 'hashtags'])->isEnable()->latest();

        $postQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        $postQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        $postQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereNotIn('group_id', $value);
        });

        $postQuery->whereHas('hashtags', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtag_id', $followHashtagIds);
        });

        if ($contentType && $contentType != 'all') {
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($contentType == 'text') {
                $postQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } elseif ($fileTypeNumber) {
                $postQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
            } else {
                $postQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
            }
        }

        if ($dateLimit) {
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
