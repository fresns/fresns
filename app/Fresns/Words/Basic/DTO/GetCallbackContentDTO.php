<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

use Fresns\DTO\DTO;

class GetCallbackContentDTO extends DTO
{
    public function rules(): array
    {
        return [
            'fskey' => ['string', 'required'],
            'callbackKey' => ['string', 'max:64', 'required'],
            'timeout' => ['integer', 'nullable'],
            'markAsUsed' => ['boolean', 'nullable'],
        ];
    }
}
