<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content\DTO;

use Fresns\DTO\DTO;

class CreateDraftDTO extends DTO
{
    public function rules(): array
    {
        return [
            'uid' => ['integer', 'required'],
            'type' => ['integer', 'required', 'in:1,2'],
            'createType' => ['integer', 'required', 'in:1,2'],
            'editorFskey' => ['string', 'nullable'],
            'commentPid' => ['string', 'nullable', 'required_if:type,2'],
            'commentCid' => ['string', 'nullable'],
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
            'archives' => ['array', 'nullable'],
            'extends' => ['array', 'nullable'],
        ];
    }
}
