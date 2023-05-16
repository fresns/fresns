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
            'platformId' => ['integer', 'nullable', 'between:1,13', 'required_with:aidToken'],
            'version' => ['string', 'nullable', 'required_with:aidToken'],
            'appId' => ['string', 'nullable', 'required_with:aidToken'],
            'username' => ['string', 'nullable', 'alpha_dash', 'unique:App\Models\User,username'],
            'nickname' => ['string', 'nullable'],
            'password' => ['string', 'nullable'],
            'avatarFid' => ['string', 'nullable'],
            'avatarUrl' => ['string', 'nullable'],
            'bannerFid' => ['string', 'nullable'],
            'bannerUrl' => ['string', 'nullable'],
            'gender' => ['numeric', 'nullable', 'in:1,2,3'],
            'birthday' => ['string', 'nullable', 'date_format:"Y-m-d H:i:s"'],
            'timezone' => ['string', 'nullable'],
            'language' => ['string', 'nullable'],
        ];
    }
}
