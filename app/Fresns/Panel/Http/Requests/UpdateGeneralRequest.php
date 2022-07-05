<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateGeneralRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'site_url' => 'url|nullable',
            'site_name' => 'array|nullable',
            'site_desc' => 'array|nullable',
            'site_copyright' => 'string|nullable',
            'site_copyright_years' => 'string|nullable',
            'default_timezone' => 'string|nullable',
            'site_mode' => 'string|nullable',
            'site_public_status' => 'string|nullable',
            'site_public_service' => 'string|nullable',
            'site_register_email' => 'string|nullable',
            'site_register_phone' => 'string|nullable',
            'site_private_status' => 'string|nullable',
            'site_private_service' => 'string|nullable',
            'site_private_end_after' => 'string|nullable',
            'site_email' => 'email|nullable',
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
