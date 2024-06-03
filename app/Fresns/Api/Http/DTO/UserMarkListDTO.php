<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class UserMarkListDTO extends DTO
{
    public function rules(): array
    {
        return [
            'markType' => ['string', 'required', 'in:like,dislike,follow,block'],
            'listType' => ['string', 'required', 'in:users,groups,hashtags,geotags,posts,comments'],
            'orderDirection' => ['string', 'nullable', 'in:asc,desc'],
            'filterType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterKeys' => ['string', 'nullable', 'required_with:filterType'],
            'filterGroupType' => ['string', 'nullable', 'in:whitelist,blacklist'], // post
            'filterGroupKeys' => ['string', 'nullable', 'required_with:filterGroupType'], // post
            'filterHashtagType' => ['string', 'nullable', 'in:whitelist,blacklist'], // post and comment
            'filterHashtagKeys' => ['string', 'nullable', 'required_with:filterHashtagType'], // post and comment
            'filterGeotagType' => ['string', 'nullable', 'in:whitelist,blacklist'], // post and comment
            'filterGeotagKeys' => ['string', 'nullable', 'required_with:filterGeotagType'], // post and comment
            'filterAuthorType' => ['string', 'nullable', 'in:whitelist,blacklist'], // post and comment
            'filterAuthorKeys' => ['string', 'nullable', 'required_with:filterAuthorType'], // post and comment
            'filterQuotedPostType' => ['string', 'nullable', 'in:whitelist,blacklist'], // post
            'filterQuotedPostKeys' => ['string', 'nullable', 'required_with:filterQuotedPostType'], // post
            'filterPreviewLikeUserType' => ['string', 'nullable', 'in:whitelist,blacklist'], // post and comment
            'filterPreviewLikeUserKeys' => ['string', 'nullable', 'required_with:filterPreviewLikeUserType'], // post and comment
            'filterPreviewCommentType' => ['string', 'nullable', 'in:whitelist,blacklist'], // post and comment
            'filterPreviewCommentKeys' => ['string', 'nullable', 'required_with:filterPreviewCommentType'], // post and comment
            'filterReplyToPostType' => ['string', 'nullable', 'in:whitelist,blacklist'], // comment
            'filterReplyToPostKeys' => ['string', 'nullable', 'required_with:filterReplyToPostType'], // comment
            'filterReplyToCommentType' => ['string', 'nullable', 'in:whitelist,blacklist'], // comment
            'filterReplyToCommentKeys' => ['string', 'nullable', 'required_with:filterReplyToCommentType'], // comment
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
