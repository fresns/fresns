<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class VerifyAccountDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['integer', 'required', 'in:1,2,3,4,5'],
            'account' => ['nullable', 'required_if:type,1,2,3,4'], // aid, email or integer
            'countryCode' => ['integer', 'nullable', 'required_if:type,4'],
            'password' => ['string', 'nullable', 'required_without_all:verifyCode,connectAccountId'],
            'verifyCode' => ['string', 'nullable', 'required_without_all:password,connectAccountId'],
            'connectPlatformId' => ['integer', 'nullable', 'required_without_all:verifyCode,password', 'required_if:type,5'],
            'connectAccountId' => ['string', 'nullable', 'required_without_all:verifyCode,password', 'required_if:type,5'],
        ];
    }
}
