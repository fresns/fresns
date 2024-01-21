<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'account_center_service' => 'string|nullable',
            'account_center_captcha' => 'string|nullable',
            'account_center_captcha_configs' => 'array|nullable',
            'account_register_status' => 'string|nullable',
            'account_register_service' => 'string|nullable',
            'account_email_register' => 'string|nullable',
            'account_phone_register' => 'string|nullable',
            'account_login_service' => 'string|nullable',
            'account_email_login' => 'string|nullable',
            'account_phone_login' => 'string|nullable',
            'account_login_with_code' => 'string|nullable',
            'account_login_or_register' => 'string|nullable',
            'password_length' => 'int|nullable',
            'password_strength' => 'array|nullable',
            'account_connect_services' => 'array|nullable',
            'account_kyc_service' => 'string|nullable',
        ];
    }

    public function attributes(): array
    {
        return [
        ];
    }
}
