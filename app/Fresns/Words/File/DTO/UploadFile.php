<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\File\DTO;

use Fresns\DTO\DTO;

/**
 * Class LogicalDeletionFile.
 */
class UploadFile extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'platform' => 'integer',
            'type' => 'required|in:1,2,3,4',
            'tableType' => 'integer',
            'tableName' => ['required', 'string'],
            'tableColumn' => 'string',
            'tableId'=>'string',
            'tableKey' => 'string',
            'file' =>['file', 'required'],
            'aid' => 'string',
            'uid' => 'integer',
        ];
    }
}
