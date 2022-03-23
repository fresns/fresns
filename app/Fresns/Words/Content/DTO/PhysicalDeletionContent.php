<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content\DTO;

use Fresns\DTO\DTO;

class PhysicalDeletionContent extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'integer'],
            'contentType' => ['required', 'integer'],
            'contentId' => 'integer',
            'contentFsid' => 'string',
        ];
    }
}
