<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdatePolicyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'send_email_service' => 'string|nullable',
            'send_sms_service' => 'string|nullable',
            'send_sms_default_code' => 'string|nullable',
            'send_sms_supported_codes' => 'string|nullable',
            'send_ios_service' => 'string|nullable',
            'send_android_service' => 'string|nullable',
            'send_wechat_service' => 'string|nullable',

        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
