<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class NotificationDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:all,choose'],
            'notificationType' => ['integer', 'nullable', 'between:0,8', 'required_without:notificationIds', 'required_if:type,all'],
            'notificationIds' => ['string', 'nullable', 'required_without:notificationType', 'required_if:type,choose'],
        ];
    }
}
