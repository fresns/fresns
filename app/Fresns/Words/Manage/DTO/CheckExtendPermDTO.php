<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Manage\DTO;

use Fresns\DTO\DTO;

class CheckExtendPermDTO extends DTO
{
    public function rules(): array
    {
        return [
            'fskey' => ['string', 'required', 'exists:App\Models\Plugin,fskey'],
            'type' => ['integer', 'required', 'between:1,9'],
            'uid' => ['integer', 'nullable'],
            'gid' => ['string', 'nullable'],
        ];
    }
}
