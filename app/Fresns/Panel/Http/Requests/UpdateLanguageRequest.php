<?php

namespace App\Fresns\Panel\Http\Requests;

class UpdateLanguageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'languages' => 'required|array',
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
