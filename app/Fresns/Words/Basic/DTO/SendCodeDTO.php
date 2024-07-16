<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

use Fresns\DTO\DTO;

class SendCodeDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['integer', 'required', 'in:1,2'],
            'templateId' => ['integer', 'required', 'in:1,2,3,4,5,6,7,8'],
            'langTag' => ['string', 'nullable'],
            'account' => ['required'], // email or integer
            'countryCallingCode' => ['integer', 'nullable', 'required_if:type,2'],
        ];
    }
}
