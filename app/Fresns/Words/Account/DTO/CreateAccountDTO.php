<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class CreateAccountDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['integer', 'required', 'in:1,2,3'],
            'account' => ['string', 'nullable', 'required_if:type,1,2'],
            'countryCode' => ['integer', 'nullable', 'required_if:type,2'],
            'connectInfo' => ['array', 'nullable', 'required_if:type,3'],
            'connectEmail' => ['string', 'nullable'],
            'connectPhone' => ['integer', 'nullable'],
            'connectCountryCode' => ['integer', 'nullable'],
            'password' => ['string', 'nullable'],
            'createUser' => ['boolean', 'nullable'],
            'userInfo' => ['array', 'nullable'],
        ];
    }
}