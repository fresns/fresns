<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class SetUserExtcreditsDTO extends DTO
{
    public function rules(): array
    {
        return [
            'uid' => ['integer', 'required', 'exists:App\Models\User,uid'],
            'extcreditsId' => ['integer', 'required', 'in:1,2,3,4,5'],
            'operation' => ['string', 'required', 'in:increment,decrement'],
            'fskey' => ['string', 'required', 'exists:App\Models\Plugin,fskey'],
            'amount' => ['integer', 'nullable'],
            'remark' => ['string', 'nullable'],
        ];
    }
}
