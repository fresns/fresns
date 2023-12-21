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
            'uid' => ['integer', 'required', 'exists:App\Models\User,uid'],
            'type' => ['integer', 'required', 'in:1,2'],
            'createType' => ['integer', 'required', 'in:1,2'],
            'editorFskey' => ['string', 'nullable', 'exists:App\Models\App,fskey'],
            'postGid' => ['string', 'nullable'],
            'postTitle' => ['string', 'nullable'],
            'postIsCommentDisabled' => ['boolean', 'nullable'],
            'postIsCommentPrivate' => ['boolean', 'nullable'],
            'postQuotePid' => ['string', 'nullable'],
            'commentPid' => ['string', 'nullable', 'required_if:type,2'],
            'commentCid' => ['string', 'nullable'],
            'content' => ['string', 'nullable'],
            'isMarkdown' => ['boolean', 'nullable'],
            'isAnonymous' => ['boolean', 'nullable'],
            'map' => ['array', 'nullable'],
            'extends' => ['array', 'nullable'],
            'archives' => ['array', 'nullable'],
        ];
    }
}
