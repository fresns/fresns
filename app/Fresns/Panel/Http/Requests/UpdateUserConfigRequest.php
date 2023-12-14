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
            'default_role' => 'int|required',
            'username_min' => 'int|required',
            'username_max' => 'int|required',
            'username_edit' => 'int|required',
            'nickname_min' => 'int|required',
            'nickname_max' => 'int|required',
            'nickname_edit' => 'int|required',
            'bio_length' => 'int|required',
        ];
    }

    public function attributes(): array
    {
        return [
        ];
    }
}
