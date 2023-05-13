<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class WalletCheckPasswordDTO extends DTO
{
    public function rules(): array
    {
        return [
            'aid' => ['string', 'required'],
            'password' => ['string', 'nullable'],
        ];
    }
}
