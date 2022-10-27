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
use App\Fresns\Api\Http\DTO\InteractiveDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
use App\Fresns\Api\Services\CommentService;
use App\Fresns\Api\Services\InteractiveService;
use App\Fresns\Api\Services\UserService;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Models\Seo;
use App\Utilities\ConfigUtility;
use App\Utilities\InteractiveUtility;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new CommentListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $commentQuery = Comment::with(['creator', 'post', 'hashtags'])->isEnable();

        $blockGroupIds = InteractiveUtility::getPrivateGroupIdArr();

        if ($authUserId) {
            $blockPostIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_POST, $authUserId);
            $blockCommentIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_COMMENT, $authUserId);
            $blockUserIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_USER, $authUserId);
            $blockGroupIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);
            $blockHashtagIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

            $commentQuery->when($blockCommentIds, function ($query, $value) {
                $query->whereNotIn('id', $value);
            });

            if ($blockPostIds) {
                $commentQuery->where(function ($commentQuery) use ($blockPostIds) {
                    $commentQuery->whereHas('post', function ($query) use ($blockPostIds) {
                        $query->whereNotIn('id', $blockPostIds);
                    });
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
        }

        $commentQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereHas('post', function ($query) use ($value) {
                $query->whereNotIn('group_id', $value);
            });
        });

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
                    return $this->failure(
                        32203,
                        ConfigUtility::getCodeMessage(32203, 'Fresns', $langTag),
                        [
                            'paginate' => [
                                'total' => 0,
                                'pageSize' => 0,
                                'currentPage' => 1,
                                'lastPage' => 1,
                            ],
                            'list' => [],
                        ],
                    );
                }
            }

            $commentQuery->where('post_id', $viewPost->id);
        }

        if ($dtoRequest->cid) {
            $viewComment = PrimaryHelper::fresnsModelByFsid('comment', $dtoRequest->cid);

            if (empty($viewComment) || $viewComment->trashed()) {
                throw new ApiException(37400);
            }

            if ($viewComment->is_enable == 0) {
                throw new ApiException(37401);
            }

            $commentQuery->where('top_parent_id', $viewComment->id);
        } else {
            $commentQuery->whereNull('top_parent_id');
        }

        if ($dtoRequest->gid) {
            $viewGroup = PrimaryHelper::fresnsModelByFsid('group', $dtoRequest->gid);

            if (empty($viewGroup) || $viewGroup->trashed()) {
                throw new ApiException(37100);
            }

            if ($viewGroup->is_enable == 0) {
                throw new ApiException(37101);
            }

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

        $commentQuery->when($dtoRequest->sticky, function ($query, $value) {
            $query->where('is_sticky', $value);
        });

        $commentQuery->when($dtoRequest->digestState, function ($query, $value) {
            $query->where('digest_state', $value);
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

        if ($dtoRequest->contentType && $dtoRequest->contentType != 'all') {
            $contentType = $dtoRequest->contentType;

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

        $dateLimit = $this->userContentViewPerm()['dateLimit'];
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
            if (empty($comment->post) || empty($comment->postAppend)) {
                continue;
            }

            $commentList[] = $service->commentData($comment, 'list', $langTag, $timezone, $authUserId, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);
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

        $comment = Comment::with(['creator', 'hashtags'])->where('cid', $cid)->first();

        if (empty($comment)) {
            throw new ApiException(37400);
        }

        if ($comment->is_enable == 0) {
            throw new ApiException(37401);
        }

        UserService::checkUserContentViewPerm($comment->created_at, $authUserId);

        $seoData = Seo::where('usage_type', Seo::TYPE_COMMENT)->where('usage_id', $comment->id)->where('lang_tag', $langTag)->first();

        $item['title'] = $seoData->title ?? null;
        $item['keywords'] = $seoData->keywords ?? null;
        $item['description'] = $seoData->description ?? null;
        $data['items'] = $item;

        $service = new CommentService();
        $data['detail'] = $service->commentData($comment, 'detail', $langTag, $timezone, $authUserId, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);

        return $this->success($data);
    }

    // interactive
    public function interactive(string $cid, string $type, Request $request)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $comment = Comment::where('cid', $cid)->isEnable()->first();

        if (empty($comment)) {
            throw new ApiException(37400);
        }

        UserService::checkUserContentViewPerm($comment->created_at, $authUserId);

        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractiveDTO($requestData);

        InteractiveService::checkInteractiveSetting($dtoRequest->type, 'comment');

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $service = new InteractiveService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractiveService::TYPE_COMMENT, $comment->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactiveData']->total(), $data['interactiveData']->perPage());
    }

    // commentLogs
    public function commentLogs(string $cid, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $comment = Comment::where('cid', $cid)->isEnable()->first();

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

        $comment = Comment::where('cid', $cid)->isEnable()->first();

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
        $comment = Comment::where('cid', $cid)->isEnable()->first();

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

        CommentLog::where('comment_id', $comment->id)->delete();
        $comment->delete();

        return $this->success();
    }
}
