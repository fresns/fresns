<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class UpdateLanguageMenuRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'order' => 'int|required',
            'length_unit' => 'string|required',
            'date_format' => 'string|required',
            'time_format_minute' => 'string|required',
            'time_format_hour' => 'string|required',
            'time_format_day' => 'string|required',
            'time_format_month' => 'string|required',
            'time_format_year' => 'string|required',
            'is_enabled' => 'boolean|required',
        ];

        if ($this->method() == 'POST') {
            $rules['lang_code'] = 'required|string';
        } elseif ($this->method() == 'PUT') {
            $rules['lang_code'] = 'string';
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
        ];
    }
}
