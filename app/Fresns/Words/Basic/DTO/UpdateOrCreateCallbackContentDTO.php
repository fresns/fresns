<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

use Fresns\DTO\DTO;

class UpdateOrCreateCallbackContentDTO extends DTO
{
    public function rules(): array
    {
        return [
            'fskey' => ['string', 'required'],
            'ulid' => ['ulid', 'required'],
            'type' => ['integer', 'nullable'],
            'content' => ['array', 'nullable'],
            'retentionDays' => ['integer', 'nullable'],
        ];
    }
}
