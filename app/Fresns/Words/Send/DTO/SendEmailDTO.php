<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send\DTO;

use Fresns\DTO\DTO;

/**
 * Class SendEmailDTO.
 */
class SendEmailDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'email' => ['email', 'required'],
            'title' => ['string', 'required'],
            'content' => ['string', 'required'],
        ];
    }
}
