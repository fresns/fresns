<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\CommentDetailDTO;
use App\Fresns\Api\Http\DTO\CommentListDTO;
use App\Fresns\Api\Http\DTO\FollowDTO;
use App\Fresns\Api\Http\DTO\InteractionDTO;
use App\Fresns\Api\Http\DTO\NearbyDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
use App\Fresns\Api\Services\CommentService;
use App\Fresns\Api\Services\FollowService;
use App\Fresns\Api\Services\GroupService;
use App\Fresns\Api\Services\InteractionService;
use App\Fresns\Api\Services\UserService;
use App\Helpers\AppHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractionUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new CommentListDTO($request->all());

        // Plugin provides data
        $dataPluginFskey = ConfigHelper::fresnsConfigByItemKey('comment_list_service');

        if ($dtoRequest->contentType && ! $dataPluginFskey) {
            $dataPluginFskey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'commentByAll');
        }

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => AppHelper::getHeaders(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getCommentByAll($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $commentQuery = Comment::with(['author', 'post', 'hashtagUsages'])->has('author');

        $blockGroupIds = InteractionUtility::getPrivateGroupIdArr();

        if ($authUserId) {
            $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);
            $blockCommentIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_COMMENT, $authUserId);
            $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
            $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
            $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);

            $commentQuery->when($blockCommentIds, function ($query, $value) {
                $query->whereNotIn('id', $value);
            });

            $commentQuery->when($blockPostIds, function ($query, $value) {
                $query->whereNotIn('post_id', $value);
            });

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
        }

        // is enabled
        $commentQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true);
            if ($authUserId) {
                $query->orWhere(function ($query) use ($authUserId) {
                    $query->where('is_enabled', false)->where('user_id', $authUserId);
                });
            }
        });

        $commentQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereHas('post', function ($query) use ($value) {
                $query->whereNotIn('group_id', $value);
            });
        });

        $outputReplyToPost = true;

        if ($dtoRequest->uidOrUsername) {
            $userCommentConfig = ConfigHelper::fresnsConfigByItemKey('it_comments');
            if (! $userCommentConfig) {
                throw new ApiException(35305);
            }

            $viewUser = PrimaryHelper::fresnsModelByFsid('user', $dtoRequest->uidOrUsername);

            if (empty($viewUser) || $viewUser->trashed()) {
                throw new ApiException(31602);
            }

            $commentQuery->where('user_id', $viewUser->id)->where('is_anonymous', 0);
        }

        if ($dtoRequest->pid) {
            $viewPost = PrimaryHelper::fresnsModelByFsid('post', $dtoRequest->pid);

            if (empty($viewPost) || $viewPost->trashed()) {
                throw new ApiException(37300);
            }

            if (! $viewPost->is_enabled && $viewPost->user_id != $authUserId) {
                throw new ApiException(37301);
            }

            $commentVisibilityRule = ConfigHelper::fresnsConfigByItemKey('comment_visibility_rule');
            if ($commentVisibilityRule > 0) {
                $visibilityTime = $viewPost->created_at->addDay($commentVisibilityRule);

                if ($visibilityTime->lt(now())) {
                    return $this->warning(37404);
                }
            }

            $commentQuery->where('post_id', $viewPost->id)->where('top_parent_id', 0);

            $outputReplyToPost = false;
        } else {
            // user is enabled
            $commentQuery->whereHas('author', function ($query) {
                $query->where('is_enabled', true);
            });

            // is comment private
            $commentQuery->whereHas('postAppend', function ($query) {
                $query->where('is_comment_private', false);
            });
        }

        $dataType = 'list';
        $outputSubComments = true;
        $outputReplyToComment = false;
        if ($dtoRequest->cid) {
            $viewComment = PrimaryHelper::fresnsModelByFsid('comment', $dtoRequest->cid);

            if (empty($viewComment) || $viewComment->trashed()) {
                throw new ApiException(37400);
            }

            if (! $viewComment->is_enabled) {
                throw new ApiException(37401);
            }

            if ($viewComment->top_parent_id) {
                $commentQuery->where('parent_id', $viewComment->id);
            } else {
                $commentQuery->where('top_parent_id', $viewComment->id);
            }

            $dataType = 'detail';
            $outputSubComments = false;
            $outputReplyToPost = false;
            $outputReplyToComment = true;
        }

        $groupDateLimit = null;
        if ($dtoRequest->gid) {
            $viewGroup = PrimaryHelper::fresnsModelByFsid('group', $dtoRequest->gid);
            $groupId = $viewGroup->id;

            if (empty($viewGroup) || $viewGroup->trashed()) {
                throw new ApiException(37100);
            }

            if (! $viewGroup->is_enabled) {
                throw new ApiException(37101);
            }

            // group mode
            $checkLimit = GroupService::getGroupContentDateLimit($groupId, $authUserId);

            if ($checkLimit['code']) {
                return $this->warning($checkLimit['code']);
            }

            $groupDateLimit = $checkLimit['datetime'];

            if ($dtoRequest->includeSubgroups) {
                $allGroups = PrimaryHelper::fresnsModelGroups($groupId);

                $groupsArr = $allGroups->pluck('id');

                $commentQuery->whereHas('post', function ($query) use ($groupsArr) {
                    $query->whereIn('group_id', $groupsArr);
                });
            } else {
                $commentQuery->whereHas('post', function ($query) use ($groupId) {
                    $query->where('group_id', $groupId);
                });
            }
        }

        if ($dtoRequest->hid) {
            $hid = StrHelper::slug($dtoRequest->hid);
            $viewHashtag = PrimaryHelper::fresnsModelByFsid('hashtag', $hid);

            if (empty($viewHashtag)) {
                throw new ApiException(37200);
            }

            if (! $viewHashtag->is_enabled) {
                throw new ApiException(37201);
            }

            $commentQuery->when($viewHashtag->id, function ($query, $value) {
                $query->whereHas('hashtagUsages', function ($query) use ($value) {
                    $query->where('hashtag_id', $value);
                });
            });
        }

        if ($dtoRequest->allDigest) {
            $commentQuery->whereIn('digest_state', [Comment::DIGEST_GENERAL, Comment::DIGEST_BEST]);
        } else {
            $commentQuery->when($dtoRequest->digestState, function ($query, $value) {
                $query->where('digest_state', $value);
            });
        }

        $commentQuery->when($dtoRequest->sticky, function ($query, $value) {
            $query->where('is_sticky', $value);
        });

        if ($dtoRequest->createDate) {
            switch ($dtoRequest->createDate) {
                case 'today':
                    $commentQuery->whereDate('created_at', now()->format('Y-m-d'));
                    break;

                case 'yesterday':
                    $commentQuery->whereDate('created_at', now()->subDay()->format('Y-m-d'));
                    break;

                case 'week':
                    $commentQuery->whereDate('created_at', '>=', now()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'lastWeek':
                    $commentQuery->whereDate('created_at', '>=', now()->subWeek()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->subWeek()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'month':
                    $commentQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;

                case 'lastMonth':
                    $lastMonth = now()->subMonth()->month;
                    $year = now()->year;
                    if ($lastMonth == 12) {
                        $year = now()->subYear()->year;
                    }
                    $commentQuery->whereMonth('created_at', $lastMonth)->whereYear('created_at', $year);
                    break;

                case 'year':
                    $commentQuery->whereYear('created_at', now()->year);
                    break;

                case 'lastYear':
                    $commentQuery->whereYear('created_at', now()->subYear()->year);
                    break;
            }
        } else {
            $commentQuery->when($dtoRequest->createDateGt, function ($query, $value) {
                $query->whereDate('created_at', '>=', $value);
            });

            $commentQuery->when($dtoRequest->createDateLt, function ($query, $value) {
                $query->whereDate('created_at', '<=', $value);
            });
        }

        $commentQuery->when($dtoRequest->viewCountGt, function ($query, $value) {
            $query->where('view_count', '>=', $value);
        });

        $commentQuery->when($dtoRequest->viewCountLt, function ($query, $value) {
            $query->where('view_count', '<=', $value);
        });

        $commentQuery->when($dtoRequest->likeCountGt, function ($query, $value) {
            $query->where('like_count', '>=', $value);
        });

        $commentQuery->when($dtoRequest->likeCountLt, function ($query, $value) {
            $query->where('like_count', '<=', $value);
        });

        $commentQuery->when($dtoRequest->dislikeCountGt, function ($query, $value) {
            $query->where('dislike_count', '>=', $value);
        });

        $commentQuery->when($dtoRequest->dislikeCountLt, function ($query, $value) {
            $query->where('dislike_count', '<=', $value);
        });

        $commentQuery->when($dtoRequest->followCountGt, function ($query, $value) {
            $query->where('follow_count', '>=', $value);
        });

        $commentQuery->when($dtoRequest->followCountLt, function ($query, $value) {
            $query->where('follow_count', '<=', $value);
        });

        $commentQuery->when($dtoRequest->blockCountGt, function ($query, $value) {
            $query->where('block_count', '>=', $value);
        });

        $commentQuery->when($dtoRequest->blockCountLt, function ($query, $value) {
            $query->where('block_count', '<=', $value);
        });

        $commentQuery->when($dtoRequest->commentCountGt, function ($query, $value) {
            $query->where('comment_count', '>=', $value);
        });

        $commentQuery->when($dtoRequest->commentCountGt, function ($query, $value) {
            $query->where('comment_count', '<=', $value);
        });

        if ($dtoRequest->contentType && $dtoRequest->contentType != 'All') {
            $contentType = $dtoRequest->contentType;

            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($contentType == 'Text') {
                $commentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } elseif ($fileTypeNumber) {
                $commentQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
            } else {
                $commentQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_fskey', $contentType);
                });
            }
        }

        $dateLimit = $groupDateLimit ?? UserService::getContentDateLimit($authUserId);
        $commentQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        if ($dtoRequest->orderType == 'random') {
            $commentQuery->inRandomOrder();
        } else {
            $orderType = match ($dtoRequest->orderType) {
                default => 'created_at',
                'createDate' => 'created_at',
                'view' => 'view_count',
                'like' => 'like_count',
                'dislike' => 'dislike_count',
                'follow' => 'follow_count',
                'block' => 'block_count',
                'comment' => 'comment_count',
            };

            $orderDirection = match ($dtoRequest->orderDirection) {
                default => 'desc',
                'asc' => 'asc',
                'desc' => 'desc',
            };

            $commentQuery->orderBy($orderType, $orderDirection);
        }

        $comments = $commentQuery->paginate($dtoRequest->pageSize ?? 15);

        $commentConfig = [
            'mapId' => $dtoRequest->mapId,
            'longitude' => $dtoRequest->mapLng,
            'latitude' => $dtoRequest->mapLat,
            'outputSubComments' => $outputSubComments,
            'outputReplyToPost' => $outputReplyToPost,
            'outputReplyToComment' => $outputReplyToComment,
            'whetherToFilter' => true,
        ];

        $commentList = [];
        $service = new CommentService();
        foreach ($comments as $comment) {
            $fresnsCommentModel = PrimaryHelper::fresnsModelById('comment', $comment->id);

            if (empty($fresnsCommentModel->post) || empty($fresnsCommentModel->postAppend)) {
                continue;
            }

            if ($fresnsCommentModel->post->deleted_at) {
                continue;
            }

            $commentList[] = $service->commentData(
                $comment,
                $dataType,
                $langTag,
                $timezone,
                $authUserId,
                $commentConfig['mapId'],
                $commentConfig['longitude'],
                $commentConfig['latitude'],
                $commentConfig['outputSubComments'],
                $commentConfig['outputReplyToPost'],
                $commentConfig['outputReplyToComment'],
                $commentConfig['whetherToFilter'],
            );
        }

        return $this->fresnsPaginate($commentList, $comments->total(), $comments->perPage());
    }

    // detail
    public function detail(string $cid, Request $request)
    {
        $dtoRequest = new CommentDetailDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $comment = Comment::with(['author'])->where('cid', $cid)->first();

        if (empty($comment)) {
            throw new ApiException(37400);
        }

        // check author
        if (empty($comment?->author)) {
            throw new ApiException(35203);
        }

        // check is enabled
        if (! $comment->is_enabled && $comment->user_id != $authUserId) {
            throw new ApiException(37401);
        }

        UserService::checkUserContentViewPerm($comment->created_at, $authUserId);
        GroupService::checkGroupContentViewPerm($comment->created_at, $comment?->post?->group_id, $authUserId);

        // Plugin provides data
        $dataPluginFskey = ConfigHelper::fresnsConfigByItemKey('comment_detail_service');

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => AppHelper::getHeaders(),
                'body' => $dtoRequest->toArray(),
                'fsid' => $cid,
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getCommentDetail($wordBody);

            return $fresnsResp->getOrigin();
        }

        $seoData = LanguageHelper::fresnsLanguageSeoDataById('comment', $comment->id, $langTag);

        $item['title'] = $seoData?->title;
        $item['keywords'] = $seoData?->keywords;
        $item['description'] = $seoData?->description;
        $data['items'] = $item;

        $commentConfig = [
            'mapId' => $dtoRequest->mapId,
            'longitude' => $dtoRequest->mapLng,
            'latitude' => $dtoRequest->mapLat,
            'outputSubComments' => false,
            'outputReplyToPost' => true,
            'outputReplyToComment' => true,
            'whetherToFilter' => true,
        ];

        $service = new CommentService();
        $data['detail'] = $service->commentData(
            $comment,
            'detail',
            $langTag,
            $timezone,
            $authUserId,
            $commentConfig['mapId'],
            $commentConfig['longitude'],
            $commentConfig['latitude'],
            $commentConfig['outputSubComments'],
            $commentConfig['outputReplyToPost'],
            $commentConfig['outputReplyToComment'],
            $commentConfig['whetherToFilter'],
        );

        return $this->success($data);
    }

    // interaction
    public function interaction(string $cid, string $type, Request $request)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $comment = Comment::with(['author'])->where('cid', $cid)->first();

        if (empty($comment)) {
            throw new ApiException(37400);
        }

        // check author
        if (empty($comment?->author)) {
            throw new ApiException(35203);
        }

        // check is enabled
        if (! $comment->is_enabled && $comment->user_id != $authUserId) {
            throw new ApiException(37401);
        }

        UserService::checkUserContentViewPerm($comment->created_at, $authUserId);

        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractionDTO($requestData);

        InteractionService::checkInteractionSetting($dtoRequest->type, 'comment');

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $service = new InteractionService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractionService::TYPE_COMMENT, $comment->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactionData']->total(), $data['interactionData']->perPage());
    }

    // commentLogs
    public function commentLogs(string $cid, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $comment = Comment::with(['author'])->where('cid', $cid)->first();

        if (empty($comment)) {
            throw new ApiException(37400);
        }

        // check author
        if (empty($comment?->author)) {
            throw new ApiException(35203);
        }

        // check is enabled
        if (! $comment->is_enabled && $comment->user_id != $authUserId) {
            throw new ApiException(37401);
        }

        UserService::checkUserContentViewPerm($comment->created_at, $authUserId);

        $commentLogs = CommentLog::with(['parentComment', 'post', 'author'])->where('comment_id', $comment->id)->where('state', 3)->latest()->paginate($dtoRequest->pageSize ?? 15);

        $commentLogList = [];
        $service = new CommentService();
        foreach ($commentLogs as $log) {
            $commentLogList[] = $service->commentLogData($log, 'list', $langTag, $timezone, $authUserId);
        }

        return $this->fresnsPaginate($commentLogList, $commentLogs->total(), $commentLogs->perPage());
    }

    // logDetail
    public function logDetail(string $cid, int $logId, Request $request)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $comment = Comment::with(['author'])->where('cid', $cid)->first();

        if (empty($comment)) {
            throw new ApiException(37400);
        }

        // check author
        if (empty($comment?->author)) {
            throw new ApiException(35203);
        }

        // check is enabled
        if (! $comment->is_enabled && $comment->user_id != $authUserId) {
            throw new ApiException(37401);
        }

        UserService::checkUserContentViewPerm($comment->created_at, $authUserId);

        $log = CommentLog::with(['parentComment', 'post', 'author'])->where('comment_id', $comment->id)->where('id', $logId)->where('state', 3)->first();

        if (empty($log)) {
            throw new ApiException(37402);
        }

        $service = new CommentService();
        $data['detail'] = $service->commentLogData($log, 'detail', $langTag, $timezone, $authUserId);

        return $this->success($data);
    }

    // delete
    public function delete(string $cid)
    {
        $comment = Comment::where('cid', $cid)->first();

        if (empty($comment)) {
            throw new ApiException(37400);
        }

        $authUser = $this->user();

        if ($comment->user_id != $authUser->id) {
            throw new ApiException(36403);
        }

        if (! $comment->commentAppend->can_delete) {
            throw new ApiException(36401);
        }

        InteractionUtility::publishStats('comment', $comment->id, 'decrement');

        CommentLog::where('comment_id', $comment->id)->delete();

        $comment->delete();

        return $this->success();
    }

    // follow
    public function follow(string $type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new FollowDTO($requestData);

        // Plugin provides data
        $dataPluginFskey = ConfigHelper::fresnsConfigByItemKey('comment_follow_service');

        if ($dtoRequest->contentType && ! $dataPluginFskey) {
            $dataPluginFskey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'commentByFollow');
        }

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => AppHelper::getHeaders(),
                'body' => $dtoRequest->toArray(),
                'type' => $type,
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getCommentByFollow($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();
        $dateLimit = UserService::getContentDateLimit($authUser->id);

        $followService = new FollowService();

        switch ($dtoRequest->type) {
            case 'all':
                $comments = $followService->getCommentListByFollowAll($authUser->id, $dtoRequest->contentType, $dateLimit);
                break;

            case 'user':
                $comments = $followService->getCommentListByFollowUsers($authUser->id, $dtoRequest->contentType, $dateLimit);
                break;

            case 'group':
                $comments = $followService->getCommentListByFollowGroups($authUser->id, $dtoRequest->contentType, $dateLimit);
                break;

            case 'hashtag':
                $comments = $followService->getCommentListByFollowHashtags($authUser->id, $dtoRequest->contentType, $dateLimit);
                break;
        }

        $commentConfig = [
            'mapId' => $dtoRequest->mapId,
            'longitude' => $dtoRequest->mapLng,
            'latitude' => $dtoRequest->mapLat,
            'outputSubComments' => true,
            'outputReplyToPost' => true,
            'outputReplyToComment' => true,
            'whetherToFilter' => true,
        ];

        $commentList = [];
        $service = new CommentService();
        foreach ($comments as $comment) {
            $listItem = $service->commentData(
                $comment,
                'list',
                $langTag,
                $timezone,
                $authUser->id,
                $commentConfig['mapId'],
                $commentConfig['longitude'],
                $commentConfig['latitude'],
                $commentConfig['outputSubComments'],
                $commentConfig['outputReplyToPost'],
                $commentConfig['outputReplyToComment'],
                $commentConfig['whetherToFilter'],
            );

            $listItem['followType'] = InteractionUtility::getFollowType($comment->user_id, $authUser?->id, $comment?->group_id, $comment?->hashtags?->toArray());

            $commentList[] = $listItem;
        }

        return $this->fresnsPaginate($commentList, $comments->total(), $comments->perPage());
    }

    // nearby
    public function nearby(Request $request)
    {
        $dtoRequest = new NearbyDTO($request->all());

        // Plugin provides data
        $dataPluginFskey = ConfigHelper::fresnsConfigByItemKey('comment_nearby_service');

        if ($dtoRequest->contentType && ! $dataPluginFskey) {
            $dataPluginFskey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'commentByNearby');
        }

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => AppHelper::getHeaders(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getCommentByNearby($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $nearbyConfig = ConfigHelper::fresnsConfigByItemKeys([
            'nearby_length_km',
            'nearby_length_mi',
        ]);

        $unit = $dtoRequest->unit ?? ConfigHelper::fresnsConfigLengthUnit($langTag);
        $length = $dtoRequest->length ?? $nearbyConfig["nearby_length_{$unit}"];

        $nearbyLength = match ($unit) {
            'km' => $length,
            'mi' => $length * 0.6214,
            default => $length,
        };

        $comments = Comment::query()
            ->select(DB::raw("*, ( 6371 * acos( cos( radians($dtoRequest->mapLat) ) * cos( radians( map_latitude ) ) * cos( radians( map_longitude ) - radians($dtoRequest->mapLng) ) + sin( radians($dtoRequest->mapLat) ) * sin( radians( map_latitude ) ) ) ) AS distance"))
            ->having('distance', '<=', $nearbyLength)
            ->orderBy('distance')
            ->paginate($dtoRequest->pageSize ?? 15);

        $commentConfig = [
            'mapId' => $dtoRequest->mapId,
            'longitude' => $dtoRequest->mapLng,
            'latitude' => $dtoRequest->mapLat,
            'outputSubComments' => true,
            'outputReplyToPost' => true,
            'outputReplyToComment' => true,
            'whetherToFilter' => true,
        ];

        $commentList = [];
        $service = new CommentService();
        foreach ($comments as $comment) {
            $commentList[] = $service->commentData(
                $comment,
                'list',
                $langTag,
                $timezone,
                $authUser?->id,
                $commentConfig['mapId'],
                $commentConfig['longitude'],
                $commentConfig['latitude'],
                $commentConfig['outputSubComments'],
                $commentConfig['outputReplyToPost'],
                $commentConfig['outputReplyToComment'],
                $commentConfig['whetherToFilter'],
            );
        }

        return $this->fresnsPaginate($commentList, $comments->total(), $comments->perPage());
    }
}
