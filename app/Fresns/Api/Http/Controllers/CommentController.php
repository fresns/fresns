<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
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
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\LbsUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new CommentListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $commentQuery = Comment::with(['post', 'hashtags']);

        $blockGroupIds = InteractionUtility::getPrivateGroupIdArr();

        if ($authUserId) {
            $commentQuery->where('is_enable', 1)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enable', 0)->where('user_id', $authUserId);
            });

            $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);
            $blockCommentIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_COMMENT, $authUserId);
            $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
            $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
            $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);

            $commentQuery->when($blockCommentIds, function ($query, $value) {
                $query->whereNotIn('id', $value);
            });

            if ($blockPostIds) {
                $commentQuery->when($blockPostIds, function ($query, $value) {
                    $query->whereNotIn('post_id', $value);
                });
            }

            $commentQuery->when($blockUserIds, function ($query, $value) {
                $query->whereNotIn('user_id', $value);
            });

            if ($blockHashtagIds) {
                $commentQuery->where(function ($commentQuery) use ($blockHashtagIds) {
                    $commentQuery->whereDoesntHave('hashtags')->orWhereHas('hashtags', function ($query) use ($blockHashtagIds) {
                        $query->whereNotIn('hashtag_id', $blockHashtagIds);
                    });
                });
            }
        } else {
            $commentQuery->where('is_enable', 1);
        }

        $commentQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereHas('post', function ($query) use ($value) {
                $query->whereNotIn('group_id', $value);
            });
        });

        $isPreviewPost = true;

        if ($dtoRequest->uidOrUsername) {
            $commentConfig = ConfigHelper::fresnsConfigByItemKey('it_comments');
            if (! $commentConfig) {
                throw new ApiException(35305);
            }

            $viewUser = PrimaryHelper::fresnsModelByFsid('user', $dtoRequest->uidOrUsername);

            if (empty($viewUser) || $viewUser->trashed()) {
                throw new ApiException(31602);
            }

            if ($viewUser->is_enable == 0) {
                throw new ApiException(35202);
            }

            if ($viewUser->wait_delete == 1) {
                throw new ApiException(35203);
            }

            $commentQuery->where('user_id', $viewUser->id)->where('is_anonymous', 0);
        }

        if ($dtoRequest->pid) {
            $viewPost = PrimaryHelper::fresnsModelByFsid('post', $dtoRequest->pid);

            if (empty($viewPost) || $viewPost->trashed()) {
                throw new ApiException(37300);
            }

            if ($viewPost->is_enable == 0) {
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

            $isPreviewPost = false;
        }

        $outputSubComments = true;
        if ($dtoRequest->cid) {
            $viewComment = PrimaryHelper::fresnsModelByFsid('comment', $dtoRequest->cid);

            if (empty($viewComment) || $viewComment->trashed()) {
                throw new ApiException(37400);
            }

            if ($viewComment->is_enable == 0) {
                throw new ApiException(37401);
            }

            if ($viewComment->top_parent_id) {
                $commentQuery->where('parent_id', $viewComment->id);
            } else {
                $commentQuery->where('top_parent_id', $viewComment->id);
            }

            $outputSubComments = false;
            $isPreviewPost = false;
        }

        $groupDateLimit = null;
        if ($dtoRequest->gid) {
            $viewGroup = PrimaryHelper::fresnsModelByFsid('group', $dtoRequest->gid);

            if (empty($viewGroup) || $viewGroup->trashed()) {
                throw new ApiException(37100);
            }

            if ($viewGroup->is_enable == 0) {
                throw new ApiException(37101);
            }

            // group mode
            $groupDateLimit = GroupService::getGroupContentDateLimit($viewGroup->id, $authUserId);

            $commentQuery->when($viewGroup->id, function ($query, $value) {
                $query->whereHas('post', function ($query) use ($value) {
                    $query->where('group_id', $value);
                });
            });
        }

        if ($dtoRequest->hid) {
            $hid = StrHelper::slug($dtoRequest->hid);
            $viewHashtag = PrimaryHelper::fresnsModelByFsid('hashtag', $hid);

            if (empty($viewHashtag)) {
                throw new ApiException(37200);
            }

            if ($viewHashtag->is_enable == 0) {
                throw new ApiException(37201);
            }

            $commentQuery->when($viewHashtag->id, function ($query, $value) {
                $query->whereHas('hashtags', function ($query) use ($value) {
                    $query->where('hashtag_id', $value);
                });
            });
        }

        if ($dtoRequest->allDigest) {
            $commentQuery->whereIn('digest_state', [2, 3]);
        } else {
            $commentQuery->when($dtoRequest->digestState, function ($query, $value) {
                $query->where('digest_state', $value);
            });
        }

        $commentQuery->when($dtoRequest->sticky, function ($query, $value) {
            $query->where('is_sticky', $value);
        });

        $commentQuery->when($dtoRequest->following, function ($query) use ($authUserId) {
            $followUserIds = InteractionUtility::getFollowIdArr(InteractionUtility::TYPE_USER, $authUserId);

            $query->whereIn('user_id', $followUserIds)->where('is_anonymous', 0);
        });

        $commentQuery->when($dtoRequest->createDateGt, function ($query, $value) {
            $query->whereDate('created_at', '>=', $value);
        });

        $commentQuery->when($dtoRequest->createDateLt, function ($query, $value) {
            $query->whereDate('created_at', '<=', $value);
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
                    $query->where('plugin_unikey', $contentType);
                });
            }
        }

        $dateLimit = $groupDateLimit ?? UserService::getContentDateLimit($authUserId);
        $commentQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        $orderType = match ($dtoRequest->orderType) {
            default => 'created_at',
            'createDate' => 'created_at',
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

        $comments = $commentQuery->paginate($request->get('pageSize', 15));

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

            $commentList[] = $service->commentData($comment, 'list', $langTag, $timezone, $isPreviewPost, $authUserId, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat, $outputSubComments);
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

        $comment = Comment::where('cid', $cid)->first();

        if (empty($comment)) {
            throw new ApiException(37400);
        }

        if ($comment->is_enable == 0 && $comment->user_id != $authUserId) {
            throw new ApiException(37401);
        }

        UserService::checkUserContentViewPerm($comment->created_at, $authUserId);
        GroupService::checkGroupContentViewPerm($comment->created_at, $comment?->post->group_id, $authUserId);

        $seoData = LanguageHelper::fresnsLanguageSeoDataById('comment', $comment->id, $langTag);

        $item['title'] = $seoData?->title;
        $item['keywords'] = $seoData?->keywords;
        $item['description'] = $seoData?->description;
        $data['items'] = $item;

        $service = new CommentService();
        $data['detail'] = $service->commentData($comment, 'detail', $langTag, $timezone, true, $authUserId, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);

        return $this->success($data);
    }

    // interaction
    public function interaction(string $cid, string $type, Request $request)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        if ($authUserId) {
            $comment = Comment::where('cid', $cid)->where('is_enable', 1)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enable', 0)->where('user_id', $authUserId);
            })->first();
        } else {
            $comment = Comment::where('cid', $cid)->isEnable()->first();
        }

        if (empty($comment)) {
            throw new ApiException(37400);
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

        if ($authUserId) {
            $comment = Comment::where('cid', $cid)->where('is_enable', 1)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enable', 0)->where('user_id', $authUserId);
            })->first();
        } else {
            $comment = Comment::where('cid', $cid)->isEnable()->first();
        }

        if (empty($comment)) {
            throw new ApiException(37400);
        }

        UserService::checkUserContentViewPerm($comment->created_at, $authUserId);

        $commentLogs = CommentLog::with('creator')->where('comment_id', $comment->id)->where('state', 3)->latest()->paginate($request->get('pageSize', 15));

        $commentLogList = [];
        $service = new CommentService();
        foreach ($commentLogs as $log) {
            $commentLogList[] = $service->commentLogData($log, 'list', $langTag, $timezone);
        }

        return $this->fresnsPaginate($commentLogList, $commentLogs->total(), $commentLogs->perPage());
    }

    // logDetail
    public function logDetail(string $cid, int $logId, Request $request)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        if ($authUserId) {
            $comment = Comment::where('cid', $cid)->where('is_enable', 1)->orWhere(function ($query) use ($authUserId) {
                $query->where('is_enable', 0)->where('user_id', $authUserId);
            })->first();
        } else {
            $comment = Comment::where('cid', $cid)->isEnable()->first();
        }

        if (empty($comment)) {
            throw new ApiException(37400);
        }

        UserService::checkUserContentViewPerm($comment->created_at, $authUserId);

        $log = CommentLog::where('comment_id', $comment->id)->where('id', $logId)->where('state', 3)->first();

        if (empty($log)) {
            throw new ApiException(37402);
        }

        $service = new CommentService();
        $data['detail'] = $service->commentLogData($log, 'detail', $langTag, $timezone);

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
        $dataPluginUnikey = ConfigHelper::fresnsConfigByItemKey('content_follow_service');

        if ($dtoRequest->contentType && ! $dataPluginUnikey) {
            $dataPluginUnikey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'commentByFollow');
        }

        if ($dataPluginUnikey) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginUnikey)->getCommentByAll($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();
        $dateLimit = UserService::getContentDateLimit($authUser->id);

        $followService = new FollowService();

        switch ($dtoRequest->type) {
            // all
            case 'all':
                $comments = $followService->getCommentListByFollowAll($authUser->id, $dtoRequest->contentType, $dateLimit);
            break;

            // user
            case 'user':
                $comments = $followService->getCommentListByFollowUsers($authUser->id, $dtoRequest->contentType, $dateLimit);
            break;

            // group
            case 'group':
                $comments = $followService->getCommentListByFollowGroups($authUser->id, $dtoRequest->contentType, $dateLimit);
            break;

            // hashtag
            case 'hashtag':
                $comments = $followService->getCommentListByFollowHashtags($authUser->id, $dtoRequest->contentType, $dateLimit);
            break;
        }

        $commentList = [];
        $service = new CommentService();
        foreach ($comments as $comment) {
            $listItem = $service->commentData($comment, 'list', $langTag, $timezone, true, $authUser->id, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);
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
        $dataPluginUnikey = ConfigHelper::fresnsConfigByItemKey('content_nearby_service');

        if ($dtoRequest->contentType && ! $dataPluginUnikey) {
            $dataPluginUnikey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'commentByNearby');
        }

        if ($dataPluginUnikey) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginUnikey)->getCommentByNearby($wordBody);

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
            ->select([
                DB::raw('*'),
                DB::raw(LbsUtility::getDistanceSql('map_longitude', 'map_latitude', $dtoRequest->mapLng, $dtoRequest->mapLat)),
            ])
            ->having('distance', '<=', $nearbyLength)
            ->orderBy('distance')
            ->paginate();

        $commentList = [];
        $service = new CommentService();
        foreach ($comments as $comment) {
            $commentList[] = $service->commentData($comment, 'list', $langTag, $timezone, true, $authUser?->id, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);
        }

        return $this->fresnsPaginate($commentList, $comments->total(), $comments->perPage());
    }
}
