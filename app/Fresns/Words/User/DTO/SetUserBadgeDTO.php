<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class SetUserBadgeDTO extends DTO
{
    public function rules(): array
    {
        return [
            'uid' => ['integer', 'required', 'exists:App\Models\User,uid'],
            'fskey' => ['string', 'required', 'exists:App\Models\Plugin,fskey'],
            'type' => ['integer', 'required', 'in:1,2,3'],
            'badgeNumber' => ['integer', 'nullable', 'required_if:type,2'],
            'badgeText' => ['string', 'nullable', 'required_if:type,3', 'size:8'],
        ];
    }
}
