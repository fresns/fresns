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
            'platform_id' => 'int|required',
            'name' => 'string|required',
            'type' => 'int|required',
            'app_fskey' => 'exists:App\Models\App,fskey',
            'is_read_only' => 'boolean|required',
            'is_enabled' => 'boolean|required',
        ];
    }

    public function attributes(): array
    {
        return [
            'platform_id' => __('FsLang::panel.table_platform'),
            'name' => __('FsLang::panel.table_name'),
            'type' => __('FsLang::panel.table_type'),
            'app_fskey' => __('FsLang::panel.table_plugin'),
            'is_read_only' => __('FsLang::panel.key_table_read_only'),
            'is_enabled' => __('FsLang::panel.table_status'),
        ];
    }
}
