<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send\DTO;

use Fresns\DTO\DTO;

/**
 * Class SendSmsDTO.
 */
class SendSmsDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'countryCode' => ['required', 'integer'],
            'phoneNumber' => ['required', 'integer'],
            'signName' => 'string',
            'templateCode' => ['required', 'string'],
            'templateParam' => ['string'],
        ];
    }
}
