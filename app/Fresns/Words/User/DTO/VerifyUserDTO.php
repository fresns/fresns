<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class VerifyUserDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'aid' => ['string', 'required'],
            'uid' => ['integer', 'required'],
            'password' => ['string', 'nullable'],
        ];
    }
}
