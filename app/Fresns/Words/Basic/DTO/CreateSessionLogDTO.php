<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

use Fresns\DTO\DTO;

class CreateSessionLogDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['integer', 'required'],
            'platformId' => ['integer', 'required'],
            'version' => ['string', 'required'],
            'appId' => ['string', 'nullable'],
            'langTag' => ['string', 'nullable'],
            'fskey' => ['string', 'nullable'],
            'aid' => ['string', 'nullable'],
            'uid' => ['integer', 'nullable'],
            'actionName' => ['string', 'required'],
            'actionDesc' => ['string', 'nullable'],
            'actionState' => ['integer', 'required', 'in:1,2,3'],
            'actionId' => ['integer', 'nullable'],
            'deviceInfo' => ['array', 'nullable'],
            'deviceToken' => ['string', 'nullable'],
            'loginToken' => ['string', 'nullable'],
            'moreInfo' => ['array', 'nullable'],
        ];
    }
}
