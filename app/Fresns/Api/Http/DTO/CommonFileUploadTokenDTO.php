<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class CommonFileUploadTokenDTO extends DTO
{
    public function rules(): array
    {
        return [
            'usageType' => ['string', 'required', 'in:userAvatar,userBanner,conversation,post,comment,postDraft,commentDraft'],
            'usageFsid' => ['string', 'required'],
            'type' => ['string', 'required', 'in:image,video,audio,document'],
            'name' => ['string', 'required'],
            'mime' => ['string', 'required'],
            'extension' => ['string', 'required'],
            'size' => ['integer', 'required'],
            'md5' => ['string', 'nullable'],
            'sha' => ['string', 'nullable'],
            'shaType' => ['string', 'nullable', 'required_with:sha'],
            'width' => ['integer', 'nullable', 'required_if:type,image'],
            'height' => ['integer', 'nullable', 'required_if:type,image'],
            'duration' => ['integer', 'nullable', 'required_if:type,video', 'required_if:type,audio'],
            'warning' => ['string', 'nullable', 'in:none,nudity,violence,sensitive'],
            'moreInfo' => ['string', 'nullable'],
        ];
    }
}
