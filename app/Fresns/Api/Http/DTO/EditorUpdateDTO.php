<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class EditorUpdateDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:post,comment'],
            'draftId' => ['integer', 'required'],
            'editorFskey' => ['string', 'nullable'],
            'postGid' => ['string', 'nullable'],
            'postTitle' => ['string', 'nullable'],
            'postIsCommentDisabled' => ['boolean', 'nullable'],
            'postIsCommentPrivate' => ['boolean', 'nullable'],
            'postQuotePid' => ['string', 'nullable'],
            'content' => ['string', 'nullable'],
            'isMarkdown' => ['boolean', 'nullable'],
            'isAnonymous' => ['boolean', 'nullable'],
            'map' => ['array', 'nullable'],
            'extends' => ['array', 'nullable'],
            'archives' => ['array', 'nullable'],
            'deleteMap' => ['boolean', 'nullable'],
            'deleteFile' => ['string', 'nullable'],
            'deleteArchive' => ['string', 'nullable'],
            'deleteExtend' => ['string', 'nullable'],
        ];
    }
}
