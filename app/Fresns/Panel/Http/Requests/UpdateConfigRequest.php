<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateConfigRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'panel_path' => 'string',
        ];
    }

    public function attributes(): array
    {
        return [
            'panel_path' => __('FsLang::panel.setting_panel_path'),
        ];
    }
}
