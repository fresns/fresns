<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\File\DTO;

use Fresns\DTO\DTO;

class CheckUploadPermDTO extends DTO
{
    public function rules(): array
    {
        return [
            'uid' => ['integer', 'required', 'exists:App\Models\User,uid'],
            'usageType' => ['string', 'required', 'in:userAvatar,userBanner,conversation,post,comment,postDraft,commentDraft'],
            'usageFsid' => ['string', 'required'],
            'type' => ['integer', 'required', 'in:1,2,3,4'],
            'extension' => ['string', 'nullable'],
            'size' => ['integer', 'nullable'],
            'duration' => ['integer', 'nullable'],
        ];
    }
}
