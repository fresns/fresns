<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

use Fresns\DTO\DTO;

class IpInfoDTO extends DTO
{
    public function rules(): array
    {
        return [
            'ip' => ['ip', 'required'],
        ];
    }
}
