<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Comment;
use App\Models\Post;
use App\Utilities\InteractionUtility;

class TimelineService
{
    /**
     * Post Timelines.
     */
    // get post list by follow all
    public function getPostListByFollowAll(int $authUserId, ?string $contentType = null, ?string $sincePid = null, ?string $beforePid = null, ?string $dateLimit = null)
    {
        $followUserIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $followGroupIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GROUP, $authUserId);

        // follow user posts
        $userPostQuery = Post::whereIn('user_id', $followUserIds)->where('is_anonymous', false);
        // follow group posts
        $groupPostQuery = Post::whereIn('group_id', $followGroupIds)->where('digest_state', Post::DIGEST_GENERAL);
        // best digest posts
        $digestPostQuery = Post::where('digest_state', Post::DIGEST_PREMIUM);

        // is_enabled
        $userPostQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });
        $groupPostQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });
        $digestPostQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });

        // has author
        $userPostQuery->whereRelation('author', 'is_enabled', 1);
        $groupPostQuery->whereRelation('author', 'is_enabled', 1);
        $digestPostQuery->whereRelation('author', 'is_enabled', 1);

        // block
        $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $blockGeotagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);

        // block user
        if ($blockUserIds) {
            $groupPostQuery->whereNotIn('user_id', $blockUserIds);
            $digestPostQuery->whereNotIn('user_id', $blockUserIds);
        }
        // block group
        if ($blockGroupIds) {
            $userPostQuery->whereNotIn('group_id', $blockGroupIds);
            $digestPostQuery->whereNotIn('group_id', $blockGroupIds);
        }
        // block hashtag
        if ($blockHashtagIds) {
            $userPostQuery->when($blockHashtagIds, function ($query, $value) {
                $query->where(function ($postQuery) use ($value) {
                    $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                        $query->whereNotIn('hashtag_id', $value);
                    });
                });
            });
            $groupPostQuery->when($blockHashtagIds, function ($query, $value) {
                $query->where(function ($postQuery) use ($value) {
                    $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                        $query->whereNotIn('hashtag_id', $value);
                    });
                });
            });
            $digestPostQuery->when($blockHashtagIds, function ($query, $value) {
                $query->where(function ($postQuery) use ($value) {
                    $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                        $query->whereNotIn('hashtag_id', $value);
                    });
                });
            });
        }
        // block geotag
        if ($blockGeotagIds) {
            $userPostQuery->whereNotIn('geotag_id', $blockGeotagIds);
            $groupPostQuery->whereNotIn('geotag_id', $blockGeotagIds);
            $digestPostQuery->whereNotIn('geotag_id', $blockGeotagIds);
        }
        // block post
        if ($blockPostIds) {
            $userPostQuery->whereNotIn('id', $blockPostIds);
            $groupPostQuery->whereNotIn('id', $blockPostIds);
            $digestPostQuery->whereNotIn('id', $blockPostIds);
        }

        // since post
        $userPostQuery->when($sincePid, function ($query, $value) {
            $sincePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '>', $sincePostId);
        });
        $groupPostQuery->when($sincePid, function ($query, $value) {
            $sincePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '>', $sincePostId);
        });
        $digestPostQuery->when($sincePid, function ($query, $value) {
            $sincePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '>', $sincePostId);
        });

        // before post
        $userPostQuery->when($beforePid, function ($query, $value) {
            $beforePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '<', $beforePostId);
        });
        $groupPostQuery->when($beforePid, function ($query, $value) {
            $beforePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '<', $beforePostId);
        });
        $digestPostQuery->when($beforePid, function ($query, $value) {
            $beforePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '<', $beforePostId);
        });

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            $userPostQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });
            $groupPostQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });
            $digestPostQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($contentType == 'Text') {
                $userPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $groupPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $digestPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $userPostQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $groupPostQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $digestPostQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        $userPostQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });
        $groupPostQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });
        $digestPostQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        $posts = $userPostQuery->union($groupPostQuery)->union($digestPostQuery)->latest()->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    // get post list by follow users
    public function getPostListByFollowUsers(int $authUserId, ?string $contentType = null, ?string $sincePid = null, ?string $beforePid = null, ?string $dateLimit = null)
    {
        $postQuery = Post::query();

        $followUserIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $postQuery->whereIn('user_id', $followUserIds)->where('is_anonymous', false);

        // is_enabled
        $postQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });

        // has author
        $postQuery->whereRelation('author', 'is_enabled', 1);

        // block
        $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $blockGeotagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);

        $postQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereNotIn('group_id', $value);
        });

        $postQuery->when($blockHashtagIds, function ($query, $value) {
            $query->where(function ($postQuery) use ($value) {
                $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                    $query->whereNotIn('hashtag_id', $value);
                });
            });
        });

        $postQuery->when($blockGeotagIds, function ($query, $value) {
            $query->whereNotIn('geotag_id', $value);
        });

        $postQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since post
        $postQuery->when($sincePid, function ($query, $value) {
            $sincePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '>', $sincePostId);
        });

        // before post
        $postQuery->when($beforePid, function ($query, $value) {
            $beforePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '<', $beforePostId);
        });

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            $postQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($contentType == 'Text') {
                $postQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $postQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        $postQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        // posts
        $posts = $postQuery->latest()->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    // get post list by follow groups
    public function getPostListByFollowGroups(int $authUserId, ?string $contentType = null, ?string $sincePid = null, ?string $beforePid = null, ?string $dateLimit = null)
    {
        $postQuery = Post::query();

        $followGroupIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $postQuery->whereIn('group_id', $followGroupIds);

        // is_enabled
        $postQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });

        // has author
        $postQuery->whereRelation('author', 'is_enabled', 1);

        // block
        $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $blockGeotagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);

        $postQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        $postQuery->when($blockHashtagIds, function ($query, $value) {
            $query->where(function ($postQuery) use ($value) {
                $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                    $query->whereNotIn('hashtag_id', $value);
                });
            });
        });

        $postQuery->when($blockGeotagIds, function ($query, $value) {
            $query->whereNotIn('geotag_id', $value);
        });

        $postQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since post
        $postQuery->when($sincePid, function ($query, $value) {
            $sincePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '>', $sincePostId);
        });

        // before post
        $postQuery->when($beforePid, function ($query, $value) {
            $beforePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '<', $beforePostId);
        });

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            $postQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($contentType == 'Text') {
                $postQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $postQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        $postQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        $posts = $postQuery->latest()->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    // get post list by follow hashtags
    public function getPostListByFollowHashtags(int $authUserId, ?string $contentType = null, ?string $sincePid = null, ?string $beforePid = null, ?string $dateLimit = null)
    {
        $postQuery = Post::query();

        $followHashtagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $postQuery->whereHas('hashtagUsages', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtag_id', $followHashtagIds);
        });

        // is_enabled
        $postQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });

        // has author
        $postQuery->whereRelation('author', 'is_enabled', 1);

        // block
        $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $blockGeotagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);

        $postQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        $postQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereNotIn('group_id', $value);
        });

        $postQuery->when($blockGeotagIds, function ($query, $value) {
            $query->whereNotIn('geotag_id', $value);
        });

        $postQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since post
        $postQuery->when($sincePid, function ($query, $value) {
            $sincePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '>', $sincePostId);
        });

        // before post
        $postQuery->when($beforePid, function ($query, $value) {
            $beforePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '<', $beforePostId);
        });

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            $postQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($contentType == 'Text') {
                $postQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $postQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        $postQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        $posts = $postQuery->latest()->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    // get post list by follow geotags
    public function getPostListByFollowGeotags(int $authUserId, ?string $contentType = null, ?string $sincePid = null, ?string $beforePid = null, ?string $dateLimit = null)
    {
        $postQuery = Post::query();

        $followGeotagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $postQuery->whereIn('geotag_id', $followGeotagIds);

        // is_enabled
        $postQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });

        // has author
        $postQuery->whereRelation('author', 'is_enabled', 1);

        // block
        $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);

        $postQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        $postQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereNotIn('group_id', $value);
        });

        $postQuery->when($blockHashtagIds, function ($query, $value) {
            $query->where(function ($postQuery) use ($value) {
                $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                    $query->whereNotIn('hashtag_id', $value);
                });
            });
        });

        $postQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since post
        $postQuery->when($sincePid, function ($query, $value) {
            $sincePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '>', $sincePostId);
        });

        // before post
        $postQuery->when($beforePid, function ($query, $value) {
            $beforePostId = PrimaryHelper::fresnsPostIdByPid($value);

            $query->where('id', '<', $beforePostId);
        });

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            $postQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($contentType == 'Text') {
                $postQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $postQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        $postQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        $posts = $postQuery->latest()->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    /**
     * Comment Timelines.
     */
    // get comment list by follow all
    public function getCommentListByFollowAll(int $authUserId, ?string $contentType = null, ?string $sinceCid = null, ?string $beforeCid = null, ?string $dateLimit = null)
    {
        $followUserIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $followGroupIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GROUP, $authUserId);

        // follow user comments
        $userCommentQuery = Comment::whereIn('user_id', $followUserIds)->where('is_anonymous', false);
        // follow group comments
        $groupCommentQuery = Comment::where('digest_state', Comment::DIGEST_GENERAL)->whereDoesntHave('post', function ($query) use ($followGroupIds) {
            $query->whereIn('group_id', $followGroupIds);
        });
        // best digest comments
        $digestCommentQuery = Comment::where('digest_state', Comment::DIGEST_PREMIUM);

        // is_enabled
        $userCommentQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });
        $groupCommentQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });
        $digestCommentQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });

        // has author
        $userCommentQuery->whereRelation('author', 'is_enabled', 1);
        $groupCommentQuery->whereRelation('author', 'is_enabled', 1);
        $digestCommentQuery->whereRelation('author', 'is_enabled', 1);

        // block
        $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $blockGeotagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);
        $blockCommentIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_COMMENT, $authUserId);

        // block user
        if ($blockUserIds) {
            $groupCommentQuery->whereNotIn('user_id', $blockUserIds);
            $digestCommentQuery->whereNotIn('user_id', $blockUserIds);
        }
        // block group
        if ($blockGroupIds) {
            $userCommentQuery->whereNotIn('group_id', $blockGroupIds);
            $digestCommentQuery->whereNotIn('group_id', $blockGroupIds);
        }
        // block hashtag
        if ($blockHashtagIds) {
            $userCommentQuery->when($blockHashtagIds, function ($query, $value) {
                $query->where(function ($postQuery) use ($value) {
                    $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                        $query->whereNotIn('hashtag_id', $value);
                    });
                });
            });
            $groupCommentQuery->when($blockHashtagIds, function ($query, $value) {
                $query->where(function ($postQuery) use ($value) {
                    $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                        $query->whereNotIn('hashtag_id', $value);
                    });
                });
            });
            $digestCommentQuery->when($blockHashtagIds, function ($query, $value) {
                $query->where(function ($postQuery) use ($value) {
                    $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                        $query->whereNotIn('hashtag_id', $value);
                    });
                });
            });
        }
        // block geotag
        if ($blockGeotagIds) {
            $userCommentQuery->whereNotIn('geotag_id', $blockGeotagIds);
            $groupCommentQuery->whereNotIn('geotag_id', $blockGeotagIds);
            $digestCommentQuery->whereNotIn('geotag_id', $blockGeotagIds);
        }
        // block post
        if ($blockPostIds) {
            $userCommentQuery->when($blockPostIds, function ($query, $value) {
                $query->whereDoesntHave('post', function ($query) use ($value) {
                    $query->whereNotIn('id', $value);
                });
            });
            $groupCommentQuery->when($blockPostIds, function ($query, $value) {
                $query->whereDoesntHave('post', function ($query) use ($value) {
                    $query->whereNotIn('id', $value);
                });
            });
            $digestCommentQuery->when($blockPostIds, function ($query, $value) {
                $query->whereDoesntHave('post', function ($query) use ($value) {
                    $query->whereNotIn('id', $value);
                });
            });
        }
        // block comment
        if ($blockCommentIds) {
            $userCommentQuery->whereNotIn('id', $blockCommentIds);
            $groupCommentQuery->whereNotIn('id', $blockCommentIds);
            $digestCommentQuery->whereNotIn('id', $blockCommentIds);
        }

        // since comment
        $userCommentQuery->when($sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '>', $sinceCommentId);
        });
        $groupCommentQuery->when($sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '>', $sinceCommentId);
        });
        $digestCommentQuery->when($sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '>', $sinceCommentId);
        });

        // before comment
        $userCommentQuery->when($beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '<', $beforeCommentId);
        });
        $groupCommentQuery->when($beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '<', $beforeCommentId);
        });
        $digestCommentQuery->when($beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '<', $beforeCommentId);
        });

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            $userCommentQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });
            $groupCommentQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });
            $digestCommentQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($contentType == 'Text') {
                $userCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $groupCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $digestCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $userCommentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $groupCommentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $digestCommentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        $userCommentQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });
        $groupCommentQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });
        $digestCommentQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        $comments = $userCommentQuery->union($groupCommentQuery)->union($digestCommentQuery)->latest()->paginate(\request()->get('pageSize', 15));

        return $comments;
    }

    // get comment list by follow users
    public function getCommentListByFollowUsers(int $authUserId, ?string $contentType = null, ?string $sinceCid = null, ?string $beforeCid = null, ?string $dateLimit = null)
    {
        $commentQuery = Comment::query();

        $followUserIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $commentQuery->whereIn('user_id', $followUserIds)->where('is_anonymous', false);

        // is_enabled
        $commentQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });

        // has author
        $commentQuery->whereRelation('author', 'is_enabled', 1);

        // block
        $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $blockGeotagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);
        $blockCommentIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_COMMENT, $authUserId);

        $commentQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereDoesntHave('post', function ($query) use ($value) {
                $query->whereNotIn('group_id', $value);
            });
        });

        $commentQuery->when($blockHashtagIds, function ($query, $value) {
            $query->where(function ($postQuery) use ($value) {
                $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                    $query->whereNotIn('hashtag_id', $value);
                });
            });
        });

        $commentQuery->when($blockGeotagIds, function ($query, $value) {
            $query->whereNotIn('geotag_id', $value);
        });

        $commentQuery->when($blockPostIds, function ($query, $value) {
            $query->whereDoesntHave('post', function ($query) use ($value) {
                $query->whereNotIn('id', $value);
            });
        });

        $commentQuery->when($blockCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since comment
        $commentQuery->when($sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '>', $sinceCommentId);
        });

        // before comment
        $commentQuery->when($beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '<', $beforeCommentId);
        });

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            $commentQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($contentType == 'Text') {
                $commentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $commentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        $commentQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        // comments
        $comments = $commentQuery->latest()->paginate(\request()->get('pageSize', 15));

        return $comments;
    }

    // get comment list by follow groups
    public function getCommentListByFollowGroups(int $authUserId, ?string $contentType = null, ?string $sinceCid = null, ?string $beforeCid = null, ?string $dateLimit = null)
    {
        $commentQuery = Comment::query();

        $followGroupIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $commentQuery->whereDoesntHave('post', function ($query) use ($followGroupIds) {
            $query->whereIn('group_id', $followGroupIds);
        });

        // is_enabled
        $commentQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });

        // has author
        $commentQuery->whereRelation('author', 'is_enabled', 1);

        // block
        $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $blockGeotagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);
        $blockCommentIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_COMMENT, $authUserId);

        $commentQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        $commentQuery->when($blockHashtagIds, function ($query, $value) {
            $query->where(function ($postQuery) use ($value) {
                $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                    $query->whereNotIn('hashtag_id', $value);
                });
            });
        });

        $commentQuery->when($blockGeotagIds, function ($query, $value) {
            $query->whereNotIn('geotag_id', $value);
        });

        $commentQuery->when($blockPostIds, function ($query, $value) {
            $query->whereDoesntHave('post', function ($query) use ($value) {
                $query->whereNotIn('id', $value);
            });
        });

        $commentQuery->when($blockCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since comment
        $commentQuery->when($sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '>', $sinceCommentId);
        });

        // before comment
        $commentQuery->when($beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '<', $beforeCommentId);
        });

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            $commentQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($contentType == 'Text') {
                $commentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $commentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        $commentQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        // comments
        $comments = $commentQuery->latest()->paginate(\request()->get('pageSize', 15));

        return $comments;
    }

    // get comment list by follow hashtags
    public function getCommentListByFollowHashtags(int $authUserId, ?string $contentType = null, ?string $sinceCid = null, ?string $beforeCid = null, ?string $dateLimit = null)
    {
        $commentQuery = Comment::query();

        $followHashtagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $commentQuery->whereHas('hashtagUsages', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtag_id', $followHashtagIds);
        });

        // is_enabled
        $commentQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });

        // has author
        $commentQuery->whereRelation('author', 'is_enabled', 1);

        // block
        $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $blockGeotagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);
        $blockCommentIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_COMMENT, $authUserId);

        $commentQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        $commentQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereDoesntHave('post', function ($query) use ($value) {
                $query->whereNotIn('group_id', $value);
            });
        });

        $commentQuery->when($blockGeotagIds, function ($query, $value) {
            $query->whereNotIn('geotag_id', $value);
        });

        $commentQuery->when($blockPostIds, function ($query, $value) {
            $query->whereDoesntHave('post', function ($query) use ($value) {
                $query->whereNotIn('id', $value);
            });
        });

        $commentQuery->when($blockCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since comment
        $commentQuery->when($sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '>', $sinceCommentId);
        });

        // before comment
        $commentQuery->when($beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '<', $beforeCommentId);
        });

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            $commentQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($contentType == 'Text') {
                $commentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $commentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        $commentQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        // comments
        $comments = $commentQuery->latest()->paginate(\request()->get('pageSize', 15));

        return $comments;
    }

    // get comment list by follow geotags
    public function getCommentListByFollowGeotags(int $authUserId, ?string $contentType = null, ?string $sinceCid = null, ?string $beforeCid = null, ?string $dateLimit = null)
    {
        $commentQuery = Comment::query();

        $followGeotagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $commentQuery->when($followGeotagIds, function ($query, $value) {
            $query->whereNotIn('geotag_id', $value);
        });

        // is_enabled
        $commentQuery->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
            $query->where('is_enabled', false)->where('user_id', $authUserId);
        });

        // has author
        $commentQuery->whereRelation('author', 'is_enabled', 1);

        // block
        $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);
        $blockCommentIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_COMMENT, $authUserId);

        $commentQuery->when($blockUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        $commentQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereDoesntHave('post', function ($query) use ($value) {
                $query->whereNotIn('group_id', $value);
            });
        });

        $commentQuery->when($blockHashtagIds, function ($query, $value) {
            $query->where(function ($postQuery) use ($value) {
                $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                    $query->whereNotIn('hashtag_id', $value);
                });
            });
        });

        $commentQuery->when($blockPostIds, function ($query, $value) {
            $query->whereDoesntHave('post', function ($query) use ($value) {
                $query->whereNotIn('id', $value);
            });
        });

        $commentQuery->when($blockCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since comment
        $commentQuery->when($sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '>', $sinceCommentId);
        });

        // before comment
        $commentQuery->when($beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsCommentIdByCid($value);

            $query->where('id', '<', $beforeCommentId);
        });

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            $commentQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($contentType == 'Text') {
                $commentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $commentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        $commentQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        // comments
        $comments = $commentQuery->latest()->paginate(\request()->get('pageSize', 15));

        return $comments;
    }
}
