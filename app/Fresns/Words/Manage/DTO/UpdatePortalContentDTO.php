<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Manage\DTO;

use Fresns\DTO\DTO;

class UpdatePortalContentDTO extends DTO
{
    public function rules(): array
    {
        return [
            'platformId' => ['integer', 'required', 'between:1,11'],
            'langTag' => ['string', 'required'],
            'content' => ['string', 'nullable'],
        ];
    }
}
