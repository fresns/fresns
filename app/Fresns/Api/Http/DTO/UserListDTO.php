<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class UserListDTO extends DTO
{
    public function rules(): array
    {
        return [
            'roles' => ['string', 'nullable'],
            'verified' => ['boolean', 'nullable'],
            'gender' => ['integer', 'nullable', 'in:1,2,3,4'],
            'createdDays' => ['integer', 'nullable'],
            'createdDate' => ['string', 'nullable', 'in:today,yesterday,week,lastWeek,month,lastMonth,year,lastYear'],
            'createdDateGt' => ['date_format:Y-m-d', 'nullable', 'before:createdDateLt'], // user_stats->created_at
            'createdDateLt' => ['date_format:Y-m-d', 'nullable', 'after:createdDateGt'],
            'viewCountGt' => ['integer', 'nullable', 'lt:viewCountLt'], // user_stats->view_count
            'viewCountLt' => ['integer', 'nullable', 'gt:viewCountGt'],
            'likeCountGt' => ['integer', 'nullable', 'lt:likeCountLt'], // user_stats->liker_count
            'likeCountLt' => ['integer', 'nullable', 'gt:likeCountGt'],
            'dislikeCountGt' => ['integer', 'nullable', 'lt:dislikeCountLt'], // user_stats->disliker_count
            'dislikeCountLt' => ['integer', 'nullable', 'gt:dislikeCountGt'],
            'followCountGt' => ['integer', 'nullable', 'lt:followCountLt'], // user_stats->follower_count
            'followCountLt' => ['integer', 'nullable', 'gt:followCountGt'],
            'blockCountGt' => ['integer', 'nullable', 'lt:blockCountLt'], // user_stats->blocker_count
            'blockCountLt' => ['integer', 'nullable', 'gt:blockCountGt'],
            'postCountGt' => ['integer', 'nullable', 'lt:postCountLt'], // user_stats->post_publish_count
            'postCountLt' => ['integer', 'nullable', 'gt:postCountGt'],
            'commentCountGt' => ['integer', 'nullable', 'lt:commentCountLt'], // user_stats->comment_publish_count
            'commentCountLt' => ['integer', 'nullable', 'gt:commentCountGt'],
            'postDigestCountGt' => ['integer', 'nullable', 'lt:postDigestCountLt'], // user_stats->post_digest_count
            'postDigestCountLt' => ['integer', 'nullable', 'gt:postDigestCountGt'],
            'commentDigestCountGt' => ['integer', 'nullable', 'lt:commentDigestCountLt'], // user_stats->comment_digest_count
            'commentDigestCountLt' => ['integer', 'nullable', 'gt:commentDigestCountGt'],
            'extcredits1CountGt' => ['integer', 'nullable', 'lt:extcredits1CountLt'], // user_stats->extcredits1
            'extcredits1CountLt' => ['integer', 'nullable', 'gt:extcredits1CountGt'],
            'extcredits2CountGt' => ['integer', 'nullable', 'lt:extcredits2CountLt'], // user_stats->extcredits2
            'extcredits2CountLt' => ['integer', 'nullable', 'gt:extcredits2CountGt'],
            'extcredits3CountGt' => ['integer', 'nullable', 'lt:extcredits3CountLt'], // user_stats->extcredits3
            'extcredits3CountLt' => ['integer', 'nullable', 'gt:extcredits3CountGt'],
            'extcredits4CountGt' => ['integer', 'nullable', 'lt:extcredits4CountLt'], // user_stats->extcredits4
            'extcredits4CountLt' => ['integer', 'nullable', 'gt:extcredits4CountGt'],
            'extcredits5CountGt' => ['integer', 'nullable', 'lt:extcredits5CountLt'], // user_stats->extcredits5
            'extcredits5CountLt' => ['integer', 'nullable', 'gt:extcredits5CountGt'],
            'orderType' => ['string', 'nullable', 'in:createdTime,random,view,like,dislike,follow,block,post,comment,postDigest,commentDigest,extcredits1,extcredits2,extcredits3,extcredits4,extcredits5'],
            'orderDirection' => ['string', 'nullable', 'in:asc,desc'],
            'filterType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterKeys' => ['string', 'nullable', 'required_with:filterType'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
