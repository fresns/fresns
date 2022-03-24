<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basis\DTO;

use Fresns\DTO\DTO;

/**
 * Class VerifySignDTO.
 *
 * @property int $platform
 * @property string $version
 */
class VerifySignDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', 'integer'],
            'version' => ['required'],
            'appId' => ['required'],
            'timestamp' => ['required'],
            'sign' => ['required'],
            'aid' => ['string'],
            'uid' => ['integer'],
            'token' => ['string'],
        ];
    }
}
