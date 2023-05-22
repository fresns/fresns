<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Feature\DTO;

use Fresns\DTO\DTO;

class RemoveSubscribeItemDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['integer', 'required', 'in:1,2,3,4'],
            'fskey' => ['string', 'required'],
            'cmdWord' => ['string', 'required'],
            'subject' => ['nullable', 'required_if:type,1,4'],
        ];
    }
}
