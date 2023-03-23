<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class AccountRegisterDTO extends DTO
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
            'verifyCode' => ['string', 'required'],
            'password' => ['string', 'required'],
            'nickname' => ['string', 'required'],
            'deviceToken' => ['string', 'nullable'],
        ];
    }
}
