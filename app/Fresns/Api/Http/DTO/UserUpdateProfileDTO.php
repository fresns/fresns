<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class UserUpdateProfileDTO extends DTO
{
    public function rules(): array
    {
        return [
            'username' => ['string', 'nullable', 'alpha_dash'],
            'nickname' => ['string', 'nullable'],
            'avatarFid' => ['string', 'nullable'],
            'avatarUrl' => ['url', 'nullable'],
            'bannerFid' => ['string', 'nullable'],
            'bannerUrl' => ['url', 'nullable'],
            'gender' => ['integer', 'nullable', 'in:1,2,3'],
            'genderCustom' => ['string', 'nullable', 'max:32'],
            'genderPronoun' => ['integer', 'nullable', 'in:1,2,3'],
            'birthdayDisplayType' => ['integer', 'nullable', 'in:1,2,3,4'],
            'bio' => ['string', 'nullable'],
            'location' => ['string', 'nullable'],
            'moreInfo' => ['array', 'nullable'],
            'archives' => ['array', 'nullable'],
            'deviceToken' => ['string', 'nullable'],
        ];
    }
}
