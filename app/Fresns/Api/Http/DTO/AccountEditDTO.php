<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class AccountEditDTO extends DTO
{
    public function rules(): array
    {
        return [
            'codeType' => ['string', 'nullable', 'in:email,sms', 'required_with:verifyCode'],
            'verifyCode' => ['string', 'nullable'],
            'newEmail' => ['email', 'nullable'],
            'newPhone' => ['integer', 'nullable'],
            'newCountryCode' => ['integer', 'nullable', 'required_with:newPhone'],
            'newVerifyCode' => ['string', 'nullable', 'required_with:newEmail', 'required_with:newPhone'],
            'currentPassword' => ['string', 'nullable'],
            'newPassword' => ['string', 'nullable'],
            'currentWalletPassword' => ['string', 'nullable'],
            'newWalletPassword' => ['string', 'nullable'],
            'updateLastLoginTime' => ['boolean', 'nullable'],
            'disconnectPlatformId' => ['integer', 'nullable'],
            'deviceToken' => ['string', 'nullable'],
        ];
    }
}
