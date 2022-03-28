<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content\DTO;

use Fresns\DTO\DTO;

class LogicalDeletionContentDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:1,2'],
            'contentType' => ['required', 'in:1,2'],
            'contentFsid' => ['nullable', 'required_if:contentType,1', 'string'],
            'contentId' => ['nullable', 'required_if:contentType,2', 'integer'],
        ];
    }
}
