<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basis\DTO;

use Fresns\DTO\DTO;

/**
 * Class CheckCodeDTO.
 */
class CheckCodeDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'integer'],
            'account' => ['required', 'string'],
            'countryCode' => 'integer',
            'verifyCode' => ['required', 'string'],
        ];
    }
}
