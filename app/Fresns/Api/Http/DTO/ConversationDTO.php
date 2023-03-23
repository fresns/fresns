<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class ConversationDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:conversation,message'],
            'conversationId' => ['integer', 'nullable', 'required_without:messageIds', 'required_if:type,conversation'],
            'messageIds' => ['string', 'nullable', 'required_without:conversationId', 'required_if:type,message'],
        ];
    }
}
