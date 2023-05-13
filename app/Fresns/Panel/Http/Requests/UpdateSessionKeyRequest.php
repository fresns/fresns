<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateSessionKeyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'platform_id' => 'required|int',
            'name' => 'required|string',
            'type' => 'required|int',
            'is_enabled' => 'required|boolean',
            'plugin_fskey' => 'exists:App\Models\Plugin,fskey',
        ];
    }

    public function attributes(): array
    {
        return [
            'platform_id' => __('FsLang::panel.table_platform'),
            'name' => __('FsLang::panel.table_name'),
            'type' => __('FsLang::panel.table_type'),
            'is_enabled' => __('FsLang::panel.table_status'),
            'plugin_fskey' => __('FsLang::panel.table_plugin'),
        ];
    }
}
