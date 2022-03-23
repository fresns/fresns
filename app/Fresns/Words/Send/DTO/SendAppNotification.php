<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send\DTO;

use Fresns\DTO\DTO;

/**
 * Class SendAppNotification.
 */
class SendAppNotification extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'channel' => ['integer', 'in:1,2'],
            'uid' => ['required', 'integer'],
            'template' => 'string',
            'coverUrl' => 'string',
            'title' => 'string',
            'content' => 'string',
            'time' => 'string',
            'linkType' => 'string',
            'linkUrl' => 'string',
        ];
    }
}
