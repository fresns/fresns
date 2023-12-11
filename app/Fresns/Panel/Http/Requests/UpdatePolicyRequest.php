<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdatePolicyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'send_email_service' => 'string|nullable',
            'send_sms_service' => 'string|nullable',
            'send_sms_default_code' => 'string|nullable',
            'send_sms_supported_codes' => 'string|nullable',
            'ios_notifications_service' => 'string|nullable',
            'android_notifications_service' => 'string|nullable',
            'desktop_notifications_service' => 'string|nullable',
            'wechat_notifications_service' => 'string|nullable',
        ];
    }

    public function attributes(): array
    {
        return [
        ];
    }
}
