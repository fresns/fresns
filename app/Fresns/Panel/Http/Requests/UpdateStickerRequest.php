<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateStickerRequest extends FormRequest
{
    public function rules(): array
    {
        $rule = [
            'rating' => 'string|required',
            'is_enabled' => 'boolean|required',
        ];
        if ($this->method() == 'POST') {
            $rule['code'] = [
                'required',
                Rule::unique('App\Models\Sticker'),
            ];
        } elseif ($this->method() == 'PUT') {
            $rule['code'] = [
                'required',
                Rule::unique('App\Models\Sticker')->ignore($this->sticker->id),
            ];
        }

        return $rule;
    }

    public function attributes(): array
    {
        return [
        ];
    }
}
