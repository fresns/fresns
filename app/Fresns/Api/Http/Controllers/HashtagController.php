<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Helpers\PrimaryHelper;
use App\Fresns\Api\Services\HeaderService;
use App\Fresns\Api\Services\HashtagService;
use App\Fresns\Api\Services\InteractiveService;
use App\Models\Hashtag;
use App\Models\Seo;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Fresns\Api\Http\DTO\HashtagListDTO;
use App\Fresns\Api\Http\DTO\InteractiveDTO;
use App\Models\UserBlock;
use App\Helpers\ConfigHelper;

class HashtagController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new HashtagListDTO($request->all());

        $headers = HeaderService::getHeaders();

        $authUserId = null;
        if (! empty($headers['uid'])) {
            $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);
        }

        $blockHashtagIds = UserBlock::type(UserBlock::TYPE_HASHTAG)->where('user_id', $authUserId)->pluck('block_id')->toArray();

        $hashtagQuery = Hashtag::whereNotIn('id', $blockHashtagIds)->isEnable();

        if ($dtoRequest->likeCountGt) {
            $hashtagQuery->where('like_count', '>=', $dtoRequest->likeCountGt);
        }

        if ($dtoRequest->likeCountLt) {
            $hashtagQuery->where('like_count', '<=', $dtoRequest->likeCountLt);
        }

        if ($dtoRequest->dislikeCountGt) {
            $hashtagQuery->where('dislike_count', '>=', $dtoRequest->dislikeCountGt);
        }

        if ($dtoRequest->dislikeCountLt) {
            $hashtagQuery->where('dislike_count', '<=', $dtoRequest->dislikeCountLt);
        }

        if ($dtoRequest->followCountGt) {
            $hashtagQuery->where('follow_count', '>=', $dtoRequest->followCountGt);
        }

        if ($dtoRequest->followCountLt) {
            $hashtagQuery->where('follow_count', '<=', $dtoRequest->followCountLt);
        }

        if ($dtoRequest->blockCountGt) {
            $hashtagQuery->where('block_count', '>=', $dtoRequest->blockCountGt);
        }

        if ($dtoRequest->blockCountLt) {
            $hashtagQuery->where('block_count', '<=', $dtoRequest->blockCountLt);
        }

        if ($dtoRequest->postCountGt) {
            $hashtagQuery->where('post_count', '>=', $dtoRequest->postCountGt);
        }

        if ($dtoRequest->postCountLt) {
            $hashtagQuery->where('post_count', '<=', $dtoRequest->postCountLt);
        }

        if ($dtoRequest->postDigestCountGt) {
            $hashtagQuery->where('post_digest_count', '>=', $dtoRequest->postDigestCountGt);
        }

        if ($dtoRequest->postDigestCountLt) {
            $hashtagQuery->where('post_digest_count', '<=', $dtoRequest->postDigestCountLt);
        }

        if ($dtoRequest->createTimeGt) {
            $hashtagQuery->where('created_at', '>=', $dtoRequest->createTimeGt);
        }

        if ($dtoRequest->createTimeLt) {
            $hashtagQuery->where('created_at', '<=', $dtoRequest->createTimeLt);
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

        $hashtagQuery->orderBy($ratingType, $ratingOrder);

        $hashtagData = $hashtagQuery->paginate($request->get('pageSize', 30));

        $hashtagList = [];
        $service = new HashtagService();
        foreach ($hashtagData as $hashtag) {
            $hashtagList[] = $service->hashtagList($hashtag, $headers['langTag'], $authUserId);
        }

        return $this->fresnsPaginate($hashtagList, $hashtagData->total(), $hashtagData->perPage());
    }

    // detail
    public function detail(string $hid)
    {
        $hashtag = Hashtag::whereSlug($hid)->isEnable()->first();
        if (empty($hashtag)) {
            throw new ApiException(37200);
        }

        $headers = HeaderService::getHeaders();

        $authUserId = null;
        if (! empty($headers['uid'])) {
            $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);
        }

        $seoData = Seo::where('linked_type', Seo::TYPE_HASHTAG)->where('linked_id', $hashtag->id)->where('lang_tag', $headers['langTag'])->first();

        $common['title'] = $seoData->title ?? null;
        $common['keywords'] = $seoData->keywords ?? null;
        $common['description'] = $seoData->description ?? null;
        $data['commons'] = $common;

        $service = new HashtagService();
        $data['detail'] = $service->hashtagDetail($hashtag, $headers['langTag'], $authUserId);

        return $this->success($data);
    }

    // interactive
    public function interactive(string $hid, string $type, Request $request)
    {
        $hashtag = Hashtag::whereSlug($hid)->isEnable()->first();
        if (empty($hashtag)) {
            throw new ApiException(37200);
        }

        $requestData = $request->all();
        $requestData['type'] = $type;
        $dtoRequest = new InteractiveDTO($requestData);

        $markSet = ConfigHelper::fresnsConfigByItemKey("it_{$dtoRequest->type}_hashtags");
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
        $data = $service->getUsersWhoMarkIt($dtoRequest->type, InteractiveService::TYPE_HASHTAG, $hashtag->id, $timeOrder, $headers['langTag'], $headers['timezone'], $authUserId);

        return $this->fresnsPaginate($data['paginateData'], $data['interactiveData']->total(), $data['interactiveData']->perPage());
    }
}
