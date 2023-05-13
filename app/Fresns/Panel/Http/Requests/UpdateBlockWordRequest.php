<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateBlockWordRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'word' => [
                'required',
                'string',
                Rule::unique('App\Models\BlockWord')->ignore(optional($this->blockWord)->id),
            ],
            'replace_word' => 'string|nullable',
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
