<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class VerifyAccountDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:1,2'],
            'account' => ['required', 'string'],
            'countryCode' => ['nullable', 'required_if:type,2', 'integer'],
            'password' => ['required_without:verifyCode', 'string'],
            'verifyCode' => ['required_without:password', 'string'],
        ];
    }
}
