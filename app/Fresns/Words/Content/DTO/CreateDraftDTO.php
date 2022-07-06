<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content\DTO;

use Fresns\DTO\DTO;

class CreateDraftDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'uid' => ['integer', 'required', 'exists:App\Models\User,uid'],
            'type' => ['integer', 'required', 'in:1,2'],
            'createType' => ['integer', 'required', 'in:1,2'],
            'editorUnikey' => ['string', 'nullable', 'exists:App\Models\Plugin,unikey'],
            'postGid' => ['string', 'nullable'],
            'postTitle' => ['string', 'nullable'],
            'postIsComment' => ['boolean', 'nullable'],
            'postIsCommentPublic' => ['boolean', 'nullable'],
            'commentPid' => ['string', 'nullable', 'required_if:type,2'],
            'commentCid' => ['string', 'nullable'],
            'content' => ['string', 'nullable'],
            'isMarkdown' => ['boolean', 'nullable'],
            'isAnonymous' => ['boolean', 'nullable'],
            'mapJson' => ['array', 'nullable'],
            'eid' => ['string', 'nullable'],
        ];
    }
}
