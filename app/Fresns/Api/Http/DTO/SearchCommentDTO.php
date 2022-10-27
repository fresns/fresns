<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class SearchCommentDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'searchKey' => ['string', 'required'],
            'mapId' => ['integer', 'nullable', 'in:1,2,3,4,5,6,7,8,9,10'],
            'mapLng' => ['numeric', 'nullable'],
            'mapLat' => ['numeric', 'nullable'],
            'uidOrUsername' => ['string', 'nullable'], // comments->user_id
            'pid' => ['string', 'nullable'], // comments->post_id
            'cid' => ['string', 'nullable'], // comments->parent_id
            'gid' => ['string', 'nullable'], // comments->post_id->group_id
            'hid' => ['string', 'nullable'],
            'sticky' => ['boolean', 'nullable'], // comments->is_sticky
            'digestState' => ['integer', 'nullable', 'in:1,2,3'], // comments->digest_state
            'contentType' => ['string', 'nullable'],
            'createDateGt' => ['date_format:Y-m-d', 'nullable', 'before:createDateLt'], // comments->created_at
            'createDateLt' => ['date_format:Y-m-d', 'nullable', 'after:createDateGt'],
            'likeCountGt' => ['integer', 'nullable', 'lt:likeCountLt'], // comments->like_count
            'likeCountLt' => ['integer', 'nullable', 'gt:likeCountGt'],
            'dislikeCountGt' => ['integer', 'nullable', 'lt:dislikeCountLt'], // comments->dislike_count
            'dislikeCountLt' => ['integer', 'nullable', 'gt:dislikeCountGt'],
            'followCountGt' => ['integer', 'nullable', 'lt:followCountLt'], // comments->follow_count
            'followCountLt' => ['integer', 'nullable', 'gt:followCountGt'],
            'blockCountGt' => ['integer', 'nullable', 'lt:blockCountLt'], // comments->block_count
            'blockCountLt' => ['integer', 'nullable', 'gt:blockCountGt'],
            'commentCountGt' => ['integer', 'nullable', 'lt:commentCountLt'], // comments->comment_count
            'commentCountLt' => ['integer', 'nullable', 'gt:commentCountGt'],
            'orderType' => ['string', 'nullable', 'in:createDate,like,dislike,follow,block,comment'],
            'orderDirection' => ['string', 'nullable', 'in:asc,desc'],
            'pluginRatingId' => ['integer', 'nullable'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
