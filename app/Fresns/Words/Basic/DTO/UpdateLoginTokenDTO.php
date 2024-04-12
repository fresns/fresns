<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

use Fresns\DTO\DTO;

class UpdateLoginTokenDTO extends DTO
{
    public function rules(): array
    {
        return [
            'loginToken' => ['string', 'required'],
            'uid' => ['integer', 'required'],
            'pin' => ['string', 'nullable'],
        ];
    }
}
