<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class PostNearbyDTO extends DTO
{
    public function rules(): array
    {
        return [
            'mapId' => ['integer', 'required', 'between:1,11'],
            'mapLng' => ['numeric', 'required', 'min:-180', 'max:180'],
            'mapLat' => ['numeric', 'required', 'min:-90', 'max:90'],
            'unit' => ['string', 'nullable', 'in:km,mi'],
            'length' => ['integer', 'nullable'],
            'langTag' => ['string', 'nullable'], // posts->lang_tag
            'contentType' => ['string', 'nullable'],
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
