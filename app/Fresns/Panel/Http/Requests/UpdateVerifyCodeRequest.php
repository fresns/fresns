<?php

namespace App\Fresns\Panel\Http\Requests;

class UpdateVerifyCodeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email_templates' => 'array',
            'sms_templates' => 'array',
        ];
    }

    public function attributes()
    {
        return [
        ];
    }
}
