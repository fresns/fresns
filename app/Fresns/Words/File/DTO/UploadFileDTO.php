<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\File\DTO;

use Fresns\DTO\DTO;

class UploadFileDTO extends DTO
{
    public function rules(): array
    {
        return [
            'file' => ['file', 'required'],
            'type' => ['integer', 'required', 'in:1,2,3,4'],
            'warningType' => ['integer', 'nullable'],
            'usageType' => ['integer', 'required', 'between:1,10'],
            'platformId' => ['integer', 'required'],
            'tableName' => ['string', 'required'],
            'tableColumn' => ['string', 'required'],
            'tableId' => ['integer', 'nullable', 'required_without:tableKey'],
            'tableKey' => ['string', 'nullable', 'required_without:tableId'],
            'moreInfo' => ['array', 'nullable'],
            'aid' => ['string', 'nullable'],
            'uid' => ['integer', 'nullable'],
        ];
    }
}
