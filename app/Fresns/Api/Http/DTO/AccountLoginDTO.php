<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class AccountLoginDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:email,phone'],
            'account' => ['string', 'required'],
            'countryCode' => ['integer', 'nullable', 'required_if:type,phone'],
            'password' => ['string', 'nullable', 'required_without:verifyCode'],
            'verifyCode' => ['string', 'nullable', 'required_without:password'],
            'deviceToken' => ['string', 'nullable'],
        ];
    }
}
