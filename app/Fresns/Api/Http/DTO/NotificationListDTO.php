<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class NotificationListDTO extends DTO
{
    public function rules(): array
    {
        return [
            'types' => ['string', 'nullable'],
            'status' => ['boolean', 'nullable'],
            'userWhitelistKeys' => ['string', 'nullable'],
            'userBlacklistKeys' => ['string', 'nullable'],
            'whitelistKeys' => ['string', 'nullable'],
            'blacklistKeys' => ['string', 'nullable'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
