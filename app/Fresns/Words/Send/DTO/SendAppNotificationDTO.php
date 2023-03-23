<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send\DTO;

use Fresns\DTO\DTO;

/**
 * Class SendAppNotificationDTO.
 */
class SendAppNotificationDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'uid' => ['integer', 'required', 'exists:App\Models\User,uid'],
            'channel' => ['integer', 'nullable', 'in:1,2'],
            'template' => ['string', 'nullable'],
            'coverUrl' => ['url', 'nullable'],
            'title' => ['string', 'nullable'],
            'content' => ['string', 'nullable'],
            'time' => ['string', 'nullable', 'date_format:"Y-m-d H:i:s"'],
            'linkType' => ['integer', 'nullable', 'in:1,2,3,4,5'],
            'linkFsid' => ['string', 'required_with:linkType'],
            'linkUrl' => ['url', 'nullable'],
        ];
    }
}
