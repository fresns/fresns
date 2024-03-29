<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class SetUserGroupExpiryDatetimeDTO extends DTO
{
    public function rules(): array
    {
        return [
            'uid' => ['integer', 'required', 'exists:App\Models\User,uid'],
            'gid' => ['integer', 'required', 'exists:App\Models\Group,gid'],
            'datetime' => ['date', 'nullable', 'required_without:clearDatetime'],
            'clearDatetime' => ['boolean', 'nullable', 'required_without:datetime'],
        ];
    }
}
