<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class CommonUploadFileDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'tableName' => ['string', 'required', 'in:users,posts,comments,conversation_messages,post_logs,comment_logs'],
            'tableColumn' => ['string', 'required'],
            'tableId' => ['integer', 'nullable', 'required_without:tableKey'],
            'tableKey' => ['string', 'nullable', 'required_without:tableId'],
            'type' => ['string', 'required', 'in:image,video,audio,document'],
            'uploadMode' => ['string', 'required', 'in:file,fileInfo'],
            'fileInfo' => ['array', 'nullable', 'required_if:uploadMode,fileInfo'],
            'moreJson' => ['array', 'nullable'],
            'file' => ['file', 'nullable', 'required_if:uploadMode,file'],
        ];
    }
}
