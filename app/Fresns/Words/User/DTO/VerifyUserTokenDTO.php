<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class VerifyUserTokenDTO extends DTO
{
    public function rules(): array
    {
        return [
            'platformId' => ['integer', 'required'],
            'aid' => ['string', 'required'],
            'aidToken' => ['string', 'required'],
            'uid' => ['integer', 'required'],
            'uidToken' => ['string', 'required'],
        ];
    }
}
