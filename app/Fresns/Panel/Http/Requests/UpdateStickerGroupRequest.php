<?php

namespace App\Fresns\Panel\Http\Requests;

use Illuminate\Validation\Rule;

class UpdateStickerGroupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rule = [
            'rank_num' => 'string|required',
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
