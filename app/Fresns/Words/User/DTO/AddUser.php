<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class AddUser extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'aid' => ['required', 'string'],
            'nickname' => ['required', 'string'],
            'username' => 'string',
            'password' => 'string',
            'avatarUrl' => 'sring',
            'gender' => 'integer',
            'birthday' => 'string',
            'timezone' => 'string',
            'language' => 'string',
        ];
    }
}
