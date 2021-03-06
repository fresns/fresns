<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateSessionKeyRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'platform_id' => 'required|int',
            'name' => 'required|string',
            'type' => 'required|int',
            'is_enable' => 'required|boolean',
            'plugin_unikey' => 'exists:App\Models\Plugin,unikey',
        ];
    }

    public function attributes()
    {
        return [
            'platform_id' => __('FsLang::panel.table_platform'),
            'name' => __('FsLang::panel.table_name'),
            'type' => __('FsLang::panel.table_type'),
            'is_enable' => __('FsLang::panel.table_status'),
            'plugin_unikey' => __('FsLang::panel.table_plugin'),
        ];
    }
}
