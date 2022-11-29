<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\FileHelper;
use App\Models\Comment;
use App\Models\Post;
use App\Utilities\InteractiveUtility;

class FollowService
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
        $userPostQuery = Post::with(['hashtags'])->whereIn('user_id', $followUserIds)->where('is_anonymous', 0)->isEnable()->latest();
        // follow group post
        $groupPostQuery = Post::with(['hashtags'])->whereIn('group_id', $followGroupIds)->where('digest_state', 2)->isEnable()->latest();
        // follow hashtag post
        $hashtagPostQuery = Post::with(['hashtags'])->where('digest_state', 2)->isEnable()->latest();
        $hashtagPostQuery->whereHas('hashtags', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtags.id', $followHashtagIds);
        });
        // digest post query
        $digestPostQuery = Post::with(['hashtags'])->where('digest_state', 3)->latest();

        // block post
        if ($blockPostIds) {
            $userPostQuery->whereNotIn('id', $blockPostIds);
            $groupPostQuery->whereNotIn('id', $blockPostIds);
            $hashtagPostQuery->whereNotIn('id', $blockPostIds);
            $digestPostQuery->whereNotIn('id', $blockPostIds);
        }
        // block user
        if ($blockUserIds) {
            $groupPostQuery->whereNotIn('user_id', $blockUserIds);
            $hashtagPostQuery->whereNotIn('user_id', $blockUserIds);
            $digestPostQuery->whereNotIn('user_id', $blockUserIds);
        }
        // block group
        if ($blockGroupIds) {
            $userPostQuery->whereNotIn('group_id', $blockGroupIds);
            $hashtagPostQuery->whereNotIn('group_id', $blockGroupIds);
            $digestPostQuery->whereNotIn('group_id', $blockGroupIds);
        }
        // block hashtag
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

        // content type filter
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

        // time condition
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

        $postQuery = Post::with(['hashtags'])->whereIn('user_id', $followUserIds)->where('is_anonymous', 0)->isEnable()->latest();

        // block post
        $postQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // block group
        $postQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereNotIn('group_id', $value);
        });

        // block hashtag
        if ($blockHashtagIds) {
            $postQuery->where(function ($postQuery) use ($blockHashtagIds) {
                $postQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
        }

        // content type filter
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

        // time condition
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

        $postQuery = Post::with(['hashtags'])->whereIn('group_id', $followGroupIds)->isEnable()->latest();

        // block post
        $postQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // block user
        $postQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        // block hashtag
        if ($blockHashtagIds) {
            $postQuery->where(function ($postQuery) use ($blockHashtagIds) {
                $postQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
        }

        // content type filter
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

        // time condition
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

        $postQuery = Post::with(['hashtags'])->isEnable()->latest();

        // follow hashtags
        $postQuery->whereHas('hashtags', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtag_id', $followHashtagIds);
        });

        // block post
        $postQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // block user
        $postQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        // block group
        $postQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereNotIn('group_id', $value);
        });

        // content type filter
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

        // time condition
        if ($dateLimit) {
            $postQuery->where('created_at', '<=', $dateLimit);
        }

        $posts = $postQuery->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    // get comment list by follow all
    public function getCommentListByFollowAll(int $authUserId, ?string $contentType = null, ?string $dateLimit = null)
    {
        $followUserIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_USER, $authUserId);
        $followGroupIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);
        $followHashtagIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

        $blockCommentIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_COMMENT, $authUserId);
        $blockPostIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_POST, $authUserId);
        $blockUserIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_USER, $authUserId);
        $blockGroupIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);
        $blockHashtagIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

        // follow user post
        $userCommentQuery = Comment::with(['post', 'hashtags'])->whereIn('user_id', $followUserIds)->where('is_anonymous', 0)->isEnable()->latest();
        // follow group post
        $groupCommentQuery = Comment::with(['post', 'hashtags'])->where('digest_state', 2)->isEnable()->latest();
        $groupCommentQuery->whereHas('post', function ($query) use ($followGroupIds) {
            $query->whereIn('group_id', $followGroupIds);
        });
        // follow hashtag post
        $hashtagCommentQuery = Comment::with(['post', 'hashtags'])->where('digest_state', 2)->isEnable()->latest();
        $hashtagCommentQuery->whereHas('hashtags', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtags.id', $followHashtagIds);
        });
        // digest post query
        $digestCommentQuery = Comment::with(['post', 'hashtags'])->where('digest_state', 3)->latest();

        // block comment
        if ($blockCommentIds) {
            $userCommentQuery->whereNotIn('id', $blockCommentIds);
            $groupCommentQuery->whereNotIn('id', $blockCommentIds);
            $hashtagCommentQuery->whereNotIn('id', $blockCommentIds);
            $digestCommentQuery->whereNotIn('id', $blockCommentIds);
        }
        // block post
        if ($blockPostIds) {
            $userCommentQuery->whereNotIn('post_id', $blockPostIds);
            $groupCommentQuery->whereNotIn('post_id', $blockPostIds);
            $hashtagCommentQuery->whereNotIn('post_id', $blockPostIds);
            $digestCommentQuery->whereNotIn('post_id', $blockPostIds);
        }
        // block user
        if ($blockUserIds) {
            $groupCommentQuery->whereNotIn('user_id', $blockUserIds);
            $hashtagCommentQuery->whereNotIn('user_id', $blockUserIds);
            $digestCommentQuery->whereNotIn('user_id', $blockUserIds);
        }
        // block group
        if ($blockGroupIds) {
            $userCommentQuery->when($blockGroupIds, function ($query, $value) {
                $query->whereHas('post', function ($query) use ($value) {
                    $query->whereNotIn('group_id', $value);
                });
            });
            $hashtagCommentQuery->when($blockGroupIds, function ($query, $value) {
                $query->whereHas('post', function ($query) use ($value) {
                    $query->whereNotIn('group_id', $value);
                });
            });
            $digestCommentQuery->when($blockGroupIds, function ($query, $value) {
                $query->whereHas('post', function ($query) use ($value) {
                    $query->whereNotIn('group_id', $value);
                });
            });
        }
        // block hashtag
        if ($blockHashtagIds) {
            $userCommentQuery->where(function ($postQuery) use ($blockHashtagIds) {
                $postQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
            $groupCommentQuery->where(function ($postQuery) use ($blockHashtagIds) {
                $postQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
            $digestCommentQuery->where(function ($postQuery) use ($blockHashtagIds) {
                $postQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
        }

        // content type filter
        if ($contentType && $contentType != 'all') {
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($contentType == 'text') {
                $userCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $groupCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $hashtagCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $digestCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } elseif ($fileTypeNumber) {
                $userCommentQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
                $groupCommentQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
                $hashtagCommentQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
                $digestCommentQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
            } else {
                $userCommentQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
                $groupCommentQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
                $hashtagCommentQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
                $digestCommentQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
            }
        }

        // time condition
        if ($dateLimit) {
            $userCommentQuery->where('created_at', '<=', $dateLimit);
            $groupCommentQuery->where('created_at', '<=', $dateLimit);
            $hashtagCommentQuery->where('created_at', '<=', $dateLimit);
            $digestCommentQuery->where('created_at', '<=', $dateLimit);
        }

        $comments = $userCommentQuery->union($groupCommentQuery)->union($hashtagCommentQuery)->union($digestCommentQuery)->latest()->paginate(\request()->get('pageSize', 15));

        return $comments;
    }

    // get comment list by follow users
    public function getCommentListByFollowUsers(int $authUserId, ?string $contentType = null, ?string $dateLimit = null)
    {
        $followUserIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_USER, $authUserId);

        $blockCommentIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_COMMENT, $authUserId);
        $blockPostIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_POST, $authUserId);
        $blockGroupIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);
        $blockHashtagIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

        $commentQuery = Comment::with(['post', 'hashtags'])->whereIn('user_id', $followUserIds)->where('is_anonymous', 0)->isEnable()->latest();

        // block comment
        $commentQuery->when($blockCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // block post
        $commentQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('post_id', $value);
        });

        // block group
        $commentQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereHas('post', function ($query) use ($value) {
                $query->whereNotIn('group_id', $value);
            });
        });

        // block hashtag
        if ($blockHashtagIds) {
            $commentQuery->where(function ($commentQuery) use ($blockHashtagIds) {
                $commentQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
        }

        // content type filter
        if ($contentType && $contentType != 'all') {
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($contentType == 'text') {
                $commentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } elseif ($fileTypeNumber) {
                $commentQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
            } else {
                $commentQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
            }
        }

        // time condition
        if ($dateLimit) {
            $commentQuery->where('created_at', '<=', $dateLimit);
        }

        $comments = $commentQuery->paginate(\request()->get('pageSize', 15));

        return $comments;
    }

    // get comment list by follow groups
    public function getCommentListByFollowGroups(int $authUserId, ?string $contentType = null, ?string $dateLimit = null)
    {
        $followGroupIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);

        $blockCommentIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_COMMENT, $authUserId);
        $blockPostIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_POST, $authUserId);
        $blockUserIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_USER, $authUserId);
        $blockHashtagIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

        $commentQuery = Comment::with(['post', 'hashtags'])->where('top_parent_id', 0)->isEnable()->latest();

        $commentQuery->when($followGroupIds, function ($query, $value) {
            $query->whereHas('post', function ($query) use ($value) {
                $query->whereIn('group_id', $value);
            });
        });

        // block comment
        $commentQuery->when($blockCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // block post
        $commentQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('post_id', $value);
        });

        // block user
        $commentQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        // block hashtag
        if ($blockHashtagIds) {
            $commentQuery->where(function ($commentQuery) use ($blockHashtagIds) {
                $commentQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
        }

        // content type filter
        if ($contentType && $contentType != 'all') {
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($contentType == 'text') {
                $commentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } elseif ($fileTypeNumber) {
                $commentQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
            } else {
                $commentQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
            }
        }

        // time condition
        if ($dateLimit) {
            $commentQuery->where('created_at', '<=', $dateLimit);
        }

        $comments = $commentQuery->paginate(\request()->get('pageSize', 15));

        return $comments;
    }

    // get comment list by follow hashtags
    public function getCommentListByFollowHashtags(int $authUserId, ?string $contentType = null, ?string $dateLimit = null)
    {
        $followHashtagIds = InteractiveUtility::getFollowIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

        $blockCommentIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_COMMENT, $authUserId);
        $blockPostIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_POST, $authUserId);
        $blockUserIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_USER, $authUserId);
        $blockGroupIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);

        $commentQuery = Comment::with(['post', 'hashtags'])->where('top_parent_id', 0)->isEnable()->latest();

        $commentQuery->whereHas('hashtags', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtag_id', $followHashtagIds);
        });

        // block comment
        $commentQuery->when($blockCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // block post
        $commentQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('post_id', $value);
        });

        // block user
        $commentQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        // block group
        $commentQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereHas('post', function ($query) use ($value) {
                $query->whereNotIn('group_id', $value);
            });
        });

        // content type filter
        if ($contentType && $contentType != 'all') {
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($contentType == 'text') {
                $commentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } elseif ($fileTypeNumber) {
                $commentQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
            } else {
                $commentQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_unikey', $contentType);
                });
            }
        }

        // time condition
        if ($dateLimit) {
            $commentQuery->where('created_at', '<=', $dateLimit);
        }

        $comments = $commentQuery->paginate(\request()->get('pageSize', 15));

        return $comments;
    }
}
