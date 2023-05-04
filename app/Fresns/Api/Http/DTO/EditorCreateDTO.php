<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class EditorCreateDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:post,comment'],
            'createType' => ['integer', 'required', 'in:1,2'],
            'editorFskey' => ['string', 'nullable'],
            'postGid' => ['string', 'nullable'],
            'postTitle' => ['string', 'nullable'],
            'postIsCommentDisabled' => ['boolean', 'nullable'],
            'postIsCommentPrivate' => ['boolean', 'nullable'],
            'postQuotePid' => ['string', 'nullable'],
            'commentPid' => ['string', 'nullable', 'required_if:type,comment'],
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
