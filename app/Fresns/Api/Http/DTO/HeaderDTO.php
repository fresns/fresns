<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class HeaderDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'platformId' => ['integer', 'required', 'between:1,13'],
            'version' => ['string', 'required'],
            'appId' => ['string', 'required', 'exists:App\Models\SessionKey,app_id'],
            'timestamp' => ['integer', 'required', 'digits_between:10,13'],
            'sign' => ['string', 'required'],
            'langTag' => ['string', 'nullable'],
            'timezone' => ['string', 'nullable'],
            'aid' => ['string', 'nullable', 'exists:App\Models\Account,aid'],
            'uid' => ['integer', 'nullable', 'exists:App\Models\User,uid'],
            'token' => ['string', 'nullable', 'required_with:aid'],
            'deviceInfo' => ['string', 'required'],
        ];
    }
}
