<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateBlockWordRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
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
