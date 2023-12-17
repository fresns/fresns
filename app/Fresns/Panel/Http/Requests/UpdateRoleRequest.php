<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nickname_color' => 'string|nullable',
            'sort_order' => 'int|required',
        ];
    }

    public function attributes(): array
    {
        return [
        ];
    }
}
