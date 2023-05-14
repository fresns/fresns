<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class SetAccountConnectDTO extends DTO
{
    public function rules(): array
    {
        return [
            'fskey' => ['string', 'required', 'exists:App\Models\Plugin,fskey'],
            'aid' => ['string', 'required', 'exists:App\Models\Account,aid'],
            'connectId' => ['integer', 'required'],
            'connectToken' => ['string', 'required'],
            'connectRefreshToken' => ['string', 'nullable'],
            'refreshTokenExpiredDatetime' => ['string', 'nullable', 'date_format:"Y-m-d H:i:s"'],
            'connectUsername' => ['string', 'nullable'],
            'connectNickname' => ['string', 'required'],
            'connectAvatar' => ['string', 'nullable'],
            'moreJson' => ['json', 'nullable'],
            'connectEmail' => ['email', 'nullable'],
            'connectPhone' => ['integer', 'nullable'],
            'connectCountryCode' => ['integer', 'nullable', 'required_with:connectPhone'],
        ];
    }
}
