<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class CommonFileLinkDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:post,comment,extend,conversation'],
            'fsid' => ['required'],
        ];
    }
}
