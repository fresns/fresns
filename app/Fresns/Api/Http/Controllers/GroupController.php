<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\GroupListDTO;
use App\Fresns\Api\Http\DTO\InteractiveDTO;
use App\Fresns\Api\Services\GroupService;
use App\Fresns\Api\Services\InteractiveService;
use App\Helpers\CacheHelper;
use App\Helpers\PrimaryHelper;
use App\Models\File;
use App\Models\Group;
use App\Models\PluginUsage;
use App\Models\Seo;
use App\Utilities\CollectionUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class GroupController extends Controller
{
    // tree
    public function tree()
    {
        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $groupFilterIds = PermissionUtility::getGroupFilterIds($authUser?->id);

        if (empty($authUser)) {
            $cacheKey = 'fresns_api_guest_groups';
        } else {
            $cacheKey = "fresns_user_groups_{$authUser?->uid}";
        }
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);

        $groups = Cache::remember($cacheKey, $cacheTime, function () use ($groupFilterIds) {
            return Group::with(['category', 'admins'])
                ->where(function ($query) {
                    $query->whereIn('type', [1, 2])
                        ->orWhere(function ($query) {
                            $query->whereIn('type', [3])
                                ->where('sublevel_public', Group::SUBLEVEL_PUBLIC);
                        });
                })
                ->whereNotIn('id', $groupFilterIds)
                ->isEnable()
                ->orderBy('recommend_rating')
                ->orderBy('rating')
                ->get();
        });

        $service = new GroupService();
        $groupData = [];
        foreach ($groups as $index => $group) {
            $groupData[$index] = $service->groupData($group, $langTag, $timezone, $authUser?->id);
        }

        $groupTree = CollectionUtility::toTree($groupData, 'gid', 'parentGid', 'groups');

        return $this->success($groupTree);
    }

    public function categories(Request $request)
    {
        $langTag = $this->langTag();

        $groupQuery = Group::where('type', Group::TYPE_CATEGORY)->orderBy('rating')->isEnable();

        $categories = $groupQuery->paginate($request->get('pageSize', 30));

        $catList = [];
        foreach ($categories as $category) {
            $item = $category->getCategoryInfo($langTag);
            $catList[] = $item;
        }

        return $this->fresnsPaginate($catList, $categories->total(), $categories->perPage());
    }

    // list
    public function list(Request $request)
    {
        $dtoRequest = new GroupListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $groupFilterIds = PermissionUtility::getGroupFilterIds($authUserId);

        $groupQuery = Group::with(['category', 'admins'])
            ->where('type', '!=', Group::TYPE_CATEGORY)
            ->whereNotIn('id', $groupFilterIds)
            ->isEnable();

        if ($dtoRequest->gid) {
            $parentGroup = PrimaryHelper::fresnsModelByFsid('group', $dtoRequest->gid);

            if (empty($parentGroup) || $parentGroup->trashed()) {
                throw new ApiException(37100);
            }

            if ($parentGroup->is_enable == 0) {
                throw new ApiException(37101);
            }

            switch ($parentGroup->type) {
                case Group::TYPE_CATEGORY:
                    $groupQuery->where('parent_id', $parentGroup->id)->where('sublevel_public', Group::SUBLEVEL_PUBLIC);
                break;

                case Group::TYPE_GROUP:
                    $groupQuery->where('parent_id', $parentGroup->id);
                break;
            }
        } else {
            $groupQuery->where('sublevel_public', Group::SUBLEVEL_PUBLIC);
        }

        $groupQuery->when($dtoRequest->recommend, function ($query, $value) {
            $query->where('is_recommend', $value);
        });

        $groupQuery->when($dtoRequest->createDateGt, function ($query, $value) {
            $query->whereDate('created_at', '>=', $value);
        });

        $groupQuery->when($dtoRequest->createDateLt, function ($query, $value) {
            $query->whereDate('created_at', '<=', $value);
        });

        $groupQuery->when($dtoRequest->likeCountGt, function ($query, $value) {
            $query->where('like_count', '>=', $value);
        });

        $groupQuery->when($dtoRequest->likeCountLt, function ($query, $value) {
            $query->where('like_count', '<=', $value);
        });

        $groupQuery->when($dtoRequest->dislikeCountGt, function ($query, $value) {
            $query->where('dislike_count', '>=', $value);
        });

        $groupQuery->when($dtoRequest->dislikeCountLt, function ($query, $value) {
            $query->where('dislike_count', '<=', $value);
        });

        $groupQuery->when($dtoRequest->followCountGt, function ($query, $value) {
            $query->where('follow_count', '>=', $value);
        });

        $groupQuery->when($dtoRequest->followCountLt, function ($query, $value) {
            $query->where('follow_count', '<=', $value);
        });

        $groupQuery->when($dtoRequest->blockCountGt, function ($query, $value) {
            $query->where('block_count', '>=', $value);
        });

        $groupQuery->when($dtoRequest->blockCountLt, function ($query, $value) {
            $query->where('block_count', '<=', $value);
        });

        $groupQuery->when($dtoRequest->postCountGt, function ($query, $value) {
            $query->where('post_count', '>=', $value);
        });

        $groupQuery->when($dtoRequest->postCountLt, function ($query, $value) {
            $query->where('post_count', '<=', $value);
        });

        $groupQuery->when($dtoRequest->postDigestCountGt, function ($query, $value) {
            $query->where('post_digest_count', '>=', $value);
        });

        $groupQuery->when($dtoRequest->postDigestCountLt, function ($query, $value) {
            $query->where('post_digest_count', '<=', $value);
        });

        $orderType = match ($dtoRequest->orderType) {
            default => 'rating',
            'createDate' => 'created_at',
            'like' => 'like_count',
            'dislike' => 'dislike_count',
            'follow' => 'follow_count',
            'block' => 'block_count',
            'post' => 'post_count',
            'postDigest' => 'post_digest_count',
            'rating' => 'rating',
        };

        $orderDirection = match ($dtoRequest->orderDirection) {
            default => 'asc',
            'asc' => 'asc',
            'desc' => 'desc',
        };

        $groupQuery->orderBy('recommend_rating')->orderBy($orderType, $orderDirection);

        $groupData = $groupQuery->paginate($request->get('pageSize', 15));

        $groupList = [];
        $service = new GroupService();
        foreach ($groupData as $group) {
            $groupList[] = $service->groupData($group, $langTag, $timezone, $authUserId);
        }

        return $this->fresnsPaginate($groupList, $groupData->total(), $groupData->perPage());
    }

    // detail
    public function detail(string $gid)
    {
        $group = Group::where('gid', $gid)->first();

        if (empty($group)) {
            throw new ApiException(37100);
        }

        if ($group->is_enable == 0) {
            throw new ApiException(37101);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $seoData = Seo::where('usage_type', Seo::TYPE_GROUP)->where('usage_id', $group->id)->where('lang_tag', $langTag)->first();

        $item['title'] = $seoData->title ?? null;
        $item['keywords'] = $seoData->keywords ?? null;
        $item['description'] = $seoData->description ?? null;
        $item['extensions'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_GROUP, $group->id, null, $authUserId, $langTag);
        $data['items'] = $item;

        $service = new GroupService();
        $data['detail'] = $service->groupData($group, $langTag, $timezone, $authUserId);

        return $this->success($data);
    }

    // interactive
    public function interactive(string $gid, string $type, Request $request)
    {
        $group = Group::where('gid', $gid)->isEnable()->first();
        if (empty($group)) {
            throw new ApiException(37100);
        }

        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractiveDTO($requestData);

        InteractiveService::checkInteractiveSetting($dtoRequest->type, 'group');

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $service = new InteractiveService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractiveService::TYPE_GROUP, $group->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactiveData']->total(), $data['interactiveData']->perPage());
    }
}
