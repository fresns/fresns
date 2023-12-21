<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

use Fresns\DTO\DTO;

class VerifySignDTO extends DTO
{
    public function rules(): array
    {
        return [
            'appId' => ['string', 'required'],
            'platformId' => ['integer', 'required', 'between:1,11'],
            'version' => ['string', 'required'],
            'aid' => ['string', 'nullable'],
            'aidToken' => ['string', 'nullable', 'required_with:aid'],
            'uid' => ['integer', 'nullable'],
            'uidToken' => ['string', 'nullable', 'required_with:uid'],
            'signature' => ['string', 'required'],
            'timestamp' => ['integer', 'required', 'digits_between:10,13'],
            'verifyType' => ['integer', 'nullable'],
            'verifyFskey' => ['string', 'nullable', 'exists:App\Models\App,fskey'],
        ];
    }
}
