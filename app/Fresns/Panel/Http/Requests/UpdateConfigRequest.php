<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateConfigRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'panel_path' => 'string',
        ];
    }

    public function attributes()
    {
        return [
            'panel_path' => __('FsLang::panel.setting_panel_path'),
        ];
    }
}
