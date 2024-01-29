<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\DetailDTO;
use App\Fresns\Api\Http\DTO\GroupListDTO;
use App\Fresns\Api\Http\DTO\GroupTreeDTO;
use App\Fresns\Api\Http\DTO\InteractionDTO;
use App\Fresns\Api\Services\InteractionService;
use App\Helpers\CacheHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Group;
use App\Models\Seo;
use App\Utilities\DetailUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\GeneralUtility;
use App\Utilities\PermissionUtility;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    // tree
    public function tree(Request $request)
    {
        $dtoRequest = new GroupTreeDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUser = $this->user();

        $cacheKey = 'fresns_group_tree_by_guest';
        $cacheTags = 'fresnsGroups';
        if ($authUser) {
            $cacheKey = "fresns_group_tree_by_user_{$authUser->id}";
            $cacheTags = ['fresnsGroups', 'fresnsUsers'];
        }

        $groups = CacheHelper::get($cacheKey, $cacheTags);

        if (empty($groups)) {
            $groupQuery = Group::isEnabled();

            if (empty($authUser)) {
                $groupQuery->where('privacy', Group::PRIVACY_PUBLIC);
            }

            $groupFilterIds = PermissionUtility::getGroupListFilterIdArr($authUser?->id);
            $groupQuery->when($groupFilterIds, function ($query, $value) {
                $query->whereNotIn('id', $value);
            });

            $groups = $groupQuery->orderBy('recommend_sort_order')->orderBy('sort_order')->get();

            CacheHelper::put($groups, $cacheKey, $cacheTags);
        }

        $groupOptions = [
            'viewType' => 'list',
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys ? $dtoRequest->filterKeys.',gid,parentGid' : null,
            ],
        ];

        $groupData = [];
        foreach ($groups as $index => $group) {
            $groupData[$index] = DetailUtility::groupDetail($group, $langTag, $timezone, $authUser?->id, $groupOptions);
        }

        $groupTree = GeneralUtility::collectionToTree($groupData, 'gid', 'parentGid', 'groups');

        return $this->success($groupTree);
    }

    // list
    public function list(Request $request)
    {
        $dtoRequest = new GroupListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $groupQuery = Group::isEnabled();

        if (empty($authUserId)) {
            $groupQuery->where('privacy', Group::PRIVACY_PUBLIC);
        }

        $groupFilterIds = PermissionUtility::getGroupListFilterIdArr($authUserId);
        $groupQuery->when($groupFilterIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        $groupQuery->when($dtoRequest->type, function ($query, $value) {
            $query->where('type', $value);
        });

        $groupQuery->when($dtoRequest->gid, function ($query, $value) {
            $parentGroup = PrimaryHelper::fresnsModelByFsid('group', $value);

            if (empty($parentGroup) || $parentGroup->trashed()) {
                throw new ApiException(37100);
            }

            if (! $parentGroup->is_enabled) {
                throw new ApiException(37101);
            }

            $query->where('parent_id', $parentGroup->id);
        });

        if (isset($dtoRequest->recommend)) {
            $groupQuery->where('is_recommend', $dtoRequest->recommend);
        }

        if ($dtoRequest->createdDate) {
            switch ($dtoRequest->createdDate) {
                case 'today':
                    $groupQuery->whereDate('created_at', now()->format('Y-m-d'));
                    break;

                case 'yesterday':
                    $groupQuery->whereDate('created_at', now()->subDay()->format('Y-m-d'));
                    break;

                case 'week':
                    $groupQuery->whereDate('created_at', '>=', now()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'lastWeek':
                    $groupQuery->whereDate('created_at', '>=', now()->subWeek()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->subWeek()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'month':
                    $groupQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;

                case 'lastMonth':
                    $lastMonth = now()->subMonth()->month;
                    $year = now()->year;
                    if ($lastMonth == 12) {
                        $year = now()->subYear()->year;
                    }
                    $groupQuery->whereMonth('created_at', $lastMonth)->whereYear('created_at', $year);
                    break;

                case 'year':
                    $groupQuery->whereYear('created_at', now()->year);
                    break;

                case 'lastYear':
                    $groupQuery->whereYear('created_at', now()->subYear()->year);
                    break;
            }
        } else {
            $groupQuery->when($dtoRequest->createdDateGt, function ($query, $value) {
                $query->whereDate('created_at', '>=', $value);
            });

            $groupQuery->when($dtoRequest->createdDateLt, function ($query, $value) {
                $query->whereDate('created_at', '<=', $value);
            });
        }

        $groupQuery->when($dtoRequest->viewCountGt, function ($query, $value) {
            $query->where('view_count', '>=', $value);
        });

        $groupQuery->when($dtoRequest->viewCountLt, function ($query, $value) {
            $query->where('view_count', '<=', $value);
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

        if ($dtoRequest->orderType == 'random') {
            $groupQuery->inRandomOrder();
        } else {
            $orderType = match ($dtoRequest->orderType) {
                'createdTime' => 'created_at',
                'lastPostTime' => 'last_post_at',
                'lastCommentTime' => 'last_comment_at',
                'view' => 'view_count',
                'like' => 'like_count',
                'dislike' => 'dislike_count',
                'follow' => 'follow_count',
                'block' => 'block_count',
                'post' => 'post_count',
                'postDigest' => 'post_digest_count',
                'sortOrder' => 'sort_order',
                default => 'sort_order',
            };

            $orderDirection = match ($dtoRequest->orderDirection) {
                default => 'asc',
                'asc' => 'asc',
                'desc' => 'desc',
            };

            $groupQuery->orderBy('recommend_sort_order')->orderBy($orderType, $orderDirection);
        }

        $groupData = $groupQuery->paginate($dtoRequest->pageSize ?? 15);

        $groupOptions = [
            'viewType' => 'list',
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
        ];

        $groupList = [];
        foreach ($groupData as $group) {
            $groupList[] = DetailUtility::groupDetail($group, $langTag, $timezone, $authUserId, $groupOptions);
        }

        return $this->fresnsPaginate($groupList, $groupData->total(), $groupData->perPage());
    }

    // detail
    public function detail(string $gid, Request $request)
    {
        $dtoRequest = new DetailDTO($request->all());

        $group = Group::where('gid', $gid)->first();

        if (empty($group)) {
            throw new ApiException(37100);
        }

        if (! $group->is_enabled) {
            throw new ApiException(37101);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $seoData = PrimaryHelper::fresnsModelSeo(Seo::TYPE_GROUP, $group->id);

        $item['title'] = StrHelper::languageContent($seoData?->title, $langTag);
        $item['keywords'] = StrHelper::languageContent($seoData?->keywords, $langTag);
        $item['description'] = StrHelper::languageContent($seoData?->description, $langTag);
        $item['extensions'] = [];

        // check limit
        $checkLimit = PermissionUtility::getGroupContentDateLimit($group->id, $authUserId);
        if ($checkLimit['code'] == 0 && empty($checkLimit['datetime'])) {
            $item['extensions'] = ExtendUtility::getGroupExtensions($group->id, $langTag, $authUserId);
        }

        $groupOptions = [
            'viewType' => 'detail',
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
        ];

        $data = [
            'items' => $item,
            'detail' => DetailUtility::groupDetail($group, $langTag, $timezone, $authUserId, $groupOptions),
        ];

        return $this->success($data);
    }

    // creator
    public function creator(string $gid, Request $request)
    {
        $dtoRequest = new DetailDTO($request->all());

        $group = PrimaryHelper::fresnsModelByFsid('group', $gid);

        if (empty($group)) {
            throw new ApiException(37100);
        }

        if (! $group?->creator) {
            return $this->success();
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $userOptions = [
            'viewType' => 'quoted',
            'isLiveStats' => false,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
        ];

        $data = DetailUtility::userDetail($group->creator, $langTag, $timezone, $authUserId, $userOptions);

        return $this->success($data);
    }

    // admins
    public function admins(string $gid, Request $request)
    {
        $dtoRequest = new DetailDTO($request->all());

        $group = PrimaryHelper::fresnsModelByFsid('group', $gid);

        if (empty($group)) {
            throw new ApiException(37100);
        }

        if (! $group?->admins) {
            return $this->success([]);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $userOptions = [
            'viewType' => 'quoted',
            'isLiveStats' => false,
            'filter' => [
                'type' => $dtoRequest->filterType,
                'keys' => $dtoRequest->filterKeys,
            ],
        ];

        $users = [];
        foreach ($group->admins as $adminUser) {
            $users[] = DetailUtility::userDetail($adminUser, $langTag, $timezone, $authUserId, $userOptions);
        }

        return $this->success($users);
    }

    // interaction
    public function interaction(string $gid, string $type, Request $request)
    {
        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractionDTO($requestData);

        $group = PrimaryHelper::fresnsModelByFsid('group', $gid);

        if (empty($group)) {
            throw new ApiException(37100);
        }

        if (! $group->is_enabled) {
            throw new ApiException(37101);
        }

        InteractionService::checkInteractionSetting('group', $dtoRequest->type);

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $service = new InteractionService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractionService::TYPE_GROUP, $group->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactionData']->total(), $data['interactionData']->perPage());
    }
}
