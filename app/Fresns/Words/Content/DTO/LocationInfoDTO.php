<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content\DTO;

use Fresns\DTO\DTO;

class LocationInfoDTO extends DTO
{
    public function rules(): array
    {
        return [
            'name' => ['string', 'required'],
            'description' => ['string', 'nullable'],
            'placeId' => ['string', 'nullable'],
            'placeType' => ['string', 'nullable'],
            'mapId' => ['integer', 'required', 'between:1,10'],
            'latitude' => ['numeric', 'required', 'min:-90', 'max:90'],
            'longitude' => ['numeric', 'required', 'min:-180', 'max:180'],
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
        ];
    }
}
