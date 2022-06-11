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
use App\Fresns\Api\Services\HeaderService;
use App\Fresns\Api\Services\InteractiveService;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Group;
use App\Models\PluginUsage;
use App\Models\Seo;
use App\Utilities\CollectionUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // tree
    public function tree()
    {
        $headers = HeaderService::getHeaders();

        $authUserId = null;
        if (! empty($headers['uid'])) {
            $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);
        }

        $groupFilterIds = PermissionUtility::getGroupFilterIds($authUserId);

        $groups = Group::where('type_view', 1)->whereNotIn('id', $groupFilterIds)->isEnable()->orderBy('rating')->get();

        $service = new GroupService();

        $groupData = [];
        foreach ($groups as $index => $group) {
            $groupData[$index][] = $service->groupList($group, $headers['langTag'], $headers['timezone'], $authUserId);
        }

        $groupTree = CollectionUtility::toTree($groupData, 'gid', 'category', 'groups');

        return $this->success($groupTree);
    }

    public function categories(Request $request)
    {
        $headers = HeaderService::getHeaders();

        $groupQuery = Group::where('type', 1)->orderBy('rating')->isEnable();

        $categories = $groupQuery->paginate($request->get('pageSize', 30));

        $catList = [];
        foreach ($categories as $category) {
            $item = $category->getCategoryInfo($headers['langTag']);
            $catList[] = $item;
        }

        return $this->fresnsPaginate($catList, $categories->total(), $categories->perPage());
    }

    // list
    public function list(Request $request)
    {
        $dtoRequest = new GroupListDTO($request->all());

        $headers = HeaderService::getHeaders();

        $authUserId = null;
        if (! empty($headers['uid'])) {
            $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);
        }

        $groupFilterIds = PermissionUtility::getGroupFilterIds($authUserId);
        $groupQuery = Group::whereIn('type', [2, 3])->whereNotIn('id', $groupFilterIds)->isEnable();

        if ($dtoRequest->gid) {
            $parentId = PrimaryHelper::fresnsGroupIdByGid($dtoRequest->gid);
            $groupQuery->where('parent_id', $parentId);
        } else {
            $groupQuery->where('type_view', 1);
        }

        if ($dtoRequest->recommend) {
            $groupQuery->where('is_recommend', $dtoRequest->recommend);
        }

        if ($dtoRequest->likeCountGt) {
            $groupQuery->where('like_count', '>=', $dtoRequest->likeCountGt);
        }

        if ($dtoRequest->likeCountLt) {
            $groupQuery->where('like_count', '<=', $dtoRequest->likeCountLt);
        }

        if ($dtoRequest->dislikeCountGt) {
            $groupQuery->where('dislike_count', '>=', $dtoRequest->dislikeCountGt);
        }

        if ($dtoRequest->dislikeCountLt) {
            $groupQuery->where('dislike_count', '<=', $dtoRequest->dislikeCountLt);
        }

        if ($dtoRequest->followCountGt) {
            $groupQuery->where('follow_count', '>=', $dtoRequest->followCountGt);
        }

        if ($dtoRequest->followCountLt) {
            $groupQuery->where('follow_count', '<=', $dtoRequest->followCountLt);
        }

        if ($dtoRequest->blockCountGt) {
            $groupQuery->where('block_count', '>=', $dtoRequest->blockCountGt);
        }

        if ($dtoRequest->blockCountLt) {
            $groupQuery->where('block_count', '<=', $dtoRequest->blockCountLt);
        }

        if ($dtoRequest->postCountGt) {
            $groupQuery->where('post_count', '>=', $dtoRequest->postCountGt);
        }

        if ($dtoRequest->postCountLt) {
            $groupQuery->where('post_count', '<=', $dtoRequest->postCountLt);
        }

        if ($dtoRequest->postDigestCountGt) {
            $groupQuery->where('post_digest_count', '>=', $dtoRequest->postDigestCountGt);
        }

        if ($dtoRequest->postDigestCountLt) {
            $groupQuery->where('post_digest_count', '<=', $dtoRequest->postDigestCountLt);
        }

        if ($dtoRequest->createTimeGt) {
            $groupQuery->where('created_at', '>=', $dtoRequest->createTimeGt);
        }

        if ($dtoRequest->createTimeLt) {
            $groupQuery->where('created_at', '<=', $dtoRequest->createTimeLt);
        }

        $ratingType = match ($dtoRequest->ratingType) {
            default => 'rating',
            'like' => 'like_me_count',
            'dislike' => 'dislike_me_count',
            'follow' => 'follow_me_count',
            'block' => 'block_me_count',
            'post' => 'post_count',
            'postDigest' => 'post_digest_count',
            'createTime' => 'created_at',
            'rating' => 'rating',
        };

        $ratingOrder = match ($dtoRequest->ratingOrder) {
            default => 'asc',
            'asc' => 'asc',
            'desc' => 'desc',
        };

        $groupQuery->orderBy('recommend_rating', 'asc')->orderBy($ratingType, $ratingOrder);

        $groupData = $groupQuery->paginate($request->get('pageSize', 15));

        $groupList = [];
        $service = new GroupService();
        foreach ($groupData as $group) {
            $groupList[] = $service->groupList($group, $headers['langTag'], $headers['timezone'], $authUserId);
        }

        return $this->fresnsPaginate($groupList, $groupData->total(), $groupData->perPage());
    }

    // detail
    public function detail(string $gid)
    {
        $group = Group::whereGid($gid)->isEnable()->first();
        if (empty($group)) {
            throw new ApiException(37100);
        }

        $headers = HeaderService::getHeaders();

        $authUserId = null;
        if (! empty($headers['uid'])) {
            $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);
        }

        $seoData = Seo::where('linked_type', Seo::TYPE_GROUP)->where('linked_id', $group->id)->where('lang_tag', $headers['langTag'])->first();

        $common['title'] = $seoData->title ?? null;
        $common['keywords'] = $seoData->keywords ?? null;
        $common['description'] = $seoData->description ?? null;
        $common['extensions'] = ExtendUtility::getPluginExtends(PluginUsage::TYPE_GROUP, $group->id, null, $authUserId, $headers['langTag']);
        $data['commons'] = $common;

        $data['category'] = $group->category->getCategoryInfo($headers['langTag']);

        $service = new GroupService();
        $data['detail'] = $service->groupDetail($group, $headers['langTag'], $headers['timezone'], $authUserId);

        return $this->success($data);
    }

    // interactive
    public function interactive(string $gid, string $type, Request $request)
    {
        $group = Group::whereGid($gid)->isEnable()->first();
        if (empty($group)) {
            throw new ApiException(37100);
        }

        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractiveDTO($requestData);

        $markSet = ConfigHelper::fresnsConfigByItemKey("it_{$dtoRequest->type}_groups");
        if (! $markSet) {
            throw new ApiException(36201);
        }

        $timeOrder = $dtoRequest->timeOrder ?: 'desc';

        $headers = HeaderService::getHeaders();
        $authUserId = null;
        if (! empty($headers['uid'])) {
            $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);
        }

        $service = new InteractiveService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractiveService::TYPE_GROUP, $group->id, $timeOrder, $headers['langTag'], $headers['timezone'], $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactiveData']->total(), $data['interactiveData']->perPage());
    }
}
