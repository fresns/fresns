<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class AddUserDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'aid' => ['string', 'required', 'exists:App\Models\Account,aid'],
            'nickname' => ['string', 'required'],
            'username' => ['string', 'nullable', 'alpha_dash', 'unique:App\Models\User,username'],
            'password' => ['string', 'nullable'],
            'avatarFid' => ['string', 'nullable'],
            'avatarUrl' => ['string', 'nullable'],
            'gender' => ['numeric', 'nullable', 'in:0,1,2'],
            'birthday' => ['string', 'nullable', 'date_format:"Y-m-d H:i:s"'],
            'timezone' => ['string', 'nullable'],
            'language' => ['string', 'nullable'],
        ];
    }
}
