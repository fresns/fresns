<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class VerifyAccount extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['integer', 'in:1,2'],
            'account' => 'string',
            'countryCode' => ['required_if:type,2'],
            'password' => 'string',
            'verifyCode' => 'string',
        ];
    }
}
