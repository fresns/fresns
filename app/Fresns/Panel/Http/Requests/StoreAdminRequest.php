<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Requests;

class StoreAdminRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'accountName' => 'required|string',
        ];
    }
}
