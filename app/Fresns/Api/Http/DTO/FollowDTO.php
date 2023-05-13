<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class FollowDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:all,user,group,hashtag'],
            'contentType' => ['string', 'nullable'],
            'mapId' => ['integer', 'nullable', 'between:1,11'],
            'mapLng' => ['numeric', 'nullable', 'min:-180', 'max:180'],
            'mapLat' => ['numeric', 'nullable', 'min:-90', 'max:90'],
            'whitelistKeys' => ['string', 'nullable'],
            'blacklistKeys' => ['string', 'nullable'],
            'pluginRatingId' => ['integer', 'nullable'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
