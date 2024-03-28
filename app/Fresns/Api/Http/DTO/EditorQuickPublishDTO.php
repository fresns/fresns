<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class EditorQuickPublishDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:post,comment'],
            'commentPid' => ['string', 'nullable'],
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
            'locationInfo' => ['string', 'nullable'],
            'archives' => ['string', 'nullable'],
            'extends' => ['string', 'nullable'],
            'image' => ['file', 'nullable'],
        ];
    }
}
