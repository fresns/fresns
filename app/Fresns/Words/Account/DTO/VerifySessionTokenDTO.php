<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

/**
 * Class VerifySessionTokenDTO.
 */
class VerifySessionTokenDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', 'integer'],
            'aid' => ['required', 'string'],
            'uid' => ['nullable', 'integer'],
            'token' => ['required', 'string'],
        ];
    }
}
