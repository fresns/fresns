<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class CommonFileStorageTokenDTO extends DTO
{
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:image,video,audio,document'],
            'usageType' => ['string', 'required', 'in:userAvatar,userBanner,conversationMessage,post,comment,postDraft,commentDraft'],
        ];
    }
}
