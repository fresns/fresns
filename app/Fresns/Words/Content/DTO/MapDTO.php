<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content\DTO;

use Fresns\DTO\DTO;

class MapDTO extends DTO
{
    public function rules(): array
    {
        return [
            'mapId' => ['integer', 'required', 'between:1,10'],
            'latitude' => ['numeric', 'required', 'min:-90', 'max:90'],
            'longitude' => ['numeric', 'required', 'min:-180', 'max:180'],
            'scale' => ['string', 'nullable'],
            'continent' => ['string', 'nullable'],
            'continentCode' => ['string', 'nullable'],
            'country' => ['string', 'nullable'],
            'countryCode' => ['string', 'nullable'],
            'region' => ['string', 'nullable'],
            'regionCode' => ['string', 'nullable'],
            'city' => ['string', 'nullable'],
            'cityCode' => ['string', 'nullable'],
            'district' => ['string', 'nullable'],
            'address' => ['string', 'nullable'],
            'zip' => ['string', 'nullable'],
            'poi' => ['string', 'required'],
            'poiId' => ['string', 'nullable'],
        ];
    }
}
