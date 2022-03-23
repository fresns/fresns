<?php

namespace App\Fresns\Panel\Http\Requests;

class UpdateLanguageRankRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'rank_num' => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
