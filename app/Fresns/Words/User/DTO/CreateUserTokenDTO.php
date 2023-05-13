<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class CreateUserTokenDTO extends DTO
{
    public function rules(): array
    {
        return [
            'platformId' => ['integer', 'required', 'between:1,13'],
            'version' => ['string', 'required'],
            'appId' => ['string', 'required'],
            'aid' => ['string', 'required'],
            'aidToken' => ['string', 'required'],
            'uid' => ['integer', 'required'],
            'expiredTime' => ['integer', 'nullable'],
        ];
    }
}
