<?php

namespace App\Fresns\Panel\Http\Requests;

class UpdateLanguageMenuRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'rank_num' => 'required|string',
            'old_lang_tag' => 'string',
            'continent_id' => 'int',
            'area_code' => 'string',
            'area_status' => 'required|boolean',
            'length_units' => 'required|string',
            'date_format' => 'required|string',
            'time_format_minute' => 'required|string',
            'time_format_hour' => 'required|string',
            'time_format_day' => 'required|string',
            'time_format_month' => 'required|string',
            'is_enable' => 'required|boolean',
        ];

        if ($this->method() == 'POST') {
            $rules['lang_code'] = 'required|string';
        } else if ($this->method() == 'PUT') {
            $rules['lang_code'] = 'string';
        }

        return $rules;
    }

    public function attributes()
    {
        return [
        ];
    }
}
