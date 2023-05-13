<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class VerifyAccountTokenDTO extends DTO
{
    public function rules(): array
    {
        return [
            'platformId' => ['integer', 'required', 'between:1,13'],
            'aid' => ['string', 'required'],
            'aidToken' => ['string', 'required'],
        ];
    }
}
