<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class PostListDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'mapId' => ['integer', 'nullable', 'in:1,2,3,4,5,6,7,8,9,10'],
            'mapLng' => ['numeric', 'nullable'],
            'mapLat' => ['numeric', 'nullable'],
            'uid' => ['integer', 'nullable'], // posts->user_id
            'gid' => ['string', 'nullable'], // posts->group_id
            'hid' => ['string', 'nullable'], // hashtag_linkeds->hashtag_id
            'contentType' => ['string', 'nullable'], // posts->types
            'digestState' => ['integer', 'nullable', 'in:1,2,3'], // posts->digest_state
            'stickyState' => ['integer', 'nullable', 'in:1,2,3'], // posts->sticky_state
            'likeCountGt' => ['integer', 'nullable', 'lt:likeCountLt'], // posts->like_count
            'likeCountLt' => ['integer', 'nullable', 'gt:likeCountGt'], // posts->like_count
            'dislikeCountGt' => ['integer', 'nullable', 'lt:dislikeCountLt'], // posts->dislike_count
            'dislikeCountLt' => ['integer', 'nullable', 'gt:dislikeCountGt'], // posts->dislike_count
            'followCountGt' => ['integer', 'nullable', 'lt:followCountLt'], // posts->follow_count
            'followCountLt' => ['integer', 'nullable', 'gt:followCountGt'], // posts->follow_count
            'blockCountGt' => ['integer', 'nullable', 'lt:blockCountLt'], // posts->block_count
            'blockCountLt' => ['integer', 'nullable', 'gt:blockCountGt'], // posts->block_count
            'commentCountGt' => ['integer', 'nullable', 'lt:commentCountLt'], // posts->comment_count
            'commentCountLt' => ['integer', 'nullable', 'gt:commentCountGt'], // posts->comment_count
            'createTimeGt' => ['date_format:Y-m-d', 'nullable', 'before:createTimeLt'], // posts->created_at
            'createTimeLt' => ['date_format:Y-m-d', 'nullable', 'after:createTimeGt'], // posts->created_at
            'ratingType' => ['string', 'nullable', 'in:like,dislike,follow,block,comment,createTime'],
            'ratingOrder' => ['string', 'nullable', 'in:asc,desc'],
            'pluginRatingId' => ['integer', 'nullable'],
            'pageSize' => ['integer', 'nullable', 'between:1,20'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
