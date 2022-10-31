<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class NearbyDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'mapId' => ['integer', 'required', 'in:1,2,3,4,5,6,7,8,9,10'],
            'mapLng' => ['numeric', 'required'],
            'mapLat' => ['numeric', 'required'],
            'unit' => ['string', 'nullable', 'in:km,mi'],
            'length' => ['integer', 'nullable'],
            'contentType' => ['string', 'nullable'],
            'pluginRatingId' => ['integer', 'nullable'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
