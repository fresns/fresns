<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class AccountEditDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'codeType' => ['string', 'nullable', 'in:email,sms'],
            'verifyCode' => ['string', 'nullable'],
            'newVerifyCode' => ['string', 'nullable', 'required_with:editEmail', 'required_with:editPhone'],
            'editEmail' => ['email', 'nullable'],
            'editPhone' => ['integer', 'nullable'],
            'editCountryCode' => ['integer', 'nullable', 'required_with:editPhone'],
            'password' => ['string', 'nullable'],
            'editPassword' => ['string', 'nullable'],
            'editPasswordConfirm' => ['string', 'nullable', 'required_with:editPassword'],
            'walletPassword' => ['string', 'nullable'],
            'editWalletPassword' => ['string', 'nullable'],
            'editWalletPasswordConfirm' => ['string', 'nullable', 'required_with:editWalletPassword'],
            'editLastLoginTime' => ['boolean', 'nullable'],
        ];
    }
}
