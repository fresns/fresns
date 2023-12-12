<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateGeneralRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'site_url' => 'url|nullable',
            'site_name' => 'array|nullable',
            'site_desc' => 'array|nullable',
            'site_intro' => 'array|nullable',
            'site_copyright_name' => 'string|nullable',
            'site_copyright_years' => 'string|nullable',
            'site_email' => 'email|nullable',
            'site_mode' => 'string|nullable',
            'site_private_status' => 'string|nullable',
            'site_private_service' => 'string|nullable',
            'site_private_end_after' => 'int|nullable',
            'site_private_whitelist_roles' => 'array|nullable',
        ];
    }

    public function attributes(): array
    {
        return [
        ];
    }
}
