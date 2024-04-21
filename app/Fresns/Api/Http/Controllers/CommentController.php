<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Exceptions\ResponseException;
use App\Fresns\Api\Http\DTO\CommentDetailDTO;
use App\Fresns\Api\Http\DTO\CommentListDTO;
use App\Fresns\Api\Http\DTO\CommentNearbyDTO;
use App\Fresns\Api\Http\DTO\CommentTimelinesDTO;
use App\Fresns\Api\Http\DTO\HistoryDTO;
use App\Fresns\Api\Http\DTO\InteractionDTO;
use App\Fresns\Api\Services\ContentService;
use App\Fresns\Api\Services\InteractionService;
use App\Fresns\Api\Services\TimelineService;
use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Models\Seo;
use App\Models\SessionLog;
use App\Utilities\DetailUtility;
use App\Utilities\InteractionUtility;
use App\Utilities\PermissionUtility;
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

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => AppHelper::getHeaders(),
                'body' => $request->all(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getComments($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();
        $authUserId = $authUser?->id;

        $commentOptions = [
            'viewType' => 'list',
            'contentFormat' => \request()->header('X-Fresns-Client-Content-Format'),
            'location' => [
                'mapId' => $dtoRequest->mapId,
                'longitude' => $dtoRequest->mapLng,
                'latitude' => $dtoRequest->mapLat,
            ],
            'checkPermissions' => true,
            'isPreviewLikeUsers' => true,
            'isPreviewComments' => $isPreviewComments,
            'outputReplyToPost' => $outputReplyToPost,
            'outputReplyToComment' => $outputReplyToComment,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
            'filterHashtag' => [
                'type' => $dtoRequest->filterHashtagType,
                'keys' => $dtoRequest->filterHashtagKeys,
            ],
            'filterGeotag' => [
                'type' => $dtoRequest->filterGeotagType,
                'keys' => $dtoRequest->filterGeotagKeys,
            ],
            'filterAuthor' => [
                'type' => $dtoRequest->filterAuthorType,
                'keys' => $dtoRequest->filterAuthorKeys,
            ],
            'filterPreviewLikeUser' => [
                'type' => $dtoRequest->filterPreviewLikeUserType,
                'keys' => $dtoRequest->filterPreviewLikeUserKeys,
            ],
            'filterPreviewComment' => [
                'type' => $dtoRequest->filterPreviewCommentType,
                'keys' => $dtoRequest->filterPreviewCommentKeys,
            ],
            'filterReplyToPost' => [
                'type' => $dtoRequest->filterReplyToPostType,
                'keys' => $dtoRequest->filterReplyToPostKeys,
            ],
            'filterReplyToComment' => [
                'type' => $dtoRequest->filterReplyToCommentType,
                'keys' => $dtoRequest->filterReplyToCommentKeys,
            ],
        ];

        // cache
        $listCrc32 = crc32(json_encode($request->all()));
        $cacheKey = "fresns_api_group_list_{$listCrc32}_guest";

        $comments = CacheHelper::get($cacheKey, 'fresnsList');

        if (empty($authUserId) && $comments) {
            $commentList = [];
            foreach ($comments as $comment) {
                $commentList[] = DetailUtility::commentDetail($comment, $langTag, $timezone, $authUserId, $commentOptions);
            }

            return $this->fresnsPaginate($commentList, $comments->total(), $comments->perPage());
        }

        // query
        $commentQuery = Comment::query();

        // has author
        $commentQuery->whereRelation('author', 'is_enabled', true);

        // block
        $blockGroupIds = InteractionUtility::explodeIdArr('user', $dtoRequest->blockGroups);
        $privateGroupIds = PermissionUtility::getGroupContentFilterIdArr($authUserId);

        $filterUserIds = InteractionUtility::explodeIdArr('user', $dtoRequest->blockUsers);
        $filterGroupIds = array_unique(array_merge($blockGroupIds, $privateGroupIds));
        $filterHashtagIds = InteractionUtility::explodeIdArr('user', $dtoRequest->blockHashtags);
        $filterGeotagIds = InteractionUtility::explodeIdArr('user', $dtoRequest->blockGeotags);
        $filterPostIds = InteractionUtility::explodeIdArr('user', $dtoRequest->blockPosts);
        $filterCommentIds = InteractionUtility::explodeIdArr('user', $dtoRequest->blockComments);

        if (empty($authUserId)) {
            $commentQuery->where('is_enabled', true);
        } else {
            $commentQuery->where(function ($query) use ($authUserId) {
                $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                    $query->where('is_enabled', false)->where('user_id', $authUserId);
                });
            });

            $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
            $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
            $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
            $blockGeotagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GEOTAG, $authUserId);
            $blockCommentIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_COMMENT, $authUserId);

            if (! $dtoRequest->pid) {
                $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);

                $filterPostIds = array_unique(array_merge($filterPostIds, $blockPostIds));
            }

            $filterUserIds = array_unique(array_merge($filterUserIds, $blockUserIds));
            $filterGroupIds = array_unique(array_merge($filterGroupIds, $blockGroupIds));
            $filterHashtagIds = array_unique(array_merge($filterHashtagIds, $blockHashtagIds));
            $filterGeotagIds = array_unique(array_merge($filterGeotagIds, $blockGeotagIds));
            $filterCommentIds = array_unique(array_merge($filterCommentIds, $blockCommentIds));
        }

        $commentQuery->when($filterUserIds, function ($query, $value) {
            $query->whereNotIn('user_id', $value);
        });

        $commentQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereDoesntHave('post', function ($query) use ($value) {
                $query->whereNotIn('group_id', $value);
            });
        });

        $commentQuery->when($filterHashtagIds, function ($query, $value) {
            $query->where(function ($postQuery) use ($value) {
                $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                    $query->whereNotIn('hashtag_id', $value);
                });
            });
        });

        $commentQuery->when($filterGeotagIds, function ($query, $value) {
            $query->whereNotIn('geotag_id', $value);
        });

        $commentQuery->when($filterPostIds, function ($query, $value) {
            $query->whereNotIn('post_id', $value);
        });

        $commentQuery->when($filterCommentIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        // options
        $isPreviewComments = true;
        $outputReplyToPost = true;
        $outputReplyToComment = false;

        // post
        if ($dtoRequest->pid) {
            $viewPost = PrimaryHelper::fresnsModelByFsid('post', $dtoRequest->pid);

            if (empty($viewPost) || $viewPost->trashed()) {
                throw new ResponseException(37400);
            }

            if (! $viewPost->is_enabled && $viewPost->user_id != $authUserId) {
                throw new ResponseException(37401);
            }

            $commentVisibilityRule = ConfigHelper::fresnsConfigByItemKey('comment_visibility_rule');
            if ($commentVisibilityRule > 0) {
                $visibilityTime = $viewPost->created_at->addDay($commentVisibilityRule);

                if ($visibilityTime->lt(now())) {
                    return $this->warning(37505);
                }
            }

            $commentQuery->where('post_id', $viewPost->id)->where('top_parent_id', 0);

            // option
            $outputReplyToPost = false;
        } else {
            $commentQuery->whereRelation('post', 'is_enabled', true);
            $commentQuery->where('privacy_state', Comment::PRIVACY_PUBLIC);
        }

        // comment
        if ($dtoRequest->cid) {
            $viewComment = PrimaryHelper::fresnsModelByFsid('comment', $dtoRequest->cid);

            if (empty($viewComment) || $viewComment->trashed()) {
                throw new ResponseException(37500);
            }

            if (! $viewComment->is_enabled) {
                throw new ResponseException(37501);
            }

            if ($viewComment->top_parent_id) {
                $commentQuery->where('parent_id', $viewComment->id);
            } else {
                $commentQuery->where('top_parent_id', $viewComment->id);
            }

            $isPreviewComments = false;
            $outputReplyToPost = false;
            $outputReplyToComment = true;
        }

        // cache tag
        $cacheTag = 'fresnsConfigs';

        // users
        if ($dtoRequest->users) {
            $profileCommentsEnabled = ConfigHelper::fresnsConfigByItemKey('profile_comments_enabled');
            if (! $profileCommentsEnabled) {
                throw new ResponseException(35305);
            }

            $crc32 = crc32($dtoRequest->users);
            $cacheKey = "fresns_api_list_{$crc32}_user_ids";

            $userExplodeArr = CacheHelper::get($cacheKey, $cacheTag);

            if (empty($userExplodeArr)) {
                $userExplodeArr = PermissionUtility::getPrimaryIdArr('user', $dtoRequest->users);

                CacheHelper::put($userExplodeArr, $cacheKey, $cacheTag);
            }

            if ($userExplodeArr['idCount'] == 0) {
                return $this->warning(35400);
            }

            $commentQuery->whereIn('user_id', $userExplodeArr['idArr'])->where('is_anonymous', false);
        }

        // groups
        $groupDateLimit = null;
        if ($dtoRequest->groups) {
            $crc32Text = $dtoRequest->groups.$dtoRequest->includeSubgroups.$authUserId;
            $crc32 = crc32($crc32Text);
            $cacheKey = "fresns_api_list_{$crc32}_group_ids";

            $groupExplodeArr = CacheHelper::get($cacheKey, $cacheTag);

            if (empty($groupExplodeArr)) {
                $groupExplodeArr = PermissionUtility::getPrimaryIdArr('group', $dtoRequest->groups, $authUserId, $dtoRequest->includeSubgroups);

                CacheHelper::put($groupExplodeArr, $cacheKey, $cacheTag);
            }

            if ($groupExplodeArr['idCount'] == 0) {
                return $this->warning(37102);
            }

            $groupDateLimit = $groupExplodeArr['datetime'];

            $viewGroupIdArr = $groupExplodeArr['idArr'];

            $commentQuery->whereDoesntHave('post', function ($query) use ($viewGroupIdArr) {
                $query->whereIn('group_id', $viewGroupIdArr);
            });
        }

        // hashtags
        if ($dtoRequest->hashtags) {
            $crc32 = crc32($dtoRequest->hashtags);
            $cacheKey = "fresns_api_list_{$crc32}_hashtag_ids";

            $hashtagExplodeArr = CacheHelper::get($cacheKey, $cacheTag);

            if (empty($hashtagExplodeArr)) {
                $hashtagExplodeArr = PermissionUtility::getPrimaryIdArr('hashtag', $dtoRequest->hashtags);

                CacheHelper::put($hashtagExplodeArr, $cacheKey, $cacheTag);
            }

            if ($hashtagExplodeArr['idCount'] == 0) {
                return $this->warning(37202);
            }

            $viewHashtagIdArr = $hashtagExplodeArr['idArr'];

            $commentQuery->whereHas('hashtagUsages', function ($query) use ($viewHashtagIdArr) {
                $query->whereIn('hashtag_id', $viewHashtagIdArr);
            });
        }

        // geotags
        if ($dtoRequest->geotags) {
            $crc32 = crc32($dtoRequest->geotags);
            $cacheKey = "fresns_api_list_{$crc32}_geotag_ids";

            $geotagExplodeArr = CacheHelper::get($cacheKey, $cacheTag);

            if (empty($geotagExplodeArr)) {
                $geotagExplodeArr = PermissionUtility::getPrimaryIdArr('geotag', $dtoRequest->geotags);

                CacheHelper::put($geotagExplodeArr, $cacheKey, $cacheTag);
            }

            if ($geotagExplodeArr['idCount'] == 0) {
                return $this->warning(37302);
            }

            $commentQuery->whereIn('geotag_id', $geotagExplodeArr['idArr']);
        }

        // other conditions
        if ($dtoRequest->allDigest) {
            $commentQuery->whereNot('digest_state', Comment::DIGEST_NO);
        } else {
            $commentQuery->when($dtoRequest->digestState, function ($query, $value) {
                $query->where('digest_state', $value);
            });
        }

        $commentQuery->when($dtoRequest->sticky, function ($query, $value) {
            $query->where('is_sticky', $value);
        });

        if ($dtoRequest->createdDays || $dtoRequest->createdDate) {
            switch ($dtoRequest->createdDate) {
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

                default:
                    $commentQuery->whereDate('created_at', '>=', now()->subDays($dtoRequest->createdDays ?? 1)->format('Y-m-d'));
            }
        } else {
            $commentQuery->when($dtoRequest->createdDateGt, function ($query, $value) {
                $query->whereDate('created_at', '>=', $value);
            });

            $commentQuery->when($dtoRequest->createdDateLt, function ($query, $value) {
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

        $commentQuery->when($dtoRequest->commentCountLt, function ($query, $value) {
            $query->where('comment_count', '<=', $value);
        });

        // since comment
        $commentQuery->when($dtoRequest->sinceCid, function ($query, $value) {
            $sinceCommentId = PrimaryHelper::fresnsPrimaryId('comment', $value);

            $query->where('id', '>', $sinceCommentId);
        });

        // before comment
        $commentQuery->when($dtoRequest->beforeCid, function ($query, $value) {
            $beforeCommentId = PrimaryHelper::fresnsPrimaryId('comment', $value);

            $query->where('id', '<', $beforeCommentId);
        });

        // lang tag
        $commentQuery->when($dtoRequest->langTag, function ($query, $value) {
            $query->where('lang_tag', $value);
        });

        // content type
        if ($dtoRequest->contentType && $dtoRequest->contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($dtoRequest->contentType);

            $commentQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($dtoRequest->contentType == 'Text') {
                $commentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $commentQuery->whereRelation('extendUsages', 'app_fskey', $dtoRequest->contentType);
            }
        }

        // datetime limit
        $dateLimit = $groupDateLimit ?? ContentService::getContentDateLimit($authUserId, $authUser?->expired_at);
        $commentQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        // order
        if ($dtoRequest->orderType == 'random') {
            $commentQuery->inRandomOrder();
        } else {
            $orderType = match ($dtoRequest->orderType) {
                default => 'created_at',
                'createdTime' => 'created_at',
                'commentTime' => 'last_comment_at',
                'view' => 'view_count',
                'like' => 'like_count',
                'dislike' => 'dislike_count',
                'follow' => 'follow_count',
                'block' => 'block_count',
                'comment' => 'comment_count',
            };

            $orderDirection = match ($dtoRequest->orderDirection) {
                'asc' => 'asc',
                'desc' => 'desc',
                default => 'desc',
            };

            if ($dtoRequest->orderType == 'commentTime') {
                $commentQuery->orderBy(DB::raw('COALESCE(last_comment_at, created_at)'), $orderDirection);
            } else {
                $commentQuery->orderBy($orderType, $orderDirection);
            }
        }

        $comments = $commentQuery->paginate($dtoRequest->pageSize ?? 15);

        if (empty($authUserId)) {
            CacheHelper::put($comments, $cacheKey, 'fresnsList', 10);
        }

        $commentList = [];
        foreach ($comments as $comment) {
            $commentList[] = DetailUtility::commentDetail($comment, $langTag, $timezone, $authUserId, $commentOptions);
        }

        return $this->fresnsPaginate($commentList, $comments->total(), $comments->perPage());
    }

    // detail
    public function detail(string $cid, Request $request)
    {
        $dtoRequest = new CommentDetailDTO($request->all());

        // Plugin provides data
        $dataPluginFskey = ConfigHelper::fresnsConfigByItemKey('comment_detail_service');

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => AppHelper::getHeaders(),
                'body' => $request->all(),
                'fsid' => $cid,
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getCommentDetail($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $comment = Comment::with(['post', 'author'])->where('cid', $cid)->first();

        if (empty($comment)) {
            throw new ResponseException(37500);
        }

        // check post
        if (empty($comment->post)) {
            throw new ResponseException(37400);
        }

        // check author
        if (empty($comment->author)) {
            throw new ResponseException(35203);
        }

        // check is enabled
        if (! $comment->is_enabled && $comment->user_id != $authUser?->id) {
            throw new ResponseException(37501);
        }

        ContentService::checkUserContentViewPerm($comment->created_at, $authUser?->id, $authUser?->expired_at);

        ContentService::checkGroupContentViewPerm($comment->created_at, $comment->post->group_id, $authUser?->id);

        // comment_visibility_rule
        $visibilityRule = ConfigHelper::fresnsConfigByItemKey('comment_visibility_rule');
        if ($visibilityRule > 0) {
            $visibilityTime = $comment->post->created_at->addDay($visibilityRule);

            if ($visibilityTime->gt(now())) {
                throw new ResponseException(37505);
            }
        }

        $seoData = PrimaryHelper::fresnsModelSeo(Seo::TYPE_COMMENT, $comment->id);

        $item['title'] = StrHelper::languageContent($seoData?->title, $langTag);
        $item['keywords'] = StrHelper::languageContent($seoData?->keywords, $langTag);
        $item['description'] = StrHelper::languageContent($seoData?->description, $langTag);

        $commentOptions = [
            'viewType' => 'detail',
            'contentFormat' => \request()->header('X-Fresns-Client-Content-Format'),
            'location' => [
                'mapId' => $dtoRequest->mapId,
                'longitude' => $dtoRequest->mapLng,
                'latitude' => $dtoRequest->mapLat,
            ],
            'checkPermissions' => true,
            'isPreviewLikeUsers' => true,
            'isPreviewComments' => true,
            'outputReplyToPost' => true,
            'outputReplyToComment' => true,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
            'filterHashtag' => [
                'type' => $dtoRequest->filterHashtagType,
                'keys' => $dtoRequest->filterHashtagKeys,
            ],
            'filterGeotag' => [
                'type' => $dtoRequest->filterGeotagType,
                'keys' => $dtoRequest->filterGeotagKeys,
            ],
            'filterAuthor' => [
                'type' => $dtoRequest->filterAuthorType,
                'keys' => $dtoRequest->filterAuthorKeys,
            ],
            'filterPreviewLikeUser' => [
                'type' => $dtoRequest->filterPreviewLikeUserType,
                'keys' => $dtoRequest->filterPreviewLikeUserKeys,
            ],
            'filterPreviewComment' => [
                'type' => $dtoRequest->filterPreviewCommentType,
                'keys' => $dtoRequest->filterPreviewCommentKeys,
            ],
            'filterReplyToPost' => [
                'type' => $dtoRequest->filterReplyToPostType,
                'keys' => $dtoRequest->filterReplyToPostKeys,
            ],
            'filterReplyToComment' => [
                'type' => $dtoRequest->filterReplyToCommentType,
                'keys' => $dtoRequest->filterReplyToCommentKeys,
            ],
        ];

        $data = [
            'items' => $item,
            'detail' => DetailUtility::commentDetail($comment, $langTag, $timezone, $authUser?->id, $commentOptions),
        ];

        return $this->success($data);
    }

    // interaction
    public function interaction(string $cid, string $type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractionDTO($requestData);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $comment = PrimaryHelper::fresnsModelByFsid('comment', $cid);

        if (empty($comment)) {
            throw new ResponseException(37500);
        }

        // check post
        if (empty($comment->post)) {
            throw new ResponseException(37400);
        }

        // check author
        if (empty($comment->author)) {
            throw new ResponseException(35203);
        }

        // check is enabled
        if (! $comment->is_enabled && $comment->user_id != $authUser?->id) {
            throw new ResponseException(37501);
        }

        InteractionService::checkInteractionSetting('comment', $dtoRequest->type);

        ContentService::checkUserContentViewPerm($comment->created_at, $authUser?->id, $authUser?->expired_at);

        ContentService::checkGroupContentViewPerm($comment->created_at, $comment->post->group_id, $authUser?->id);

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $service = new InteractionService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractionService::TYPE_COMMENT, $comment->id, $orderDirection, $langTag, $timezone, $authUser?->id);

        return $this->fresnsPaginate($data['paginateData'], $data['interactionData']->total(), $data['interactionData']->perPage());
    }

    // delete
    public function delete(string $cid)
    {
        $comment = Comment::where('cid', $cid)->first();

        if (empty($comment)) {
            throw new ResponseException(37500);
        }

        $authUser = $this->user();

        if ($comment->user_id != $authUser->id) {
            throw new ResponseException(36403);
        }

        $canDelete = PermissionUtility::checkContentIsCanDelete('comment', $comment->digest_state, $comment->is_sticky);

        $permissions = $comment->permissions;
        $canDeleteConfig = $permissions['canDelete'] ?? true;

        if (! $canDeleteConfig || ! $canDelete) {
            throw new ResponseException(36401);
        }

        InteractionUtility::publishStats('comment', $comment->id, 'decrement');

        CommentLog::where('comment_id', $comment->id)->delete();

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_POST_DELETE,
            'fskey' => 'Fresns',
            'appId' => $this->appId(),
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => $this->account()->aid,
            'uid' => $authUser->uid,
            'actionName' => \request()->path(),
            'actionDesc' => 'Comment Delete',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => $comment->id,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'loginToken' => null,
            'moreInfo' => null,
        ];
        // create session log
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        $comment->delete();

        return $this->success();
    }

    // histories
    public function histories(string $cid, Request $request)
    {
        $dtoRequest = new HistoryDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $comment = PrimaryHelper::fresnsModelByFsid('comment', $cid);

        if (empty($comment)) {
            throw new ResponseException(37500);
        }

        // check post
        if (empty($comment->post)) {
            throw new ResponseException(37400);
        }

        // check author
        if (empty($comment->author)) {
            throw new ResponseException(35203);
        }

        // check is enabled
        if (! $comment->is_enabled && $comment->user_id != $authUser?->id) {
            throw new ResponseException(37501);
        }

        ContentService::checkUserContentViewPerm($comment->created_at, $authUser?->id, $authUser?->expired_at);

        ContentService::checkGroupContentViewPerm($comment->created_at, $comment->post->group_id, $authUser?->id);

        $historyQuery = CommentLog::where('comment_id', $comment->id)->where('state', CommentLog::STATE_SUCCESS)->latest();

        // has author
        $historyQuery->whereRelation('author', 'is_enabled', true);

        $histories = $historyQuery->paginate($dtoRequest->pageSize ?? 15);

        $historyOptions = [
            'viewType' => 'list',
            'contentFormat' => \request()->header('X-Fresns-Client-Content-Format'),
            'checkPermissions' => true,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
            'filterAuthor' => [
                'type' => $dtoRequest->filterAuthorType,
                'keys' => $dtoRequest->filterAuthorKeys,
            ],
        ];

        $historyList = [];
        foreach ($histories as $history) {
            $historyList[] = DetailUtility::commentHistoryDetail($history, $langTag, $timezone, $authUser?->id, $historyOptions);
        }

        return $this->fresnsPaginate($historyList, $histories->total(), $histories->perPage());
    }

    // historyDetail
    public function historyDetail(string $hcid, Request $request)
    {
        $dtoRequest = new HistoryDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $commentLog = CommentLog::with(['author', 'comment', 'post'])->where('hcid', $hcid)->where('state', CommentLog::STATE_SUCCESS)->first();

        // check log
        if (empty($commentLog)) {
            throw new ResponseException(37502);
        }

        // check is enabled
        if (! $commentLog->is_enabled && $commentLog->user_id != $authUser?->id) {
            throw new ResponseException(37503);
        }

        // check comment
        if (empty($commentLog->comment)) {
            throw new ResponseException(37500);
        }

        // check post
        if (empty($commentLog->post)) {
            throw new ResponseException(37400);
        }

        // check author
        if (empty($commentLog->author)) {
            throw new ResponseException(35203);
        }

        // check is enabled
        if (! $commentLog->comment->is_enabled && $commentLog->comment->user_id != $authUser?->id) {
            throw new ResponseException(37501);
        }

        ContentService::checkUserContentViewPerm($commentLog->comment->created_at, $authUser?->id, $authUser?->expired_at);

        ContentService::checkGroupContentViewPerm($commentLog->comment->created_at, $commentLog->post->group_id, $authUser?->id);

        $historyOptions = [
            'viewType' => 'detail',
            'contentFormat' => \request()->header('X-Fresns-Client-Content-Format'),
            'checkPermissions' => true,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
            'filterAuthor' => [
                'type' => $dtoRequest->filterAuthorType,
                'keys' => $dtoRequest->filterAuthorKeys,
            ],
        ];

        $data['detail'] = DetailUtility::commentHistoryDetail($commentLog, $langTag, $timezone, $authUser?->id, $historyOptions);

        return $this->success($data);
    }

    // timelines
    public function timelines(Request $request)
    {
        $dtoRequest = new CommentTimelinesDTO($request->all());

        // Plugin provides data
        $dataPluginFskey = ConfigHelper::fresnsConfigByItemKey('comment_timelines_service');

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => AppHelper::getHeaders(),
                'body' => $request->all(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getCommentsByTimelines($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $timelineService = new TimelineService();
        $timelineOptions = [
            'langTag' => $dtoRequest->langTag,
            'contentType' => $dtoRequest->contentType,
            'sinceCid' => $dtoRequest->sinceCid,
            'beforeCid' => $dtoRequest->beforeCid,
            'dateLimit' => ContentService::getContentDateLimit($authUser->id, $authUser->expired_at),
        ];

        $followType = null;
        switch ($dtoRequest->type) {
            case 'user':
                $followType = 'user';
                $comments = $timelineService->getCommentListByFollowUsers($authUser->id, $timelineOptions);
                break;

            case 'group':
                $followType = 'group';
                $comments = $timelineService->getCommentListByFollowGroups($authUser->id, $timelineOptions);
                break;

            case 'hashtag':
                $followType = 'hashtag';
                $comments = $timelineService->getCommentListByFollowHashtags($authUser->id, $timelineOptions);
                break;

            case 'geotag':
                $followType = 'geotag';
                $comments = $timelineService->getCommentListByFollowGeotags($authUser->id, $timelineOptions);
                break;

            default:
                $followType = 'all';
                $comments = $timelineService->getCommentListByFollowAll($authUser->id, $timelineOptions);
        }

        $commentOptions = [
            'viewType' => 'list',
            'contentFormat' => \request()->header('X-Fresns-Client-Content-Format'),
            'location' => [
                'mapId' => $dtoRequest->mapId,
                'longitude' => $dtoRequest->mapLng,
                'latitude' => $dtoRequest->mapLat,
            ],
            'checkPermissions' => true,
            'isPreviewLikeUsers' => true,
            'isPreviewComments' => true,
            'outputReplyToPost' => true,
            'outputReplyToComment' => true,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
            'filterHashtag' => [
                'type' => $dtoRequest->filterHashtagType,
                'keys' => $dtoRequest->filterHashtagKeys,
            ],
            'filterGeotag' => [
                'type' => $dtoRequest->filterGeotagType,
                'keys' => $dtoRequest->filterGeotagKeys,
            ],
            'filterAuthor' => [
                'type' => $dtoRequest->filterAuthorType,
                'keys' => $dtoRequest->filterAuthorKeys,
            ],
            'filterPreviewLikeUser' => [
                'type' => $dtoRequest->filterPreviewLikeUserType,
                'keys' => $dtoRequest->filterPreviewLikeUserKeys,
            ],
            'filterPreviewComment' => [
                'type' => $dtoRequest->filterPreviewCommentType,
                'keys' => $dtoRequest->filterPreviewCommentKeys,
            ],
            'filterReplyToPost' => [
                'type' => $dtoRequest->filterReplyToPostType,
                'keys' => $dtoRequest->filterReplyToPostKeys,
            ],
            'filterReplyToComment' => [
                'type' => $dtoRequest->filterReplyToCommentType,
                'keys' => $dtoRequest->filterReplyToCommentKeys,
            ],
        ];

        $commentList = [];
        foreach ($comments as $comment) {
            $item = DetailUtility::postDetail($comment, $langTag, $timezone, $authUser->id, $commentOptions);

            $item['followType'] = InteractionUtility::getFollowType($followType, $comment->user_id, $comment->digest_state, $authUser->id, $comment->group_id, $comment->geotag_id);

            $commentList[] = $item;
        }

        return $this->fresnsPaginate($commentList, $comments->total(), $comments->perPage());
    }

    // nearby
    public function nearby(Request $request)
    {
        $dtoRequest = new CommentNearbyDTO($request->all());

        // Plugin provides data
        $dataPluginFskey = ConfigHelper::fresnsConfigByItemKey('comment_nearby_service');

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => AppHelper::getHeaders(),
                'body' => $request->all(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getCommentsByNearby($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();
        $authUserId = $authUser?->id;

        $commentQuery = Comment::query();

        // has author
        $commentQuery->whereRelation('author', 'is_enabled', true);

        // has geotag
        $commentQuery->whereNot('geotag_id', 0);

        // has post
        $commentQuery->whereRelation('post', 'is_enabled', true);

        // privacy
        $commentQuery->where('top_parent_id', 0)->where('privacy_state', Comment::PRIVACY_PUBLIC);

        // block
        $filterGroupIds = PermissionUtility::getGroupContentFilterIdArr($authUserId);

        if (empty($authUserId)) {
            $commentQuery->where('is_enabled', true);
        } else {
            $commentQuery->where(function ($query) use ($authUserId) {
                $query->where('is_enabled', true)->orWhere(function ($query) use ($authUserId) {
                    $query->where('is_enabled', false)->where('user_id', $authUserId);
                });
            });

            $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
            $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
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

            $filterGroupIds = array_unique(array_merge($filterGroupIds, $blockGroupIds));
        }

        $commentQuery->when($filterGroupIds, function ($query, $value) {
            $query->whereDoesntHave('post', function ($query) use ($value) {
                $query->whereNotIn('group_id', $value);
            });
        });

        // nearby
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
        $distance = $nearbyLength * 1000;

        $mapLng = $dtoRequest->mapLng;
        $mapLat = $dtoRequest->mapLat;

        switch (config('database.default')) {
            case 'sqlite':
                // use SpatiaLite
                $commentQuery->whereHas('geotag', function ($query) use ($mapLng, $mapLat, $distance) {
                    $query->whereRaw("ST_Distance(Transform(GeomFromText('POINT($mapLng $mapLat)', 4326), 4326), Transform(map_location, 4326)) <= {$distance}");
                });
                break;

            case 'mysql':
                $commentQuery->whereHas('geotag', function ($query) use ($mapLng, $mapLat, $distance) {
                    $query->whereRaw("ST_Distance_Sphere(map_location, ST_GeomFromText('POINT($mapLng $mapLat)', 4326)) <= {$distance}");
                });
                break;

            case 'mariadb':
                $commentQuery->whereHas('geotag', function ($query) use ($mapLng, $mapLat, $distance) {
                    $query->whereRaw("ST_Distance_Sphere(map_location, ST_GeomFromText('POINT($mapLng $mapLat)', 4326)) <= {$distance}");
                });
                break;

            case 'pgsql':
                // use PostGIS
                $commentQuery->whereHas('geotag', function ($query) use ($mapLng, $mapLat, $distance) {
                    $query->whereRaw("ST_DWithin(map_location::geography, ST_SetSRID(ST_MakePoint($mapLng, $mapLat), 4326)::geography, {$distance})");
                });
                break;

            case 'sqlsrv':
                $commentQuery->whereHas('geotag', function ($query) use ($mapLng, $mapLat, $distance) {
                    $query->whereRaw("map_location.STDistance(geography::Point($mapLat, $mapLng, 4326)) <= {$distance}");
                });
                break;

            default:
                throw new ResponseException(32303);
        }

        // lang tag
        $commentQuery->when($dtoRequest->langTag, function ($query, $value) {
            $query->where('lang_tag', $value);
        });

        // content type
        if ($dtoRequest->contentType && $dtoRequest->contentType != 'All') {
            // file
            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($dtoRequest->contentType);

            $commentQuery->when($fileTypeNumber, function ($query, $value) {
                $query->whereRelation('fileUsages', 'file_type', $value);
            });

            // text
            if ($dtoRequest->contentType == 'Text') {
                $commentQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } else {
                $commentQuery->whereRelation('extendUsages', 'app_fskey', $dtoRequest->contentType);
            }
        }

        // datetime limit
        $dateLimit = ContentService::getContentDateLimit($authUserId, $authUser?->expired_at);
        $commentQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        $comments = $commentQuery->paginate($dtoRequest->pageSize ?? 15);

        $commentOptions = [
            'viewType' => 'list',
            'contentFormat' => \request()->header('X-Fresns-Client-Content-Format'),
            'location' => [
                'mapId' => $dtoRequest->mapId,
                'longitude' => $dtoRequest->mapLng,
                'latitude' => $dtoRequest->mapLat,
            ],
            'checkPermissions' => true,
            'isPreviewLikeUsers' => true,
            'isPreviewComments' => true,
            'outputReplyToPost' => true,
            'outputReplyToComment' => true,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
            'filterHashtag' => [
                'type' => $dtoRequest->filterHashtagType,
                'keys' => $dtoRequest->filterHashtagKeys,
            ],
            'filterGeotag' => [
                'type' => $dtoRequest->filterGeotagType,
                'keys' => $dtoRequest->filterGeotagKeys,
            ],
            'filterAuthor' => [
                'type' => $dtoRequest->filterAuthorType,
                'keys' => $dtoRequest->filterAuthorKeys,
            ],
            'filterPreviewLikeUser' => [
                'type' => $dtoRequest->filterPreviewLikeUserType,
                'keys' => $dtoRequest->filterPreviewLikeUserKeys,
            ],
            'filterPreviewComment' => [
                'type' => $dtoRequest->filterPreviewCommentType,
                'keys' => $dtoRequest->filterPreviewCommentKeys,
            ],
            'filterReplyToPost' => [
                'type' => $dtoRequest->filterReplyToPostType,
                'keys' => $dtoRequest->filterReplyToPostKeys,
            ],
            'filterReplyToComment' => [
                'type' => $dtoRequest->filterReplyToCommentType,
                'keys' => $dtoRequest->filterReplyToCommentKeys,
            ],
        ];

        $commentList = [];
        foreach ($comments as $comment) {
            $commentList[] = DetailUtility::commentDetail($comment, $langTag, $timezone, $authUserId, $commentOptions);
        }

        return $this->fresnsPaginate($commentList, $comments->total(), $comments->perPage());
    }
}
