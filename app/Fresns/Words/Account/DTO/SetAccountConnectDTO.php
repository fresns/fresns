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
            'fskey' => ['string', 'required', 'exists:App\Models\App,fskey'],
            'aid' => ['string', 'required', 'exists:App\Models\Account,aid'],
            'connectPlatformId' => ['integer', 'required'],
            'connectAccountId' => ['string', 'required'],
            'connectToken' => ['string', 'nullable'],
            'connectRefreshToken' => ['string', 'nullable'],
            'refreshTokenExpiredDatetime' => ['string', 'nullable', 'date_format:"Y-m-d H:i:s"'],
            'connectUsername' => ['string', 'nullable'],
            'connectNickname' => ['string', 'nullable'],
            'connectAvatar' => ['string', 'nullable'],
            'moreInfo' => ['json', 'nullable'],
            'connectEmail' => ['email', 'nullable'],
            'connectPurePhone' => ['integer', 'nullable'],
            'connectCountryCallingCode' => ['integer', 'nullable', 'required_with:connectPurePhone'],
        ];
    }
}
