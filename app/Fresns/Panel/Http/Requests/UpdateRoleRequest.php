<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => 'required|int',
            // 'is_display_name' => 'required|int',
            // 'is_display_name' => 'required|int',
            'nickname_color' => 'string',
            'rank_num' => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
