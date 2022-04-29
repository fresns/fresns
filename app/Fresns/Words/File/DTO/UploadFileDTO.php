<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\File\DTO;

use Fresns\DTO\DTO;

/**
 * Class LogicalDeletionFileDTO.
 */
class UploadFileDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', 'integer'],
            'type' => ['required', 'in:1,2,3,4'],
            'tableType' => ['required', 'integer'],
            'tableName' => ['required', 'string'],
            'tableColumn' => ['required', 'string'],
            'tableId' => ['required_without:tableKey', 'nullable', 'integer'],
            'tableKey' => ['required_without:tableId', 'nullable', 'string'],
            'aid' => ['nullable', 'string'],
            'uid' => ['nullable', 'integer'],
            'file' =>['required', 'file'],
        ];
    }
}
