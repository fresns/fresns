<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class PostListDTO extends DTO
{
    public function rules(): array
    {
        return [
            'mapId' => ['integer', 'nullable', 'between:1,11'],
            'mapLng' => ['numeric', 'nullable', 'min:-180', 'max:180'],
            'mapLat' => ['numeric', 'nullable', 'min:-90', 'max:90'],
            'uidOrUsername' => ['string', 'nullable'], // posts->user_id
            'hid' => ['string', 'nullable'], // hashtag_usages->hashtag_id
            'gid' => ['string', 'nullable'], // posts->group_id
            'includeSubgroups' => ['boolean', 'nullable'],
            'allDigest' => ['boolean', 'nullable'],
            'digestState' => ['integer', 'nullable', 'in:1,2,3'], // posts->digest_state
            'stickyState' => ['integer', 'nullable', 'in:1,2,3'], // posts->sticky_state
            'contentType' => ['string', 'nullable'],
            'createDate' => ['string', 'nullable', 'in:today,yesterday,week,lastWeek,month,lastMonth,year,lastYear'],
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
            'orderType' => ['string', 'nullable', 'in:createDate,random,like,dislike,follow,block,comment'],
            'orderDirection' => ['string', 'nullable', 'in:asc,desc'],
            'blockGroups' => ['string', 'nullable'],
            'blockHashtags' => ['string', 'nullable'],
            'whitelistKeys' => ['string', 'nullable'],
            'blacklistKeys' => ['string', 'nullable'],
            'pluginRatingId' => ['integer', 'nullable'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
