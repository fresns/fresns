<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class CreateAccountTokenDTO extends DTO
{
    public function rules(): array
    {
        return [
            'platformId' => ['integer', 'required', 'between:1,11'],
            'version' => ['string', 'required'],
            'appId' => ['string', 'required'],
            'aid' => ['string', 'required'],
            'deviceToken' => ['string', 'nullable'],
            'expiredTime' => ['integer', 'nullable'],
        ];
    }
}
