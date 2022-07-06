<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class CheckHeaderDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'platformId' => ['integer', 'required', 'between:1,13'],
            'version' => ['string', 'required'],
            'appId' => ['string', 'required'],
            'timestamp' => ['integer', 'required', 'digits_between:10,13'],
            'sign' => ['string', 'required'],
            'langTag' => ['string', 'nullable'],
            'timezone' => ['string', 'nullable'],
            'aid' => ['string', 'nullable'],
            'uid' => ['integer', 'nullable'],
            'token' => ['string', 'nullable', 'required_with:aid'],
            'deviceInfo' => ['array', 'required'],
        ];
    }
}
