<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class GlobalArchivesDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:user,group,hashtag,geotag,post,comment'],
            'fskey' => ['string', 'nullable', 'exists:App\Models\App,fskey'],
        ];
    }
}
