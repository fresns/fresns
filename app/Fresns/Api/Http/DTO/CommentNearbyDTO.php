<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class CommentNearbyDTO extends DTO
{
    public function rules(): array
    {
        return [
            'mapId' => ['integer', 'required', 'between:1,11'],
            'mapLng' => ['numeric', 'required', 'min:-180', 'max:180'],
            'mapLat' => ['numeric', 'required', 'min:-90', 'max:90'],
            'unit' => ['string', 'nullable', 'in:km,mi'],
            'length' => ['integer', 'nullable'],
            'langTag' => ['string', 'nullable'], // comments->lang_tag
            'contentType' => ['string', 'nullable'],
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
