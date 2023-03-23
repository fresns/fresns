<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateStickerRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rule = [
            'rating' => 'string|required',
            'is_enable' => 'boolean|required',
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

    public function attributes()
    {
        return [
        ];
    }
}
