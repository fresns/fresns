<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

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
            'platformId' => ['integer', 'required', 'between:1,13'],
            'version' => ['string', 'required'],
            'appId' => ['string', 'required'],
            'timestamp' => ['integer', 'required', 'digits_between:10,13'],
            'sign' => ['string', 'required'],
            'aid' => ['string', 'nullable'],
            'aidToken' => ['string', 'nullable', 'required_with:aid'],
            'uid' => ['integer', 'nullable'],
            'uidToken' => ['string', 'nullable', 'required_with:uid'],
        ];
    }
}
