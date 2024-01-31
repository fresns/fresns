<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class PostQuotesDTO extends DTO
{
    public function rules(): array
    {
        return [
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
