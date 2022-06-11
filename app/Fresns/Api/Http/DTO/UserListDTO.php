<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class UserListDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'verified' => ['boolean', 'nullable'],
            'gender' => ['integer', 'nullable', 'in:1,2,3'],
            'createTimeGt' => ['date_format:Y-m-d', 'nullable', 'before:createTimeLt'], // user_stats->created_at
            'createTimeLt' => ['date_format:Y-m-d', 'nullable', 'after:createTimeGt'], // user_stats->created_at
            'likeCountGt' => ['integer', 'nullable', 'lt:likeCountLt'], // user_stats->like_me_count
            'likeCountLt' => ['integer', 'nullable', 'gt:likeCountGt'], // user_stats->like_me_count
            'dislikeCountGt' => ['integer', 'nullable', 'lt:dislikeCountLt'], // user_stats->dislike_me_count
            'dislikeCountLt' => ['integer', 'nullable', 'gt:dislikeCountGt'], // user_stats->dislike_me_count
            'followCountGt' => ['integer', 'nullable', 'lt:followCountLt'], // user_stats->follow_me_count
            'followCountLt' => ['integer', 'nullable', 'gt:followCountGt'], // user_stats->follow_me_count
            'blockCountGt' => ['integer', 'nullable', 'lt:blockCountLt'], // user_stats->block_me_count
            'blockCountLt' => ['integer', 'nullable', 'gt:blockCountGt'], // user_stats->block_me_count
            'postCountGt' => ['integer', 'nullable', 'lt:postCountLt'], // user_stats->post_publish_count
            'postCountLt' => ['integer', 'nullable', 'gt:postCountGt'], // user_stats->post_publish_count
            'commentCountGt' => ['integer', 'nullable', 'lt:commentCountLt'], // user_stats->comment_publish_count
            'commentCountLt' => ['integer', 'nullable', 'gt:commentCountGt'], // user_stats->comment_publish_count
            'postDigestCountGt' => ['integer', 'nullable', 'lt:postDigestCountLt'], // user_stats->post_digest_count
            'postDigestCountLt' => ['integer', 'nullable', 'gt:postDigestCountGt'], // user_stats->post_digest_count
            'commentDigestCountGt' => ['integer', 'nullable', 'lt:commentDigestCountLt'], // user_stats->comment_digest_count
            'commentDigestCountLt' => ['integer', 'nullable', 'gt:commentDigestCountGt'], // user_stats->comment_digest_count
            'extcredits1CountGt' => ['integer', 'nullable', 'lt:extcredits1CountLt'], // user_stats->extcredits1
            'extcredits1CountLt' => ['integer', 'nullable', 'gt:extcredits1CountGt'], // user_stats->extcredits1
            'extcredits2CountGt' => ['integer', 'nullable', 'lt:extcredits2CountLt'], // user_stats->extcredits2
            'extcredits2CountLt' => ['integer', 'nullable', 'gt:extcredits2CountGt'], // user_stats->extcredits2
            'extcredits3CountGt' => ['integer', 'nullable', 'lt:extcredits3CountLt'], // user_stats->extcredits3
            'extcredits3CountLt' => ['integer', 'nullable', 'gt:extcredits3CountGt'], // user_stats->extcredits3
            'extcredits4CountGt' => ['integer', 'nullable', 'lt:extcredits4CountLt'], // user_stats->extcredits4
            'extcredits4CountLt' => ['integer', 'nullable', 'gt:extcredits4CountGt'], // user_stats->extcredits4
            'extcredits5CountGt' => ['integer', 'nullable', 'lt:extcredits5CountLt'], // user_stats->extcredits5
            'extcredits5CountLt' => ['integer', 'nullable', 'gt:extcredits5CountGt'], // user_stats->extcredits5
            'ratingType' => ['string', 'nullable', 'in:like,dislike,follow,block,post,comment,postDigest,commentDigest,extcredits1,extcredits2,extcredits3,extcredits4,extcredits5,createTime'],
            'ratingOrder' => ['string', 'nullable', 'in:asc,desc'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
