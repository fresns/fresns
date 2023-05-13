<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class NearbyDTO extends DTO
{
    public function rules(): array
    {
        return [
            'mapId' => ['integer', 'required', 'between:1,11'],
            'mapLng' => ['numeric', 'required', 'min:-180', 'max:180'],
            'mapLat' => ['numeric', 'required', 'min:-90', 'max:90'],
            'unit' => ['string', 'nullable', 'in:km,mi'],
            'length' => ['integer', 'nullable'],
            'contentType' => ['string', 'nullable'],
            'whitelistKeys' => ['string', 'nullable'],
            'blacklistKeys' => ['string', 'nullable'],
            'pluginRatingId' => ['integer', 'nullable'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
