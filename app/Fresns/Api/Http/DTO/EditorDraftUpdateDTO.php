<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class EditorDraftUpdateDTO extends DTO
{
    public function rules(): array
    {
        return [
            'editorFskey' => ['string', 'nullable'],
            'quotePid' => ['string', 'nullable'],
            'gid' => ['string', 'nullable'],
            'title' => ['string', 'nullable'],
            'content' => ['string', 'nullable'],
            'isMarkdown' => ['boolean', 'nullable'],
            'isAnonymous' => ['boolean', 'nullable'],
            'commentPolicy' => ['integer', 'nullable', 'in:1,2,3,4,5'],
            'commentPrivate' => ['boolean', 'nullable'],
            'gtid' => ['string', 'nullable'],
            'locationInfo' => ['array', 'nullable'],
            'fileInfo' => ['array', 'nullable'],
            'archives' => ['array', 'nullable'],
            'extends' => ['array', 'nullable'],
            'deleteLocation' => ['boolean', 'nullable'],
            'deleteExtend' => ['string', 'nullable'], // eid
            'deleteFile' => ['string', 'nullable'], // fid
        ];
    }
}
