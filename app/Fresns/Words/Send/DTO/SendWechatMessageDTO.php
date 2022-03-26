<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send\DTO;

use Fresns\DTO\DTO;

/**
 * Class SendWechatMessageDTO.
 */
class SendWechatMessageDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'uid' => ['required', 'integer'],
            'channel' => ['nullable', 'in:1,2'],
            'template' => ['nullable', 'string'],
            'coverUrl' => ['nullable', 'url'],
            'title' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'time' => ['nullable', 'date_format:"Y-m-d H:i:s"'],
            'linkType' => ['nullable', 'in:1,2,3,4,5'],
            'linkFsid' => ['required_with:linkType', 'string'],
            'linkUrl' => ['nullable', 'url'],
        ];
    }
}
