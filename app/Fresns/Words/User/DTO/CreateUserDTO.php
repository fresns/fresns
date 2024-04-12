<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class CreateUserDTO extends DTO
{
    public function rules(): array
    {
        return [
            'aid' => ['string', 'required'],
            'aidToken' => ['string', 'nullable'],
            'appId' => ['string', 'nullable', 'required_with:aidToken'],
            'platformId' => ['integer', 'nullable', 'required_with:aidToken'],
            'version' => ['string', 'nullable', 'required_with:aidToken'],
            'username' => ['string', 'nullable', 'alpha_dash'],
            'nickname' => ['string', 'nullable'],
            'pin' => ['string', 'nullable'],
            'avatarFid' => ['string', 'nullable'],
            'avatarUrl' => ['string', 'nullable'],
            'bannerFid' => ['string', 'nullable'],
            'bannerUrl' => ['string', 'nullable'],
            'gender' => ['numeric', 'nullable', 'in:1,2,3,4'],
            'genderPronoun' => ['numeric', 'nullable', 'in:1,2,3'],
            'genderCustom' => ['string', 'nullable'],
        ];
    }
}
