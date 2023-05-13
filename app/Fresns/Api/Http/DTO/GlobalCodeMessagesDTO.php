<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class GlobalCodeMessagesDTO extends DTO
{
    public function rules(): array
    {
        return [
            'fskey' => ['string', 'nullable'],
            'isAll' => ['boolean', 'nullable', 'required_without:codes'],
            'codes' => ['string', 'nullable', 'required_without:isAll'],
        ];
    }
}
