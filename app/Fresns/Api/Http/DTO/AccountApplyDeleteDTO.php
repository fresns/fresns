<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class AccountApplyDeleteDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'password' => ['string', 'nullable', 'required_without:verifyCode'],
            'verifyCode' => ['string', 'nullable', 'required_without:password'],
            'codeType' => ['string', 'required', 'in:email,sms'],
        ];
    }
}
