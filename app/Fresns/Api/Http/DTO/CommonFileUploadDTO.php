<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class CommonFileUploadDTO extends DTO
{
    public function rules(): array
    {
        return [
            'usageType' => ['string', 'required', 'in:userAvatar,userBanner,conversation,post,comment,postDraft,commentDraft'],
            'usageFsid' => ['string', 'required'],
            'type' => ['string', 'required', 'in:image,video,audio,document'],
            'file' => ['file', 'required'],
            'warning' => ['string', 'nullable', 'in:none,nudity,violence,sensitive'],
            'moreInfo' => ['string', 'nullable'],
        ];
    }
}
