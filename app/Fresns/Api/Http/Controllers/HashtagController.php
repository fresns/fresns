<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\HashtagListDTO;
use App\Fresns\Api\Http\DTO\InteractionDTO;
use App\Fresns\Api\Services\HashtagService;
use App\Fresns\Api\Services\InteractionService;
use App\Helpers\LanguageHelper;
use App\Helpers\StrHelper;
use App\Models\Hashtag;
use App\Utilities\InteractionUtility;
use Illuminate\Http\Request;

class HashtagController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new HashtagListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $hashtagQuery = Hashtag::isEnabled();

        $blockHashtagIds = InteractionUtility::getBlockIdArr(InteractionUtility::TYPE_HASHTAG, $authUserId);
        $hashtagQuery->when($blockHashtagIds, function ($query, $value) {
            $query->whereNotIn('id', $value);
        });

        if ($dtoRequest->createdDate) {
            switch ($dtoRequest->createdDate) {
                case 'today':
                    $hashtagQuery->whereDate('created_at', now()->format('Y-m-d'));
                    break;

                case 'yesterday':
                    $hashtagQuery->whereDate('created_at', now()->subDay()->format('Y-m-d'));
                    break;

                case 'week':
                    $hashtagQuery->whereDate('created_at', '>=', now()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'lastWeek':
                    $hashtagQuery->whereDate('created_at', '>=', now()->subWeek()->startOfWeek()->format('Y-m-d'))
                        ->whereDate('created_at', '<=', now()->subWeek()->endOfWeek()->format('Y-m-d'));
                    break;

                case 'month':
                    $hashtagQuery->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;

                case 'lastMonth':
                    $lastMonth = now()->subMonth()->month;
                    $year = now()->year;
                    if ($lastMonth == 12) {
                        $year = now()->subYear()->year;
                    }
                    $hashtagQuery->whereMonth('created_at', $lastMonth)->whereYear('created_at', $year);
                    break;

                case 'year':
                    $hashtagQuery->whereYear('created_at', now()->year);
                    break;

                case 'lastYear':
                    $hashtagQuery->whereYear('created_at', now()->subYear()->year);
                    break;
            }
        } else {
            $hashtagQuery->when($dtoRequest->createdDateGt, function ($query, $value) {
                $query->whereDate('created_at', '>=', $value);
            });

            $hashtagQuery->when($dtoRequest->createdDateLt, function ($query, $value) {
                $query->whereDate('created_at', '<=', $value);
            });
        }

        $hashtagQuery->when($dtoRequest->viewCountGt, function ($query, $value) {
            $query->where('view_count', '>=', $value);
        });

        $hashtagQuery->when($dtoRequest->viewCountLt, function ($query, $value) {
            $query->where('view_count', '<=', $value);
        });

        $hashtagQuery->when($dtoRequest->likeCountGt, function ($query, $value) {
            $query->where('like_count', '>=', $value);
        });

        $hashtagQuery->when($dtoRequest->likeCountLt, function ($query, $value) {
            $query->where('like_count', '<=', $value);
        });

        $hashtagQuery->when($dtoRequest->dislikeCountGt, function ($query, $value) {
            $query->where('dislike_count', '>=', $value);
        });

        $hashtagQuery->when($dtoRequest->dislikeCountLt, function ($query, $value) {
            $query->where('dislike_count', '<=', $value);
        });

        $hashtagQuery->when($dtoRequest->followCountGt, function ($query, $value) {
            $query->where('follow_count', '>=', $value);
        });

        $hashtagQuery->when($dtoRequest->followCountLt, function ($query, $value) {
            $query->where('follow_count', '<=', $value);
        });

        $hashtagQuery->when($dtoRequest->blockCountGt, function ($query, $value) {
            $query->where('block_count', '>=', $value);
        });

        $hashtagQuery->when($dtoRequest->blockCountLt, function ($query, $value) {
            $query->where('block_count', '<=', $value);
        });

        $hashtagQuery->when($dtoRequest->postCountGt, function ($query, $value) {
            $query->where('post_count', '>=', $value);
        });

        $hashtagQuery->when($dtoRequest->postCountLt, function ($query, $value) {
            $query->where('post_count', '<=', $value);
        });

        $hashtagQuery->when($dtoRequest->postDigestCountGt, function ($query, $value) {
            $query->where('post_digest_count', '>=', $value);
        });

        $hashtagQuery->when($dtoRequest->postDigestCountLt, function ($query, $value) {
            $query->where('post_digest_count', '<=', $value);
        });

        if ($dtoRequest->orderType == 'random') {
            $hashtagQuery->inRandomOrder();
        } else {
            $orderType = match ($dtoRequest->orderType) {
                default => 'created_at',
                'createdTime' => 'created_at',
                'view' => 'view_count',
                'like' => 'like_count',
                'dislike' => 'dislike_count',
                'follow' => 'follow_count',
                'block' => 'block_count',
                'post' => 'post_count',
                'postDigest' => 'post_digest_count',
            };

            $orderDirection = match ($dtoRequest->orderDirection) {
                default => 'desc',
                'asc' => 'asc',
                'desc' => 'desc',
            };

            $hashtagQuery->orderBy($orderType, $orderDirection);
        }

        $hashtagData = $hashtagQuery->paginate($dtoRequest->pageSize ?? 30);

        $hashtagList = [];
        $service = new HashtagService();
        foreach ($hashtagData as $hashtag) {
            $hashtagList[] = $service->hashtagData($hashtag, $langTag, $timezone, $authUserId);
        }

        return $this->fresnsPaginate($hashtagList, $hashtagData->total(), $hashtagData->perPage());
    }

    // detail
    public function detail(string $hid)
    {
        $hid = StrHelper::slug($hid);

        $hashtag = Hashtag::where('slug', $hid)->first();

        if (empty($hashtag)) {
            throw new ApiException(37200);
        }

        if (! $hashtag->is_enabled) {
            throw new ApiException(37201);
        }

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $seoData = LanguageHelper::fresnsLanguageSeoDataById('hashtag', $hashtag->id, $langTag);

        $item['title'] = $seoData?->title;
        $item['keywords'] = $seoData?->keywords;
        $item['description'] = $seoData?->description;
        $data['items'] = $item;

        $service = new HashtagService();
        $data['detail'] = $service->hashtagData($hashtag, $langTag, $timezone, $authUserId);

        return $this->success($data);
    }

    // interaction
    public function interaction(string $hid, string $type, Request $request)
    {
        $hashtag = Hashtag::where('slug', $hid)->isEnabled()->first();
        if (empty($hashtag)) {
            throw new ApiException(37200);
        }

        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractionDTO($requestData);

        InteractionService::checkInteractionSetting($dtoRequest->type, 'hashtag');

        $orderDirection = $dtoRequest->orderDirection ?: 'desc';

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()?->id;

        $service = new InteractionService();
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractionService::TYPE_HASHTAG, $hashtag->id, $orderDirection, $langTag, $timezone, $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactionData']->total(), $data['interactionData']->perPage());
    }
}
