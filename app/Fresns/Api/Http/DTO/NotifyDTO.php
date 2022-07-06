<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class NotifyDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:all,choose'],
            'notifyType' => ['integer', 'nullable', 'in:2,3,4,5,6,7', 'required_without:notifyIds', 'required_if:type,all'],
            'notifyIds' => ['string', 'nullable', 'required_without:notifyType', 'required_if:type,choose'],
        ];
    }
}
