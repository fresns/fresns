<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class DialogDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:dialog,message'],
            'dialogId' => ['integer', 'nullable', 'required_without:messageIds', 'required_if:type,dialog'],
            'messageIds' => ['string', 'nullable', 'required_without:dialogId', 'required_if:type,message'],
        ];
    }
}
