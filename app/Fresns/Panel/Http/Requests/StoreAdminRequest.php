<?php

namespace App\Fresns\Panel\Http\Requests;

class StoreAdminRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'accountName' => 'required|string',
        ];
    }
}
