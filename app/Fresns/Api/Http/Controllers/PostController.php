<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\FollowDTO;
use App\Fresns\Api\Http\DTO\InteractionDTO;
use App\Fresns\Api\Http\DTO\NearbyDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
use App\Fresns\Api\Http\DTO\PostDetailDTO;
use App\Fresns\Api\Http\DTO\PostListDTO;
use App\Fresns\Api\Services\FollowService;
use App\Fresns\Api\Services\GroupService;
use App\Fresns\Api\Services\InteractionService;
use App\Fresns\Api\Services\PostService;
use App\Fresns\Api\Services\UserService;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Post;
use App\Models\PostLog;
use App\Models\PostUser;
use App\Utilities\ExtendUtility;
use App\Utilities\InteractionUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new PostListDTO($request->all());

        // Plugin provides data
        $dataPluginFskey = ConfigHelper::fresnsConfigByItemKey('content_list_service');

        if ($dtoRequest->contentType && ! $dataPluginFskey) {
            $dataPluginFskey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'postByAll');
        }

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getPostByAll($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $postQuery = Post::with(['author', 'hashtagUsages'])->has('author');

        $blockGroupIds = InteractionUtility::getPrivateGroupIdArr();
        $blockHashtagIds = [];

        if ($authUserId) {
            $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);
            $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
            $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
            $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);

            $postQuery->when($blockPostIds, function ($query, $value) {
                $query->whereNotIn('id', $value);
            });

            $postQuery->when($blockUserIds, function ($query, $value) {
                $query->whereNotIn('user_id', $value);
            });
        }

        // filterGroups
        $filterGroups = array_filter(explode(',', $dtoRequest->blockGroups));
        if ($filterGroups) {
            $groupIds = [];
            foreach ($filterGroups as $gid) {
                $groupId = PrimaryHelper::fresnsGroupIdByGid($gid);

                if (empty($groupId)) {
                    continue;
                }

                $groupIds[] = $groupId;
            }

            $blockGroupIds = array_merge($blockGroupIds, $groupIds);
            $blockGroupIds = array_unique($blockGroupIds);
        }

        $postQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereNotIn('group_id', $value);
        });

        // filterHashtags
        $filterHashtags = array_filter(explode(',', $dtoRequest->blockHashtags));
        if ($filterHashtags) {
            $hashtagIds = [];
            foreach ($filterHashtags as $hid) {
                $hashtagIds[] = PrimaryHelper::fresnsHashtagIdByHid($hid);
            }

            $blockHashtagIds = array_merge($blockHashtagIds, $hashtagIds);
            $blockHashtagIds = array_unique($blockHashtagIds);
        }

        $postQuery->when($blockHashtagIds, function ($query, $value) {
            $query->where(function ($postQuery) use ($value) {
                $postQuery->whereDoesntHave('hashtagUsages')->orWhereHas('hashtagUsages', function ($query) use ($value) {
                    $query->whereNotIn('hashtag_id', $value);
                });
            });
        });

        // is enabled
        $postQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true);
            if ($authUserId) {
                $query->orWhere(function ($query) use ($authUserId) {
                    $query->where('is_enabled', false)->where('user_id', $authUserId);
                });
            }
        });

        // user is enabled
        $postQuery->whereHas('author', function ($query) {
            $query->where('is_enabled', true);
        });

        if ($dtoRequest->uidOrUsername) {
            $postConfig = ConfigHelper::fresnsConfigByItemKey('it_posts');
            if (! $postConfig) {
                throw new ApiException(35305);
            }

            $viewUser = PrimaryHelper::fresnsModelByFsid('user', $dtoRequest->uidOrUsername);

            if (empty($viewUser) || $viewUser->trashed()) {
                throw new ApiException(31602);
            }

            $postQuery->where('user_id', $viewUser->id)->where('is_anonymous', 0);
        }

        $groupDateLimit = null;
        if ($dtoRequest->gid) {
            $viewGroup = PrimaryHelper::fresnsModelByFsid('group', $dtoRequest->gid);

            if (empty($viewGroup) || $viewGroup->trashed()) {
                throw new ApiException(37100);
            }

            // group disable
            if (! $viewGroup->is_enabled) {
                throw new ApiException(37101);
            }

            // group mode
            $checkLimit = GroupService::getGroupContentDateLimit($viewGroup->id, $authUserId);

            if ($checkLimit['code']) {
                return $this->warning($checkLimit['code']);
            }

            $groupDateLimit = $checkLimit['datetime'];

            if ($dtoRequest->includeSubgroups) {
                $allGroups = PrimaryHelper::fresnsModelGroups($viewGroup->id);

                $groupsArr = $allGroups->pluck('id');

                $postQuery->whereIn('group_id', $groupsArr);
            } else {
                $postQuery->where('group_id', $viewGroup->id);
            }
        }

        if ($dtoRequest->hid) {
            $hid = StrHelper::slug($dtoRequest->hid);
            $viewHashtag = PrimaryHelper::fresnsModelByFsid('hashtag', $hid);

            if (empty($viewHashtag)) {
                throw new ApiException(37200);
            }

            // hashtag disable
            if (! $viewHashtag->is_enabled) {
                throw new ApiException(37201);
            }

            $hashtagId = $viewHashtag->id;

            $postQuery->whereHas('hashtagUsages', function ($query) use ($hashtagId) {
                $query->where('hashtag_id', $hashtagId);
            });
        }

        if ($dtoRequest->allDigest) {
            $postQuery->whereIn('digest_state', [Post::DIGEST_GENERAL, Post::DIGEST_BEST]);
        } else {
            $postQuery->when($dtoRequest->digestState, function ($query, $value) {
                $query->where('digest_state', $value);
            });
        }

        $postQuery->when($dtoRequest->stickyState, function ($query, $value) {
            $query->where('sticky_state', $value);
        });

        if ($dtoRequest->createDate) {
            switch ($dtoRequest->createDate) {
                case 'today':
                    $postQuery->whereDate('created_at', now()->format('Y-m-d'));
                    break;

                case 'yesterday':
                    $postQuery->whereDate('created_at', now()->subDay()->format('Y-m-d'));
                    break;

                case 'week':
                    $postQuery->whereDate('created_at', '>=', now()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'lastWeek':
                    $postQuery->whereDate('created_at', '>=', now()->subWeek()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->subWeek()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'month':
                    $postQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;

                case 'lastMonth':
                    $lastMonth = now()->subMonth()->month;
                    $year = now()->year;
                    if ($lastMonth == 12) {
                        $year = now()->subYear()->year;
                    }
                    $postQuery->whereMonth('created_at', $lastMonth)->whereYear('created_at', $year);
                    break;

                case 'year':
                    $postQuery->whereYear('created_at', now()->year);
                    break;

                case 'lastYear':
                    $postQuery->whereYear('created_at', now()->subYear()->year);
                    break;
            }
        } else {
            $postQuery->when($dtoRequest->createDateGt, function ($query, $value) {
                $query->whereDate('created_at', '>=', $value);
            });

            $postQuery->when($dtoRequest->createDateLt, function ($query, $value) {
                $query->whereDate('created_at', '<=', $value);
            });
        }

        $postQuery->when($dtoRequest->likeCountGt, function ($query, $value) {
            $query->where('like_count', '>=', $value);
        });

        $postQuery->when($dtoRequest->likeCountLt, function ($query, $value) {
            $query->where('like_count', '<=', $value);
        });

        $postQuery->when($dtoRequest->dislikeCountGt, function ($query, $value) {
            $query->where('dislike_count', '>=', $value);
        });

        $postQuery->when($dtoRequest->dislikeCountLt, function ($query, $value) {
            $query->where('dislike_count', '<=', $value);
        });

        $postQuery->when($dtoRequest->followCountGt, function ($query, $value) {
            $query->where('follow_count', '>=', $value);
        });

        $postQuery->when($dtoRequest->followCountLt, function ($query, $value) {
            $query->where('follow_count', '<=', $value);
        });

        $postQuery->when($dtoRequest->blockCountGt, function ($query, $value) {
            $query->where('block_count', '>=', $value);
        });

        $postQuery->when($dtoRequest->blockCountLt, function ($query, $value) {
            $query->where('block_count', '<=', $value);
        });

        $postQuery->when($dtoRequest->commentCountGt, function ($query, $value) {
            $query->where('comment_count', '>=', $value);
        });

        $postQuery->when($dtoRequest->commentCountGt, function ($query, $value) {
            $query->where('comment_count', '<=', $value);
        });

        if ($dtoRequest->contentType && $dtoRequest->contentType != 'All') {
            $contentType = $dtoRequest->contentType;

            $fileTypeNumber = FileHelper::fresnsFileTypeNumber($contentType);

            if ($contentType == 'Text') {
                $postQuery->doesntHave('fileUsages')->doesntHave('extendUsages');
            } elseif ($fileTypeNumber) {
                $postQuery->whereHas('fileUsages', function ($query) use ($fileTypeNumber) {
                    $query->where('file_type', $fileTypeNumber);
                });
            } else {
                $postQuery->whereHas('extendUsages', function ($query) use ($contentType) {
                    $query->where('plugin_fskey', $contentType);
                });
            }
        }

        $dateLimit = $groupDateLimit ?? UserService::getContentDateLimit($authUserId);
        $postQuery->when($dateLimit, function ($query, $value) {
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

        $postQuery->orderBy($orderType, $orderDirection);

        $posts = $postQuery->paginate($dtoRequest->pageSize ?? 15);

        $postList = [];
        $service = new PostService();
        foreach ($posts as $post) {
            $postList[] = $service->postData($post, 'list', $langTag, $timezone, $authUserId, true, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);
        }

        return $this->fresnsPaginate($postList, $posts->total(), $posts->perPage());
    }

    // detail
    public function detail(string $pid, Request $request)
    {
        $requestData = $request->all();
        $requestData['pid'] = $pid;
        $dtoRequest = new PostDetailDTO($requestData);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $post = Post::with(['author'])->where('pid', $pid)->first();

        if (empty($post)) {
            throw new ApiException(37300);
        }

        // check author
        if (empty($post?->author)) {
            throw new ApiException(35203);
        }

        // check is enabled
        if (! $post->is_enabled && $post->user_id != $authUserId) {
            throw new ApiException(37301);
        }

        UserService::checkUserContentViewPerm($post->created_at, $authUserId);
        GroupService::checkGroupContentViewPerm($post->created_at, $post->group_id, $authUserId);

        // Plugin provides data
        $dataPluginFskey = ConfigHelper::fresnsConfigByItemKey('content_detail_service');

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getPostDetail($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $seoData = LanguageHelper::fresnsLanguageSeoDataById('post', $post->id, $langTag);

        $item['title'] = $seoData?->title;
        $item['keywords'] = $seoData?->keywords;
        $item['description'] = $seoData?->description;
        $data['items'] = $item;

        $service = new PostService();
        $data['detail'] = $service->postData($post, 'detail', $langTag, $timezone, $authUserId, true, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);

        return $this->success($data);
    }

    // interaction
    public function interaction(string $pid, string $type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractionDTO($requestData);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $post = Post::with(['author'])->where('pid', $pid)->first();

        if (empty($post)) {
            throw new ApiException(37300);
        }

        // check author
        if (empty($post?->author)) {
            throw new ApiException(35203);
        }

        // check is enabled
        if (! $post->is_enabled && $post->user_id != $authUserId) {
            throw new ApiException(37301);
        }

        UserService::checkUserContentViewPerm($post->created_at, $authUserId);

        InteractionService::checkInteractionSetting($dtoRequest->type, 'post');

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $service = new InteractionService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractionService::TYPE_POST, $post->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactionData']->total(), $data['interactionData']->perPage());
    }

    // users
    public function users(string $pid, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $post = Post::with(['author'])->where('pid', $pid)->first();

        if (empty($post)) {
            throw new ApiException(37300);
        }

        // check author
        if (empty($post?->author)) {
            throw new ApiException(35203);
        }

        // check is enabled
        if (! $post->is_enabled && $post->user_id != $authUserId) {
            throw new ApiException(37301);
        }

        UserService::checkUserContentViewPerm($post->created_at, $authUserId);

        $userListData = PostUser::with('user')->where('post_id', $post->id)->latest()->paginate($dtoRequest->pageSize ?? 15);

        $userList = [];
        $service = new UserService();
        foreach ($userListData as $user) {
            $userList[] = $service->userData($user, 'list', $langTag, $timezone, $authUserId);
        }

        return $this->fresnsPaginate($userList, $userListData->total(), $userListData->perPage());
    }

    // quotes
    public function quotes(string $pid, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $post = Post::with(['author'])->where('pid', $pid)->first();

        if (empty($post)) {
            throw new ApiException(37300);
        }

        // check author
        if (empty($post?->author)) {
            throw new ApiException(35203);
        }

        // check is enabled
        if (! $post->is_enabled && $post->user_id != $authUserId) {
            throw new ApiException(37301);
        }

        UserService::checkUserContentViewPerm($post->created_at, $authUserId);

        $postQuery = Post::with(['author', 'hashtagUsages'])->has('author');

        $blockGroupIds = InteractionUtility::getPrivateGroupIdArr();
        $blockHashtagIds = [];

        if ($authUserId) {
            $blockPostIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_POST, $authUserId);
            $blockUserIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_USER, $authUserId);
            $blockGroupIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_GROUP, $authUserId);
            $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);

            $postQuery->when($blockPostIds, function ($query, $value) {
                $query->whereNotIn('id', $value);
            });

            $postQuery->when($blockUserIds, function ($query, $value) {
                $query->whereNotIn('user_id', $value);
            });
        }

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

        // is enabled
        $postQuery->where(function ($query) use ($authUserId) {
            $query->where('is_enabled', true);
            if ($authUserId) {
                $query->orWhere(function ($query) use ($authUserId) {
                    $query->where('is_enabled', false)->where('user_id', $authUserId);
                });
            }
        });

        // user is enabled
        $postQuery->whereHas('author', function ($query) {
            $query->where('is_enabled', true);
        });

        // date limit
        $dateLimit = $groupDateLimit ?? UserService::getContentDateLimit($authUserId);
        $postQuery->when($dateLimit, function ($query, $value) {
            $query->where('created_at', '<=', $value);
        });

        $postListData = $postQuery->latest()->paginate($dtoRequest->pageSize ?? 15);

        $postList = [];
        $service = new PostService();
        foreach ($postListData as $post) {
            $postList[] = $service->postData($post, 'list', $langTag, $timezone, $authUserId);
        }

        return $this->fresnsPaginate($postList, $postListData->total(), $postListData->perPage());
    }

    // postLogs
    public function postLogs(string $pid, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $post = Post::with(['author'])->where('pid', $pid)->first();

        if (empty($post)) {
            throw new ApiException(37300);
        }

        // check author
        if (empty($post?->author)) {
            throw new ApiException(35203);
        }

        // check is enabled
        if (! $post->is_enabled && $post->user_id != $authUserId) {
            throw new ApiException(37301);
        }

        UserService::checkUserContentViewPerm($post->created_at, $authUserId);

        $postLogs = PostLog::with(['parentPost', 'group', 'author'])->where('post_id', $post->id)->where('state', 3)->latest()->paginate($dtoRequest->pageSize ?? 15);

        $postLogList = [];
        $service = new PostService();
        foreach ($postLogs as $log) {
            $postLogList[] = $service->postLogData($log, 'list', $langTag, $timezone, $authUserId);
        }

        return $this->fresnsPaginate($postLogList, $postLogs->total(), $postLogs->perPage());
    }

    // logDetail
    public function logDetail(string $pid, int $logId, Request $request)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $post = Post::where('pid', $pid)->first();

        if (empty($post)) {
            throw new ApiException(37300);
        }

        if (! $post->is_enabled && $post->user_id != $authUserId) {
            throw new ApiException(37301);
        }

        UserService::checkUserContentViewPerm($post->created_at, $authUserId);

        $log = PostLog::with(['parentPost', 'group', 'author'])->where('post_id', $post->id)->where('id', $logId)->where('state', 3)->first();

        if (empty($log)) {
            throw new ApiException(37302);
        }

        $service = new PostService();
        $data['detail'] = $service->postLogData($log, 'detail', $langTag, $timezone, $authUserId);

        return $this->success($data);
    }

    // delete
    public function delete(string $pid)
    {
        $post = Post::where('pid', $pid)->first();

        if (empty($post)) {
            throw new ApiException(36400);
        }

        $authUser = $this->user();

        if ($post->user_id != $authUser->id) {
            throw new ApiException(36403);
        }

        if (! $post->postAppend->can_delete) {
            throw new ApiException(36401);
        }

        InteractionUtility::publishStats('post', $post->id, 'decrement');

        PostLog::where('post_id', $post->id)->delete();

        $post->delete();

        return $this->success();
    }

    // follow
    public function follow(string $type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new FollowDTO($requestData);

        // Plugin provides data
        $dataPluginFskey = ConfigHelper::fresnsConfigByItemKey('content_follow_service');

        if ($dtoRequest->contentType && ! $dataPluginFskey) {
            $dataPluginFskey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'postByFollow');
        }

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getPostByAll($wordBody);

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
                $posts = $followService->getPostListByFollowAll($authUser->id, $dtoRequest->contentType, $dateLimit);
                break;

            case 'user':
                $posts = $followService->getPostListByFollowUsers($authUser->id, $dtoRequest->contentType, $dateLimit);
                break;

            case 'group':
                $posts = $followService->getPostListByFollowGroups($authUser->id, $dtoRequest->contentType, $dateLimit);
                break;

            case 'hashtag':
                $posts = $followService->getPostListByFollowHashtags($authUser->id, $dtoRequest->contentType, $dateLimit);
                break;
        }

        $postList = [];
        $service = new PostService();
        foreach ($posts as $post) {
            $listItem = $service->postData($post, 'list', $langTag, $timezone, $authUser->id, true, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);
            $listItem['followType'] = InteractionUtility::getFollowType($post->user_id, $authUser?->id, $post->group_id, $post?->hashtags?->toArray());

            $postList[] = $listItem;
        }

        return $this->fresnsPaginate($postList, $posts->total(), $posts->perPage());
    }

    // nearby
    public function nearby(Request $request)
    {
        $dtoRequest = new NearbyDTO($request->all());

        // Plugin provides data
        $dataPluginFskey = ConfigHelper::fresnsConfigByItemKey('content_nearby_service');

        if ($dtoRequest->contentType && ! $dataPluginFskey) {
            $dataPluginFskey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'postByNearby');
        }

        if ($dataPluginFskey) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginFskey)->getPostByAll($wordBody);

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

        $posts = Post::query()
            ->select(DB::raw("*, ( 6371 * acos( cos( radians($dtoRequest->mapLat) ) * cos( radians( map_latitude ) ) * cos( radians( map_longitude ) - radians($dtoRequest->mapLng) ) + sin( radians($dtoRequest->mapLat) ) * sin( radians( map_latitude ) ) ) ) AS distance"))
            ->having('distance', '<=', $nearbyLength)
            ->orderBy('distance')
            ->paginate($dtoRequest->pageSize ?? 15);

        $postList = [];
        $service = new PostService();
        foreach ($posts as $post) {
            $postList[] = $service->postData($post, 'list', $langTag, $timezone, $authUser?->id, true, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);
        }

        return $this->fresnsPaginate($postList, $posts->total(), $posts->perPage());
    }
}
