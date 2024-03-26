<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class CommonFileInfoDTO extends DTO
{
    public function rules(): array
    {
        return [
            'fileType' => ['string', 'required', 'in:image,video,audio,document'],
            'name' => ['string', 'required'],
            'mime' => ['string', 'nullable'],
            'extension' => ['string', 'required'],
            'size' => ['integer', 'required'],
            'md5' => ['string', 'nullable'],
            'sha' => ['string', 'nullable'],
            'shaType' => ['string', 'nullable', 'required_with:sha'],
            'path' => ['string', 'required'],
            'imageWidth' => ['integer', 'nullable', 'required_if:fileType,image'],
            'imageHeight' => ['integer', 'nullable', 'required_if:fileType,image'],
            'videoTime' => ['integer', 'nullable', 'required_if:fileType,video'],
            'videoPosterPath' => ['string', 'nullable'],
            'audioTime' => ['integer', 'nullable', 'required_if:fileType,audio'],
            'transcodingState' => ['integer', 'nullable', 'in:1,2,3,4'],
            'originalPath' => ['string', 'nullable'],
            'sortOrder' => ['integer', 'nullable'],
        ];
    }
}
