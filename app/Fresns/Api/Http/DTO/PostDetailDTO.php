<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class PostDetailDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'pid' => ['string', 'required'],
            'mapId' => ['integer', 'nullable', 'between:1,11'],
            'mapLng' => ['numeric', 'nullable'],
            'mapLat' => ['numeric', 'nullable'],
        ];
    }
}
