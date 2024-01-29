<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class CommonFileWarningDTO extends DTO
{
    public function rules(): array
    {
        return [
            'warning' => ['string', 'nullable', 'in:nudity,violence,sensitive'],
        ];
    }
}
