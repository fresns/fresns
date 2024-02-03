<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class SearchCommentDTO extends DTO
{
    public function rules(): array
    {
        return [
            'searchKey' => ['string', 'required'],
            'followUsers' => ['boolean', 'nullable'],
            'followGroups' => ['boolean', 'nullable'],
            'followHashtags' => ['boolean', 'nullable'],
            'followGeotags' => ['boolean', 'nullable'],
            'followComments' => ['boolean', 'nullable'],
            'uidOrUsername' => ['nullable'], // comments->user_id
            'pid' => ['string', 'nullable'], // comments->post_id
            'cid' => ['string', 'nullable'], // comments->top_parent_id
            'htid' => ['string', 'nullable'], // hashtag_usages->hashtag_id
            'gtid' => ['string', 'nullable'], // comments->geotag_id
            'gid' => ['string', 'nullable'], // comments->post_id->group_id
            'includeSubgroups' => ['boolean', 'nullable'],
            'allDigest' => ['boolean', 'nullable'],
            'digestState' => ['integer', 'nullable', 'in:1,2,3'], // comments->digest_state
            'sticky' => ['boolean', 'nullable'], // comments->is_sticky
            'langTag' => ['string', 'nullable'], // comments->lang_tag
            'contentType' => ['string', 'nullable'],
            'createdDays' => ['integer', 'nullable'],
            'createdDate' => ['string', 'nullable', 'in:today,yesterday,week,lastWeek,month,lastMonth,year,lastYear'],
            'createdDateGt' => ['date_format:Y-m-d', 'nullable', 'before:createdDateLt'], // comments->created_at
            'createdDateLt' => ['date_format:Y-m-d', 'nullable', 'after:createdDateGt'],
            'viewCountGt' => ['integer', 'nullable', 'lt:viewCountLt'], // comments->view_count
            'viewCountLt' => ['integer', 'nullable', 'gt:viewCountGt'],
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
            'blockUsers' => ['string', 'nullable'],
            'blockGroups' => ['string', 'nullable'],
            'blockHashtags' => ['string', 'nullable'],
            'blockGeotags' => ['string', 'nullable'],
            'blockPosts' => ['string', 'nullable'],
            'blockComments' => ['string', 'nullable'],
            'sinceCid' => ['string', 'nullable'],
            'beforeCid' => ['string', 'nullable'],
            'orderType' => ['string', 'nullable', 'in:createdTime,commentTime,random,view,like,dislike,follow,block,comment'],
            'orderDirection' => ['string', 'nullable', 'in:asc,desc'],
            'mapId' => ['integer', 'nullable', 'between:1,11'],
            'mapLng' => ['numeric', 'nullable', 'min:-180', 'max:180'],
            'mapLat' => ['numeric', 'nullable', 'min:-90', 'max:90'],
            'filterType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterKeys' => ['string', 'nullable', 'required_with:filterType'],
            'filterHashtagType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterHashtagKeys' => ['string', 'nullable', 'required_with:filterHashtagType'],
            'filterGeotagType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterGeotagKeys' => ['string', 'nullable', 'required_with:filterGeotagType'],
            'filterAuthorType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterAuthorKeys' => ['string', 'nullable', 'required_with:filterAuthorType'],
            'filterPreviewLikeUserType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterPreviewLikeUserKeys' => ['string', 'nullable', 'required_with:filterPreviewLikeUserType'],
            'filterPreviewCommentType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterPreviewCommentKeys' => ['string', 'nullable', 'required_with:filterPreviewCommentType'],
            'filterReplyToPostType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterReplyToPostKeys' => ['string', 'nullable', 'required_with:filterReplyToPostType'],
            'filterReplyToCommentType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterReplyToCommentKeys' => ['string', 'nullable', 'required_with:filterReplyToCommentType'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
