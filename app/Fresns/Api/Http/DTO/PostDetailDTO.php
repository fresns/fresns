<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class PostDetailDTO extends DTO
{
    public function rules(): array
    {
        return [
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
        ];
    }
}
