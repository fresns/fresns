<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content\DTO;

use Fresns\DTO\DTO;

class SetPostAuthDTO extends DTO
{
    public function rules(): array
    {
        return [
            'pid' => ['string', 'required'],
            'type' => ['string', 'required', 'in:add,remove'],
            'uid' => ['integer', 'nullable', 'required_without:rid'],
            'rid' => ['integer', 'nullable', 'required_without:uid', 'exists:App\Models\Role,id'],
        ];
    }
}
