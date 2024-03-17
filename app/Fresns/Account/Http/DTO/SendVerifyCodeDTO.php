<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Account\Http\DTO;

use Fresns\DTO\DTO;

class SendVerifyCodeDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:email,sms'],
            'templateId' => ['integer', 'required', 'between:1,8'],
            'account' => ['nullable'],
            'countryCode' => ['integer', 'nullable'],
        ];
    }
}
