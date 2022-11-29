<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\FollowDTO;
use App\Fresns\Api\Http\DTO\InteractiveDTO;
use App\Fresns\Api\Http\DTO\NearbyDTO;
use App\Fresns\Api\Http\DTO\PaginationDTO;
use App\Fresns\Api\Http\DTO\PostDetailDTO;
use App\Fresns\Api\Http\DTO\PostListDTO;
use App\Fresns\Api\Services\FollowService;
use App\Fresns\Api\Services\GroupService;
use App\Fresns\Api\Services\InteractiveService;
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
use App\Utilities\InteractiveUtility;
use App\Utilities\LbsUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new PostListDTO($request->all());

        // Plugin provides data
        $dataPluginUnikey = ConfigHelper::fresnsConfigByItemKey('content_list_service');

        if ($dtoRequest->contentType && ! $dataPluginUnikey) {
            $dataPluginUnikey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'postByAll');
        }

        if ($dataPluginUnikey) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginUnikey)->getPostByAll($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $postQuery = Post::with(['hashtags'])->isEnable();

        $blockGroupIds = InteractiveUtility::getPrivateGroupIdArr();

        if ($authUserId) {
            $blockPostIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_POST, $authUserId);
            $blockUserIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_USER, $authUserId);
            $blockGroupIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_GROUP, $authUserId);
            $blockHashtagIds = InteractiveUtility::getBlockIdArr(InteractiveUtility::TYPE_HASHTAG, $authUserId);

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
        }

        $postQuery->when($blockGroupIds, function ($query, $value) {
            $query->whereNotIn('group_id', $value);
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

            if ($viewUser->is_enable == 0) {
                throw new ApiException(35202);
            }

            if ($viewUser->wait_delete == 1) {
                throw new ApiException(35203);
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
            if ($viewGroup->is_enable == 0) {
                throw new ApiException(37101);
            }

            // group mode
            $groupDateLimit = GroupService::getGroupContentDateLimit($viewGroup->id, $authUserId);

            $postQuery->where('group_id', $viewGroup->id);
        }

        if ($dtoRequest->hid) {
            $hid = StrHelper::slug($dtoRequest->hid);
            $viewHashtag = PrimaryHelper::fresnsModelByFsid('hashtag', $hid);

            if (empty($viewHashtag)) {
                throw new ApiException(37200);
            }

            // hashtag disable
            if ($viewHashtag->is_enable == 0) {
                throw new ApiException(37201);
            }

            $postQuery->when($viewHashtag->id, function ($query, $value) {
                $query->whereHas('hashtags', function ($query) use ($value) {
                    $query->where('hashtag_id', $value);
                });
            });
        }

        $postQuery->when($dtoRequest->digestState, function ($query, $value) {
            $query->where('digest_state', $value);
        });

        $postQuery->when($dtoRequest->stickyState, function ($query, $value) {
            $query->where('sticky_state', $value);
        });

        $postQuery->when($dtoRequest->createDateGt, function ($query, $value) {
            $query->whereDate('created_at', '>=', $value);
        });

        $postQuery->when($dtoRequest->createDateLt, function ($query, $value) {
            $query->whereDate('created_at', '<=', $value);
        });

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

        if ($dtoRequest->contentType && $dtoRequest->contentType != 'all') {
            $contentType = $dtoRequest->contentType;

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

        $posts = $postQuery->paginate($request->get('pageSize', 15));

        $postList = [];
        $service = new PostService();
        foreach ($posts as $post) {
            $postList[] = $service->postData($post, 'list', $langTag, $timezone, $authUserId, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);
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

        $post = PrimaryHelper::fresnsModelByFsid('post', $pid);

        if (empty($post)) {
            throw new ApiException(37300);
        }

        if ($post->is_enable == 0) {
            throw new ApiException(37301);
        }

        UserService::checkUserContentViewPerm($post->created_at, $authUserId);
        GroupService::checkGroupContentViewPerm($post->created_at, $post->group_id, $authUserId);

        // Plugin provides data
        $dataPluginUnikey = ConfigHelper::fresnsConfigByItemKey('content_detail_service');

        if ($dataPluginUnikey) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginUnikey)->getPostDetail($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns provides data
        $seoData = LanguageHelper::fresnsLanguageSeoDataById('post', $post->id, $langTag);

        $item['title'] = $seoData?->title;
        $item['keywords'] = $seoData?->keywords;
        $item['description'] = $seoData?->description;
        $data['items'] = $item;

        $service = new PostService();
        $data['detail'] = $service->postData($post, 'detail', $langTag, $timezone, $authUserId, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);

        return $this->success($data);
    }

    // interactive
    public function interactive(string $pid, string $type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractiveDTO($requestData);

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $post = Post::where('pid', $pid)->isEnable()->first();

        if (empty($post)) {
            throw new ApiException(37300);
        }

        UserService::checkUserContentViewPerm($post->created_at, $authUserId);

        InteractiveService::checkInteractiveSetting($dtoRequest->type, 'post');

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $service = new InteractiveService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractiveService::TYPE_POST, $post->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactiveData']->total(), $data['interactiveData']->perPage());
    }

    // userList
    public function userList(string $pid, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $post = Post::where('pid', $pid)->isEnable()->first();

        if (empty($post)) {
            throw new ApiException(37300);
        }

        UserService::checkUserContentViewPerm($post->created_at, $authUserId);

        $userListData = PostUser::with('user')->where('post_id', $post->id)->latest()->paginate($request->get('pageSize', 15));

        $userList = [];
        $service = new UserService();
        foreach ($userListData as $user) {
            $userList[] = $service->userData($user, $langTag, $timezone, $authUserId);
        }

        return $this->fresnsPaginate($userList, $userListData->total(), $userListData->perPage());
    }

    // postLogs
    public function postLogs(string $pid, Request $request)
    {
        $dtoRequest = new PaginationDTO($request->all());
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $post = Post::where('pid', $pid)->isEnable()->first();

        if (empty($post)) {
            throw new ApiException(37300);
        }

        UserService::checkUserContentViewPerm($post->created_at, $authUserId);

        $postLogs = PostLog::with('creator')->where('post_id', $post->id)->where('state', 3)->latest()->paginate($request->get('pageSize', 15));

        $postLogList = [];
        $service = new PostService();
        foreach ($postLogs as $log) {
            $postLogList[] = $service->postLogData($log, 'list', $langTag, $timezone);
        }

        return $this->fresnsPaginate($postLogList, $postLogs->total(), $postLogs->perPage());
    }

    // logDetail
    public function logDetail(string $pid, int $logId, Request $request)
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $post = Post::where('pid', $pid)->isEnable()->first();

        if (empty($post)) {
            throw new ApiException(37300);
        }

        UserService::checkUserContentViewPerm($post->created_at, $authUserId);

        $log = PostLog::where('post_id', $post->id)->where('id', $logId)->where('state', 3)->first();

        if (empty($log)) {
            throw new ApiException(37302);
        }

        $service = new PostService();
        $data['detail'] = $service->postLogData($log, 'detail', $langTag, $timezone);

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
        $dataPluginUnikey = ConfigHelper::fresnsConfigByItemKey('content_follow_service');

        if ($dtoRequest->contentType && ! $dataPluginUnikey) {
            $dataPluginUnikey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'postByFollow');
        }

        if ($dataPluginUnikey) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginUnikey)->getPostByAll($wordBody);

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
                $posts = $followService->getPostListByFollowAll($authUser->id, $dtoRequest->contentType, $dateLimit);
            break;

            // user
            case 'user':
                $posts = $followService->getPostListByFollowUsers($authUser->id, $dtoRequest->contentType, $dateLimit);
            break;

            // group
            case 'group':
                $posts = $followService->getPostListByFollowGroups($authUser->id, $dtoRequest->contentType, $dateLimit);
            break;

            // hashtag
            case 'hashtag':
                $posts = $followService->getPostListByFollowHashtags($authUser->id, $dtoRequest->contentType, $dateLimit);
            break;
        }

        $postList = [];
        $service = new PostService();
        foreach ($posts as $post) {
            $listItem = $service->postData($post, 'list', $langTag, $timezone, $authUser->id, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);
            $listItem['followType'] = InteractiveUtility::getFollowType($post->user_id, $authUser?->id, $post->group_id, $post?->hashtags?->toArray());

            $postList[] = $listItem;
        }

        return $this->fresnsPaginate($postList, $posts->total(), $posts->perPage());
    }

    // nearby
    public function nearby(Request $request)
    {
        $dtoRequest = new NearbyDTO($request->all());

        // Plugin provides data
        $dataPluginUnikey = ConfigHelper::fresnsConfigByItemKey('content_nearby_service');

        if ($dtoRequest->contentType && ! $dataPluginUnikey) {
            $dataPluginUnikey = ExtendUtility::getDataExtend($dtoRequest->contentType, 'postByNearby');
        }

        if ($dataPluginUnikey) {
            $wordBody = [
                'headers' => \request()->headers->all(),
                'body' => $dtoRequest->toArray(),
            ];

            $fresnsResp = \FresnsCmdWord::plugin($dataPluginUnikey)->getPostByAll($wordBody);

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
            ->select([
                DB::raw('*'),
                DB::raw(LbsUtility::getDistanceSql('map_longitude', 'map_latitude', $dtoRequest->mapLng, $dtoRequest->mapLat)),
            ])
            ->having('distance', '<=', $nearbyLength)
            ->orderBy('distance')
            ->paginate();

        $postList = [];
        $service = new PostService();
        foreach ($posts as $post) {
            $postList[] = $service->postData($post, 'list', $langTag, $timezone, $authUser?->id, $dtoRequest->mapId, $dtoRequest->mapLng, $dtoRequest->mapLat);
        }

        return $this->fresnsPaginate($postList, $posts->total(), $posts->perPage());
    }
}
