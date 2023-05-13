<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class GlobalBlockWordsDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['string', 'nullable', 'in:content,user,conversation'],
            'pageSize' => ['integer', 'nullable', 'between:1,100'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
