<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class DialogSendMessageDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'uidOrUsername' => ['string', 'required'],
            'message' => ['string', 'nullable', 'required_without:fid'],
            'fid' => ['string', 'nullable', 'required_without:message'],
        ];
    }
}
