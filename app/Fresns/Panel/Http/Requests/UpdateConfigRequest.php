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
            'domain' => 'url',
            'path' => 'string',
        ];
    }

    public function attributes()
    {
        return [
            'domain' => __('FsLang::panel.backendDomain'),
            'path' => __('FsLang::panel.safePath'),
        ];
    }
}
