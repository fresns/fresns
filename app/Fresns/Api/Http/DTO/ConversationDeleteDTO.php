<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class ConversationDeleteDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:conversation,messages'],
            'cmids' => ['string', 'nullable', 'required_if:type,messages'],
        ];
    }
}
