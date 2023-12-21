<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content\DTO;

use Fresns\DTO\DTO;

class SetPostAffiliateUserDTO extends DTO
{
    public function rules(): array
    {
        return [
            'pid' => ['string', 'required'],
            'type' => ['string', 'required', 'in:add,remove'],
            'uid' => ['integer', 'required'],
            'fskey' => ['string', 'required', 'exists:App\Models\App,fskey'],
            'moreJson' => ['json', 'nullable'],
        ];
    }
}
