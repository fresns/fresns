<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateUserConfigRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'connects' => 'array',
            'connect_plugins' => 'array',
            'account_real_name_service' => 'nullable|string',
            'multi_user_status' => 'string',
            'multi_user_service' => 'nullable|string',
            'multi_user_roles' => 'array',
            'default_role' => 'int',
            //'default_avatar' => 'required|int',
            //'anonymous_avatar' => 'required|int',
            //'deactivate_avatar' => 'required|int',
            'password_length' => 'int',
            'password_strength' => 'array',
            'username_min' => 'int',
            'username_max' => 'int',
            'username_edit' => 'int',
            'nickname_min' => 'int',
            'nickname_max' => 'int',
            'nickname_edit' => 'int',
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
