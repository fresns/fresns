<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class FileUploadsDTO extends DTO
{
    public function rules(): array
    {
        return [
            'usageType' => ['string', 'required', 'in:userAvatar,userBanner,conversation,post,comment,postDraft,commentDraft'],
            'usageFsid' => ['string', 'required'],
            'type' => ['string', 'required', 'in:image,video,audio,document'],
            'uploadMode' => ['string', 'required', 'in:file,fileInfo'],
            'file' => ['file', 'nullable', 'required_if:uploadMode,file'],
            'fileInfo' => ['array', 'nullable', 'required_if:uploadMode,fileInfo'],
            'warning' => ['string', 'nullable', 'in:nudity,violence,sensitive'],
            'moreInfo' => ['array', 'nullable'],
        ];
    }
}
