<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class CommonUploadFileDTO extends DTO
{
    public function rules(): array
    {
        return [
            'usageType' => ['integer', 'required', 'between:1,10'],
            'tableName' => ['string', 'required'],
            'tableColumn' => ['string', 'required'],
            'tableId' => ['integer', 'nullable', 'required_without:tableKey'],
            'tableKey' => ['string', 'nullable', 'required_without:tableId'],
            'type' => ['string', 'required', 'in:image,video,audio,document'],
            'uploadMode' => ['string', 'required', 'in:file,fileInfo'],
            'fileInfo' => ['array', 'nullable', 'required_if:uploadMode,fileInfo'],
            'moreInfo' => ['array', 'nullable'],
            'file' => ['file', 'nullable', 'required_if:uploadMode,file'],
        ];
    }
}
