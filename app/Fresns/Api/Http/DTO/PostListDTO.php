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
            'uidOrUsername' => ['nullable'], // posts->user_id
            'htid' => ['string', 'nullable'], // hashtag_usages->hashtag_id
            'gtid' => ['string', 'nullable'], // geotag_usages->geotag_id
            'gid' => ['string', 'nullable'], // posts->group_id
            'includeSubgroups' => ['boolean', 'nullable'],
            'langTag' => ['string', 'nullable'], // posts->lang_tag
            'contentType' => ['string', 'nullable'],
            'allDigest' => ['boolean', 'nullable'],
            'digestState' => ['integer', 'nullable', 'in:1,2,3'], // posts->digest_state
            'stickyState' => ['integer', 'nullable', 'in:1,2,3'], // posts->sticky_state
            'createdDays' => ['integer', 'nullable'],
            'createdDate' => ['string', 'nullable', 'in:today,yesterday,week,lastWeek,month,lastMonth,year,lastYear'],
            'createdDateGt' => ['date_format:Y-m-d', 'nullable', 'before:createdDateLt'], // posts->created_at
            'createdDateLt' => ['date_format:Y-m-d', 'nullable', 'after:createdDateGt'],
            'viewCountGt' => ['integer', 'nullable', 'lt:viewCountLt'], // posts->view_count
            'viewCountLt' => ['integer', 'nullable', 'gt:viewCountGt'],
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
            'blockUsers' => ['string', 'nullable'],
            'blockGroups' => ['string', 'nullable'],
            'blockHashtags' => ['string', 'nullable'],
            'blockGeotags' => ['string', 'nullable'],
            'sincePid' => ['string', 'nullable'],
            'beforePid' => ['string', 'nullable'],
            'orderType' => ['string', 'nullable', 'in:createdTime,commentTime,random,view,like,dislike,follow,block,comment'],
            'orderDirection' => ['string', 'nullable', 'in:asc,desc'],
            'mapId' => ['integer', 'nullable', 'between:1,11'],
            'mapLng' => ['numeric', 'nullable', 'min:-180', 'max:180'],
            'mapLat' => ['numeric', 'nullable', 'min:-90', 'max:90'],
            'filterType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterKeys' => ['string', 'nullable', 'required_with:filterType'],
            'filterGroupType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterGroupKeys' => ['string', 'nullable', 'required_with:filterGroupType'],
            'filterHashtagType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterHashtagKeys' => ['string', 'nullable', 'required_with:filterHashtagType'],
            'filterGeotagType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterGeotagKeys' => ['string', 'nullable', 'required_with:filterGeotagType'],
            'filterAuthorType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterAuthorKeys' => ['string', 'nullable', 'required_with:filterAuthorType'],
            'filterQuotedPostType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterQuotedPostKeys' => ['string', 'nullable', 'required_with:filterQuotedPostType'],
            'filterPreviewLikeUserType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterPreviewLikeUserKeys' => ['string', 'nullable', 'required_with:filterPreviewLikeUserType'],
            'filterPreviewCommentType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterPreviewCommentKeys' => ['string', 'nullable', 'required_with:filterPreviewCommentType'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
