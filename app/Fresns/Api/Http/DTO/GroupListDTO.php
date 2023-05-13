<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class GroupListDTO extends DTO
{
    public function rules(): array
    {
        return [
            'gid' => ['string', 'nullable'],
            'recommend' => ['boolean', 'nullable'],
            'createDate' => ['string', 'nullable', 'in:today,yesterday,week,lastWeek,month,lastMonth,year,lastYear'],
            'createDateGt' => ['date_format:Y-m-d', 'nullable', 'before:createDateLt'], // groups->created_at
            'createDateLt' => ['date_format:Y-m-d', 'nullable', 'after:createDateGt'],
            'likeCountGt' => ['integer', 'nullable', 'lt:likeCountLt'], // groups->like_count
            'likeCountLt' => ['integer', 'nullable', 'gt:likeCountGt'],
            'dislikeCountGt' => ['integer', 'nullable', 'lt:dislikeCountLt'], // groups->dislike_count
            'dislikeCountLt' => ['integer', 'nullable', 'gt:dislikeCountGt'],
            'followCountGt' => ['integer', 'nullable', 'lt:followCountLt'], // groups->follow_count
            'followCountLt' => ['integer', 'nullable', 'gt:followCountGt'],
            'blockCountGt' => ['integer', 'nullable', 'lt:blockCountLt'], // groups->block_count
            'blockCountLt' => ['integer', 'nullable', 'gt:blockCountGt'],
            'postCountGt' => ['integer', 'nullable', 'lt:postCountLt'], // groups->post_count
            'postCountLt' => ['integer', 'nullable', 'gt:postCountGt'],
            'postDigestCountGt' => ['integer', 'nullable', 'lt:postDigestCountLt'], // groups->post_digest_count
            'postDigestCountLt' => ['integer', 'nullable', 'gt:postDigestCountGt'],
            'orderType' => ['string', 'nullable', 'in:createDate,random,like,follow,block,post,postDigest,rating'],
            'orderDirection' => ['string', 'nullable', 'in:asc,desc'],
            'whitelistKeys' => ['string', 'nullable'],
            'blacklistKeys' => ['string', 'nullable'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
