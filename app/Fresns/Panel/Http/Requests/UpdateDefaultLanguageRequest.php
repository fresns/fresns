<?php

namespace App\Fresns\Panel\Http\Requests;

class UpdateDefaultLanguageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'default_language' => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
            'default_language' => __('FsLang::panel.defaultLanguage'),
        ];
    }
}
