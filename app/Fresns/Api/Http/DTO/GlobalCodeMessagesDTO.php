<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class GlobalCodeMessagesDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'unikey' => ['string', 'nullable'],
            'isAll' => ['boolean', 'nullable', 'required_without:codes'],
            'codes' => ['string', 'nullable', 'required_without:isAll'],
        ];
    }
}
