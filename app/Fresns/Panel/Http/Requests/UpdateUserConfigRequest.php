<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateUserConfigRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'default_role' => 'int',
            //'default_avatar' => 'int|required',
            //'anonymous_avatar' => 'int|required',
            //'deactivate_avatar' => 'int|required',
            'username_min' => 'int',
            'username_max' => 'int',
            'username_edit' => 'int',
            'nickname_min' => 'int',
            'nickname_max' => 'int',
            'nickname_edit' => 'int',
        ];
    }

    public function attributes(): array
    {
        return [
        ];
    }
}
