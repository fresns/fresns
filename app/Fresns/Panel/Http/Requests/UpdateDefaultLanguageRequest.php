<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateDefaultLanguageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'default_language' => 'string|required',
        ];
    }

    public function attributes(): array
    {
        return [
            'default_language' => __('FsLang::panel.default_language'),
        ];
    }
}
