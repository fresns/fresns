<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class SearchPostDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'key' => ['string', 'required'],
            'mapId' => ['integer', 'nullable', 'in:1,2,3,4,5,6,7,8,9,10'],
            'mapLng' => ['numeric', 'nullable'],
            'mapLat' => ['numeric', 'nullable'],
            'uidOrUsername' => ['string', 'nullable'], // posts->user_id
            'gid' => ['string', 'nullable'], // posts->group_id
            'hid' => ['string', 'nullable'], // hashtag_usages->hashtag_id
            'digestState' => ['integer', 'nullable', 'in:1,2,3'], // posts->digest_state
            'stickyState' => ['integer', 'nullable', 'in:1,2,3'], // posts->sticky_state
            'contentType' => ['string', 'nullable'], // posts->types
            'createDateGt' => ['date_format:Y-m-d', 'nullable', 'before:createDateLt'], // posts->created_at
            'createDateLt' => ['date_format:Y-m-d', 'nullable', 'after:createDateGt'],
            'likeCountGt' => ['integer', 'nullable', 'lt:likeCountLt'], // posts->like_count
            'likeCountLt' => ['integer', 'nullable', 'gt:likeCountGt'],
            'dislikeCountGt' => ['integer', 'nullable', 'lt:dislikeCountLt'], // posts->dislike_count
            'dislikeCountLt' => ['integer', 'nullable', 'gt:dislikeCountGt'],
            'followCountGt' => ['integer', 'nullable', 'lt:followCountLt'], // posts->follow_count
            'followCountLt' => ['integer', 'nullable', 'gt:followCountGt'],
            'blockCountGt' => ['integer', 'nullable', 'lt:blockCountLt'], // posts->block_count
            'blockCountLt' => ['integer', 'nullable', 'gt:blockCountGt'],
            'commentCountGt' => ['integer', 'nullable', 'lt:commentCountLt'], // posts->comment_count
            'commentCountLt' => ['integer', 'nullable', 'gt:commentCountGt'],
            'orderType' => ['string', 'nullable', 'in:createDate,like,dislike,follow,block,comment'],
            'orderDirection' => ['string', 'nullable', 'in:asc,desc'],
            'pluginRatingId' => ['integer', 'nullable'],
            'pageSize' => ['integer', 'nullable', 'between:1,25'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
