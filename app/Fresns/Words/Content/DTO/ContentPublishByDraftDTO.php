<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content\DTO;

use Fresns\DTO\DTO;

class ContentPublishByDraftDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['integer', 'required', 'in:1,2'],
            'logId' => ['integer', 'required'],
        ];
    }
}
