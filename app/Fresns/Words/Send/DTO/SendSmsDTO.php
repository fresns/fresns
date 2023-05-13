<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send\DTO;

use Fresns\DTO\DTO;

class SendSmsDTO extends DTO
{
    public function rules(): array
    {
        return [
            'countryCode' => ['integer', 'required'],
            'phoneNumber' => ['integer', 'required'],
            'signName' => ['string', 'nullable'],
            'templateCode' => ['string', 'required'],
            'templateParam' => ['array', 'nullable'],
        ];
    }
}
