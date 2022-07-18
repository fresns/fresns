<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class EditorDraftsDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'status' => ['integer', 'nullable', 'in:1,2'],
            'pageSize' => ['integer', 'nullable', 'between:1,25'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
