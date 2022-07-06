<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class EditorDirectPublishDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:post,comment'],
            'postGid' => ['string', 'nullable'],
            'postTitle' => ['string', 'nullable'],
            'postIsComment' => ['boolean', 'nullable'],
            'postIsCommentPublic' => ['boolean', 'nullable'],
            'commentPid' => ['string', 'nullable', 'required_if:type,comment'],
            'commentCid' => ['string', 'nullable'],
            'content' => ['string', 'nullable'],
            'isMarkdown' => ['boolean', 'nullable'],
            'isAnonymous' => ['boolean', 'nullable'],
            'mapJson' => ['array', 'nullable'],
            'eid' => ['string', 'nullable'],
            'file' => ['file', 'nullable'],
        ];
    }
}
