<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class EditorDraftCreateDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:post,comment'],
            'editorFskey' => ['string', 'nullable'],
            'createType' => ['integer', 'required', 'in:1,2'],
            'commentPid' => ['string', 'nullable', 'required_if:type,comment'],
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
