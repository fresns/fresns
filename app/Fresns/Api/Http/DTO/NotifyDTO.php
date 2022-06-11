<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class NotifyDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'markType' => ['string', 'required', 'in:all,choose'],
            'type' => ['integer', 'nullable', 'required_without:ids', 'required_if:markType,all'],
            'ids' => ['string', 'nullable', 'required_without:type', 'required_if:markType,choose'],
        ];
    }
}
