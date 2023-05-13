<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send\DTO;

use Fresns\DTO\DTO;

class SendEmailDTO extends DTO
{
    public function rules(): array
    {
        return [
            'email' => ['email', 'required'],
            'title' => ['string', 'required'],
            'content' => ['string', 'required'],
        ];
    }
}
