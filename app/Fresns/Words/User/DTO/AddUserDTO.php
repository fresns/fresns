<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class AddUserDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'aid' => ['required', 'string'],
            'nickname' => ['required', 'string'],
            'username' => ['nullable', 'alpha_dash'],
            'password' => ['nullable', 'string'],
            'avatarFid' => ['nullable', 'string'],
            'avatarUrl' => ['nullable', 'string'],
            'gender' => ['nullable', 'in:0,1,2'],
            'birthday' => ['nullable', 'date_format:"Y-m-d H:i:s"'],
            'timezone' => ['nullable', 'string'],
            'language' => ['nullable', 'string'],
        ];
    }
}
