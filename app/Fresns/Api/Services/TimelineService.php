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
    // $options = [
    //     'langTag' => '',
    //     'contentType' => '',
    //     'sincePid' => '',
    //     'beforePid' => '',
    //     'dateLimit' => '',
    // ];

    // get post list by follow all
    public function getPostListByFollowAll(int $authUserId, ?array $options = [])
    {
        $langTag = $options['langTag'] ?? null;
        $contentType = $options['contentType'] ?? null;
        $sincePid = $options['sincePid'] ?? null;
        $beforePid = $options['beforePid'] ?? null;
        $dateLimit = $options['dateLimit'] ?? null;

        $followUserIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $followGroupIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $followHashtagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $followGeotagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);

        // follow user posts
        $userPostQuery = Post::whereIn('user_id', $followUserIds)->where('is_anonymous', false);
        // follow group posts
        $groupPostQuery = Post::whereIn('group_id', $followGroupIds);
        // follow hashtag posts
        $hashtagPostQuery = Post::whereHas('hashtagUsages', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtag_id', $followHashtagIds);
        });
        // follow geotag posts
        $geotagPostQuery = Post::whereIn('geotag_id', $followGeotagIds);
        // best digest posts
        $digestPostQuery = Post::query();

        // digest state
        $groupPostQuery->where('digest_state', Post::DIGEST_GENERAL);
        $hashtagPostQuery->where('digest_state', Post::DIGEST_GENERAL);
        $geotagPostQuery->where('digest_state', Post::DIGEST_GENERAL);
        $digestPostQuery->where('digest_state', Post::DIGEST_PREMIUM);

        // is_enabled
        $userPostQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });
        $groupPostQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });
        $hashtagPostQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });
        $geotagPostQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });
        $digestPostQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });

        // has author
        $userPostQuery->whereRelation('author', 'is_enabled', true);
        $groupPostQuery->whereRelation('author', 'is_enabled', true);
        $hashtagPostQuery->whereRelation('author', 'is_enabled', true);
        $geotagPostQuery->whereRelation('author', 'is_enabled', true);
        $digestPostQuery->whereRelation('author', 'is_enabled', true);

        // block
        $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $blockGeotagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);

        // block user
        if ($blockUserIds) {
            $groupPostQuery->whereNotIn('user_id', $blockUserIds);
            $hashtagPostQuery->whereNotIn('user_id', $blockUserIds);
            $geotagPostQuery->whereNotIn('user_id', $blockUserIds);
            $digestPostQuery->whereNotIn('user_id', $blockUserIds);
        }
        // block group
        if ($blockGroupIds) {
            $userPostQuery->whereNotIn('group_id', $blockGroupIds);
            $hashtagPostQuery->whereNotIn('group_id', $blockGroupIds);
            $geotagPostQuery->whereNotIn('group_id', $blockGroupIds);
            $digestPostQuery->whereNotIn('group_id', $blockGroupIds);
        }
        // block hashtag
        if ($blockHashtagIds) {
            $userPostQuery->where(function ($query) use ($blockHashtagIds) {
                $query->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
            $groupPostQuery->where(function ($query) use ($blockHashtagIds) {
                $query->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
            $geotagPostQuery->where(function ($query) use ($blockHashtagIds) {
                $query->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
            $digestPostQuery->where(function ($query) use ($blockHashtagIds) {
                $query->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
        }
        // block geotag
        if ($blockGeotagIds) {
            $userPostQuery->whereNotIn('geotag_id', $blockGeotagIds);
            $groupPostQuery->whereNotIn('geotag_id', $blockGeotagIds);
            $hashtagPostQuery->whereNotIn('geotag_id', $blockGeotagIds);
            $digestPostQuery->whereNotIn('geotag_id', $blockGeotagIds);
        }
        // block post
        if ($blockPostIds) {
            $userPostQuery->whereNotIn('id', $blockPostIds);
            $groupPostQuery->whereNotIn('id', $blockPostIds);
            $hashtagPostQuery->whereNotIn('id', $blockPostIds);
            $geotagPostQuery->whereNotIn('id', $blockPostIds);
            $digestPostQuery->whereNotIn('id', $blockPostIds);
        }

        // since post
        if ($sincePid) {
            $sincePostId = PrimaryHelper::fresnsPrimaryId('post', $sincePid);

            $userPostQuery->where('id', '>', $sincePostId);
            $groupPostQuery->where('id', '>', $sincePostId);
            $hashtagPostQuery->where('id', '>', $sincePostId);
            $geotagPostQuery->where('id', '>', $sincePostId);
            $digestPostQuery->where('id', '>', $sincePostId);
        }

        // before post
        if ($beforePid) {
            $beforePostId = PrimaryHelper::fresnsPrimaryId('post', $beforePid);

            $userPostQuery->where('id', '<', $beforePostId);
            $groupPostQuery->where('id', '<', $beforePostId);
            $hashtagPostQuery->where('id', '<', $beforePostId);
            $geotagPostQuery->where('id', '<', $beforePostId);
            $digestPostQuery->where('id', '<', $beforePostId);
        }

        // lang tag
        if ($langTag) {
            $userPostQuery->where('lang_tag', $langTag);
            $groupPostQuery->where('lang_tag', $langTag);
            $hashtagPostQuery->where('lang_tag', $langTag);
            $geotagPostQuery->where('lang_tag', $langTag);
            $digestPostQuery->where('lang_tag', $langTag);
        }

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($fileTypeNumber) {
                $userPostQuery->whereRelation('fileUsages', 'file_type', $fileTypeNumber);
                $groupPostQuery->whereRelation('fileUsages', 'file_type', $fileTypeNumber);
                $hashtagPostQuery->whereRelation('fileUsages', 'file_type', $fileTypeNumber);
                $geotagPostQuery->whereRelation('fileUsages', 'file_type', $fileTypeNumber);
                $digestPostQuery->whereRelation('fileUsages', 'file_type', $fileTypeNumber);
            }

            // text
            if ($contentType == 'Text') {
                $userPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $groupPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $hashtagPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $geotagPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $digestPostQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $userPostQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $groupPostQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $hashtagPostQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $geotagPostQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $digestPostQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        if ($dateLimit) {
            $userPostQuery->where('created_at', '<=', $dateLimit);
            $groupPostQuery->where('created_at', '<=', $dateLimit);
            $hashtagPostQuery->where('created_at', '<=', $dateLimit);
            $geotagPostQuery->where('created_at', '<=', $dateLimit);
            $digestPostQuery->where('created_at', '<=', $dateLimit);
        }

        $posts = $userPostQuery->union($groupPostQuery)->union($hashtagPostQuery)->union($geotagPostQuery)->union($digestPostQuery)->latest()->paginate(\request()->get('pageSize', 15));

        return $posts;
    }

    // get post list by follow users
    public function getPostListByFollowUsers(int $authUserId, ?array $options = [])
    {
        $langTag = $options['langTag'] ?? null;
        $contentType = $options['contentType'] ?? null;
        $sincePid = $options['sincePid'] ?? null;
        $beforePid = $options['beforePid'] ?? null;
        $dateLimit = $options['dateLimit'] ?? null;

        $postQuery = Post::query();

        $followUserIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $postQuery->whereIn('user_id', $followUserIds)->where('is_anonymous', false);

        // is_enabled
        $postQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });

        // has author
        $postQuery->whereRelation('author', 'is_enabled', true);

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
            $sincePostId = PrimaryHelper::fresnsPrimaryId('post', $value);

            $query->where('id', '>', $sincePostId);
        });

        // before post
        $postQuery->when($beforePid, function ($query, $value) {
            $beforePostId = PrimaryHelper::fresnsPrimaryId('post', $value);

            $query->where('id', '<', $beforePostId);
        });

        // lang tag
        $postQuery->when($langTag, function ($query, $value) {
            $query->where('lang_tag', $value);
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
    public function getPostListByFollowGroups(int $authUserId, ?array $options = [])
    {
        $langTag = $options['langTag'] ?? null;
        $contentType = $options['contentType'] ?? null;
        $sincePid = $options['sincePid'] ?? null;
        $beforePid = $options['beforePid'] ?? null;
        $dateLimit = $options['dateLimit'] ?? null;

        $postQuery = Post::query();

        $followGroupIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $postQuery->whereIn('group_id', $followGroupIds);

        // is_enabled
        $postQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });

        // has author
        $postQuery->whereRelation('author', 'is_enabled', true);

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
            $sincePostId = PrimaryHelper::fresnsPrimaryId('post', $value);

            $query->where('id', '>', $sincePostId);
        });

        // before post
        $postQuery->when($beforePid, function ($query, $value) {
            $beforePostId = PrimaryHelper::fresnsPrimaryId('post', $value);

            $query->where('id', '<', $beforePostId);
        });

        // lang tag
        $postQuery->when($langTag, function ($query, $value) {
            $query->where('lang_tag', $value);
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
    public function getPostListByFollowHashtags(int $authUserId, ?array $options = [])
    {
        $langTag = $options['langTag'] ?? null;
        $contentType = $options['contentType'] ?? null;
        $sincePid = $options['sincePid'] ?? null;
        $beforePid = $options['beforePid'] ?? null;
        $dateLimit = $options['dateLimit'] ?? null;

        $postQuery = Post::query();

        $followHashtagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $postQuery->whereHas('hashtagUsages', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtag_id', $followHashtagIds);
        });

        // is_enabled
        $postQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });

        // has author
        $postQuery->whereRelation('author', 'is_enabled', true);

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
            $sincePostId = PrimaryHelper::fresnsPrimaryId('post', $value);

            $query->where('id', '>', $sincePostId);
        });

        // before post
        $postQuery->when($beforePid, function ($query, $value) {
            $beforePostId = PrimaryHelper::fresnsPrimaryId('post', $value);

            $query->where('id', '<', $beforePostId);
        });

        // lang tag
        $postQuery->when($langTag, function ($query, $value) {
            $query->where('lang_tag', $value);
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
    public function getPostListByFollowGeotags(int $authUserId, ?array $options = [])
    {
        $langTag = $options['langTag'] ?? null;
        $contentType = $options['contentType'] ?? null;
        $sincePid = $options['sincePid'] ?? null;
        $beforePid = $options['beforePid'] ?? null;
        $dateLimit = $options['dateLimit'] ?? null;

        $postQuery = Post::query();

        $followGeotagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $postQuery->whereIn('geotag_id', $followGeotagIds);

        // is_enabled
        $postQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });

        // has author
        $postQuery->whereRelation('author', 'is_enabled', true);

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
            $sincePostId = PrimaryHelper::fresnsPrimaryId('post', $value);

            $query->where('id', '>', $sincePostId);
        });

        // before post
        $postQuery->when($beforePid, function ($query, $value) {
            $beforePostId = PrimaryHelper::fresnsPrimaryId('post', $value);

            $query->where('id', '<', $beforePostId);
        });

        // lang tag
        $postQuery->when($langTag, function ($query, $value) {
            $query->where('lang_tag', $value);
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
    // $options = [
    //     'langTag' => '',
    //     'contentType' => '',
    //     'sinceCid' => '',
    //     'beforeCid' => '',
    //     'dateLimit' => '',
    // ];

    // get comment list by follow all
    public function getCommentListByFollowAll(int $authUserId, ?array $options = [])
    {
        $langTag = $options['langTag'] ?? null;
        $contentType = $options['contentType'] ?? null;
        $sinceCid = $options['sinceCid'] ?? null;
        $beforeCid = $options['beforeCid'] ?? null;
        $dateLimit = $options['dateLimit'] ?? null;

        $followUserIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $followGroupIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $followHashtagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $followGeotagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);

        // follow user comments
        $userCommentQuery = Comment::whereIn('user_id', $followUserIds)->where('is_anonymous', false);
        // follow group comments
        $groupCommentQuery = Comment::whereDoesntHave('post', function ($query) use ($followGroupIds) {
            $query->whereIn('group_id', $followGroupIds);
        });
        // follow hashtag comments
        $hashtagCommentQuery = Comment::whereHas('hashtagUsages', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtag_id', $followHashtagIds);
        });
        // follow geotag comments
        $geotagCommentQuery = Comment::whereIn('geotag_id', $followGeotagIds);
        // best digest comments
        $digestCommentQuery = Comment::query();

        // digest state
        $groupCommentQuery->where('digest_state', Comment::DIGEST_GENERAL);
        $hashtagCommentQuery->where('digest_state', Comment::DIGEST_GENERAL);
        $geotagCommentQuery->where('digest_state', Comment::DIGEST_GENERAL);
        $digestCommentQuery->where('digest_state', Comment::DIGEST_PREMIUM);

        // is_enabled
        $userCommentQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });
        $groupCommentQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });
        $hashtagCommentQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });
        $geotagCommentQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });
        $digestCommentQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });

        // has author
        $userCommentQuery->whereRelation('author', 'is_enabled', true);
        $groupCommentQuery->whereRelation('author', 'is_enabled', true);
        $hashtagCommentQuery->whereRelation('author', 'is_enabled', true);
        $geotagCommentQuery->whereRelation('author', 'is_enabled', true);
        $digestCommentQuery->whereRelation('author', 'is_enabled', true);

        // has post
        $userCommentQuery->whereRelation('post', 'is_enabled', true);
        $groupCommentQuery->whereRelation('post', 'is_enabled', true);
        $hashtagCommentQuery->whereRelation('post', 'is_enabled', true);
        $geotagCommentQuery->whereRelation('post', 'is_enabled', true);
        $digestCommentQuery->whereRelation('post', 'is_enabled', true);

        // privacy
        $userCommentQuery->where('top_parent_id', 0)->where('privacy_state', Comment::PRIVACY_PUBLIC);
        $groupCommentQuery->where('top_parent_id', 0)->where('privacy_state', Comment::PRIVACY_PUBLIC);
        $hashtagCommentQuery->where('top_parent_id', 0)->where('privacy_state', Comment::PRIVACY_PUBLIC);
        $geotagCommentQuery->where('top_parent_id', 0)->where('privacy_state', Comment::PRIVACY_PUBLIC);
        $digestCommentQuery->where('top_parent_id', 0)->where('privacy_state', Comment::PRIVACY_PUBLIC);

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
            $hashtagCommentQuery->whereNotIn('user_id', $blockUserIds);
            $geotagCommentQuery->whereNotIn('user_id', $blockUserIds);
            $digestCommentQuery->whereNotIn('user_id', $blockUserIds);
        }
        // block group
        if ($blockGroupIds) {
            $userCommentQuery->whereDoesntHave('post', function ($query) use ($blockGroupIds) {
                $query->whereNotIn('group_id', $blockGroupIds);
            });
            $hashtagCommentQuery->whereDoesntHave('post', function ($query) use ($blockGroupIds) {
                $query->whereNotIn('group_id', $blockGroupIds);
            });
            $geotagCommentQuery->whereDoesntHave('post', function ($query) use ($blockGroupIds) {
                $query->whereNotIn('group_id', $blockGroupIds);
            });
            $digestCommentQuery->whereDoesntHave('post', function ($query) use ($blockGroupIds) {
                $query->whereNotIn('group_id', $blockGroupIds);
            });
        }
        // block hashtag
        if ($blockHashtagIds) {
            $userCommentQuery->where(function ($query) use ($blockHashtagIds) {
                $query->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
            $groupCommentQuery->where(function ($query) use ($blockHashtagIds) {
                $query->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
            $geotagCommentQuery->where(function ($query) use ($blockHashtagIds) {
                $query->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
            $digestCommentQuery->where(function ($query) use ($blockHashtagIds) {
                $query->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($blockHashtagIds) {
                    $query->whereNotIn('hashtag_id', $blockHashtagIds);
                });
            });
        }
        // block geotag
        if ($blockGeotagIds) {
            $userCommentQuery->whereNotIn('geotag_id', $blockGeotagIds);
            $groupCommentQuery->whereNotIn('geotag_id', $blockGeotagIds);
            $hashtagCommentQuery->whereNotIn('geotag_id', $blockGeotagIds);
            $geotagCommentQuery->whereNotIn('geotag_id', $blockGeotagIds);
            $digestCommentQuery->whereNotIn('geotag_id', $blockGeotagIds);
        }
        // block post
        if ($blockPostIds) {
            $userCommentQuery->whereNotIn('post_id', $blockPostIds);
            $groupCommentQuery->whereNotIn('post_id', $blockPostIds);
            $hashtagCommentQuery->whereNotIn('geotag_id', $blockPostIds);
            $geotagCommentQuery->whereNotIn('geotag_id', $blockPostIds);
            $digestCommentQuery->whereNotIn('post_id', $blockPostIds);
        }
        // block comment
        if ($blockCommentIds) {
            $userCommentQuery->whereNotIn('id', $blockCommentIds);
            $groupCommentQuery->whereNotIn('id', $blockCommentIds);
            $hashtagCommentQuery->whereNotIn('id', $blockCommentIds);
            $geotagCommentQuery->whereNotIn('id', $blockCommentIds);
            $digestCommentQuery->whereNotIn('id', $blockCommentIds);
        }

        // since comment
        if ($sinceCid) {
            $sinceCommentId = PrimaryHelper::fresnsPrimaryId('comment', $sinceCid);

            $userCommentQuery->where('id', '>', $sinceCommentId);
            $groupCommentQuery->where('id', '>', $sinceCommentId);
            $hashtagCommentQuery->where('id', '>', $sinceCommentId);
            $geotagCommentQuery->where('id', '>', $sinceCommentId);
            $digestCommentQuery->where('id', '>', $sinceCommentId);
        }

        // before comment
        if ($beforeCid) {
            $beforeCommentId = PrimaryHelper::fresnsPrimaryId('comment', $beforeCid);

            $userCommentQuery->where('id', '<', $beforeCommentId);
            $groupCommentQuery->where('id', '<', $beforeCommentId);
            $hashtagCommentQuery->where('id', '<', $beforeCommentId);
            $geotagCommentQuery->where('id', '<', $beforeCommentId);
            $digestCommentQuery->where('id', '<', $beforeCommentId);
        }

        // lang tag
        if ($langTag) {
            $userCommentQuery->where('lang_tag', $langTag);
            $groupCommentQuery->where('lang_tag', $langTag);
            $hashtagCommentQuery->where('lang_tag', $langTag);
            $geotagCommentQuery->where('lang_tag', $langTag);
            $digestCommentQuery->where('lang_tag', $langTag);
        }

        // content type
        if ($contentType && $contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($fileTypeNumber) {
                $userCommentQuery->whereRelation('fileUsages', 'file_type', $fileTypeNumber);
                $groupCommentQuery->whereRelation('fileUsages', 'file_type', $fileTypeNumber);
                $hashtagCommentQuery->whereRelation('fileUsages', 'file_type', $fileTypeNumber);
                $geotagCommentQuery->whereRelation('fileUsages', 'file_type', $fileTypeNumber);
                $digestCommentQuery->whereRelation('fileUsages', 'file_type', $fileTypeNumber);
            }

            // text
            if ($contentType == 'Text') {
                $userCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $groupCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $hashtagCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $geotagCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
                $digestCommentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $userCommentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $groupCommentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $hashtagCommentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $geotagCommentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
                $digestCommentQuery->whereRelation('extendUsages', 'app_fskey', $contentType);
            }
        }

        // datetime limit
        if ($dateLimit) {
            $userCommentQuery->where('created_at', '<=', $dateLimit);
            $groupCommentQuery->where('created_at', '<=', $dateLimit);
            $hashtagCommentQuery->where('created_at', '<=', $dateLimit);
            $geotagCommentQuery->where('created_at', '<=', $dateLimit);
            $digestCommentQuery->where('created_at', '<=', $dateLimit);
        }

        $comments = $userCommentQuery->union($groupCommentQuery)->union($hashtagCommentQuery)->union($geotagCommentQuery)->union($digestCommentQuery)->latest()->paginate(\request()->get('pageSize', 15));

        return $comments;
    }

    // get comment list by follow users
    public function getCommentListByFollowUsers(int $authUserId, ?array $options = [])
    {
        $langTag = $options['langTag'] ?? null;
        $contentType = $options['contentType'] ?? null;
        $sinceCid = $options['sinceCid'] ?? null;
        $beforeCid = $options['beforeCid'] ?? null;
        $dateLimit = $options['dateLimit'] ?? null;

        $commentQuery = Comment::query();

        $followUserIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_USER, $authUserId);
        $commentQuery->whereIn('user_id', $followUserIds)->where('is_anonymous', false);

        // is_enabled
        $commentQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });

        // has author
        $commentQuery->whereRelation('author', 'is_enabled', true);

        // has post
        $commentQuery->whereRelation('post', 'is_enabled', true);

        // privacy
        $commentQuery->where('top_parent_id', 0)->where('privacy_state', Comment::PRIVACY_PUBLIC);

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
            $query->where(function ($commentQuery) use ($value) {
                $commentQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                    $query->whereNotIn('hashtag_id', $value);
                });
            });
        });

        $commentQuery->when($blockGeotagIds, function ($query, $value) {
            $query->whereNotIn('geotag_id', $value);
        });

        $commentQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('post_id', $value);
        });

        $commentQuery->when($blockCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since comment
        $commentQuery->when($sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsPrimaryId('comment', $value);

            $query->where('id', '>', $sinceCommentId);
        });

        // before comment
        $commentQuery->when($beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsPrimaryId('comment', $value);

            $query->where('id', '<', $beforeCommentId);
        });

        // lang tag
        $commentQuery->when($langTag, function ($query, $value) {
            $query->where('lang_tag', $value);
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
    public function getCommentListByFollowGroups(int $authUserId, ?array $options = [])
    {
        $langTag = $options['langTag'] ?? null;
        $contentType = $options['contentType'] ?? null;
        $sinceCid = $options['sinceCid'] ?? null;
        $beforeCid = $options['beforeCid'] ?? null;
        $dateLimit = $options['dateLimit'] ?? null;

        $commentQuery = Comment::query();

        $followGroupIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
        $commentQuery->whereDoesntHave('post', function ($query) use ($followGroupIds) {
            $query->whereIn('group_id', $followGroupIds);
        });

        // is_enabled
        $commentQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });

        // has author
        $commentQuery->whereRelation('author', 'is_enabled', true);

        // has post
        $commentQuery->whereRelation('post', 'is_enabled', true);

        // privacy
        $commentQuery->where('top_parent_id', 0)->where('privacy_state', Comment::PRIVACY_PUBLIC);

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
            $query->where(function ($commentQuery) use ($value) {
                $commentQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                    $query->whereNotIn('hashtag_id', $value);
                });
            });
        });

        $commentQuery->when($blockGeotagIds, function ($query, $value) {
            $query->whereNotIn('geotag_id', $value);
        });

        $commentQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('post_id', $value);
        });

        $commentQuery->when($blockCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since comment
        $commentQuery->when($sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsPrimaryId('comment', $value);

            $query->where('id', '>', $sinceCommentId);
        });

        // before comment
        $commentQuery->when($beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsPrimaryId('comment', $value);

            $query->where('id', '<', $beforeCommentId);
        });

        // lang tag
        $commentQuery->when($langTag, function ($query, $value) {
            $query->where('lang_tag', $value);
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
    public function getCommentListByFollowHashtags(int $authUserId, ?array $options = [])
    {
        $langTag = $options['langTag'] ?? null;
        $contentType = $options['contentType'] ?? null;
        $sinceCid = $options['sinceCid'] ?? null;
        $beforeCid = $options['beforeCid'] ?? null;
        $dateLimit = $options['dateLimit'] ?? null;

        $commentQuery = Comment::query();

        $followHashtagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $commentQuery->whereHas('hashtagUsages', function ($query) use ($followHashtagIds) {
            $query->whereIn('hashtag_id', $followHashtagIds);
        });

        // is_enabled
        $commentQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });

        // has author
        $commentQuery->whereRelation('author', 'is_enabled', true);

        // has post
        $commentQuery->whereRelation('post', 'is_enabled', true);

        // privacy
        $commentQuery->where('top_parent_id', 0)->where('privacy_state', Comment::PRIVACY_PUBLIC);

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
            $query->whereNotIn('post_id', $value);
        });

        $commentQuery->when($blockCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since comment
        $commentQuery->when($sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsPrimaryId('comment', $value);

            $query->where('id', '>', $sinceCommentId);
        });

        // before comment
        $commentQuery->when($beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsPrimaryId('comment', $value);

            $query->where('id', '<', $beforeCommentId);
        });

        // lang tag
        $commentQuery->when($langTag, function ($query, $value) {
            $query->where('lang_tag', $value);
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
    public function getCommentListByFollowGeotags(int $authUserId, ?array $options = [])
    {
        $langTag = $options['langTag'] ?? null;
        $contentType = $options['contentType'] ?? null;
        $sinceCid = $options['sinceCid'] ?? null;
        $beforeCid = $options['beforeCid'] ?? null;
        $dateLimit = $options['dateLimit'] ?? null;

        $commentQuery = Comment::query();

        $followGeotagIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
        $commentQuery->whereIn('geotag_id', $followGeotagIds);

        // is_enabled
        $commentQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enabled', false)->where('user_id', $authUserId);
            });
        });

        // has author
        $commentQuery->whereRelation('author', 'is_enabled', true);

        // has post
        $commentQuery->whereRelation('post', 'is_enabled', true);

        // privacy
        $commentQuery->where('top_parent_id', 0)->where('privacy_state', Comment::PRIVACY_PUBLIC);

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
            $query->where(function ($commentQuery) use ($value) {
                $commentQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                    $query->whereNotIn('hashtag_id', $value);
                });
            });
        });

        $commentQuery->when($blockPostIds, function ($query, $value) {
            $query->whereNotIn('post_id', $value);
        });

        $commentQuery->when($blockCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // since comment
        $commentQuery->when($sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsPrimaryId('comment', $value);

            $query->where('id', '>', $sinceCommentId);
        });

        // before comment
        $commentQuery->when($beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsPrimaryId('comment', $value);

            $query->where('id', '<', $beforeCommentId);
        });

        // lang tag
        $commentQuery->when($langTag, function ($query, $value) {
            $query->where('lang_tag', $value);
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
