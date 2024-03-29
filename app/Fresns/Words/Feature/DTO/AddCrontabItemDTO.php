<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Feature\DTO;

use Fresns\DTO\DTO;

class AddCrontabItemDTO extends DTO
{
    public function rules(): array
    {
        return [
            'fskey' => ['string', 'required'],
            'cmdWord' => ['string', 'required'],
            'cronTableFormat' => ['string', 'required'],
        ];
    }
}
