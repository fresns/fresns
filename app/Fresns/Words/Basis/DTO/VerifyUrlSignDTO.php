<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basis\DTO;

use Fresns\DTO\DTO;

/**
 * Class VerifyUrlSignDTO.
 */
class VerifyUrlSignDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'urlSign' => ['string', 'required'],
        ];
    }
}
