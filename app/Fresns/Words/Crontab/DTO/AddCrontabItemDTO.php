<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Crontab\DTO;

use Fresns\DTO\DTO;

/**
 * Class AddCrontabItemDTO.
 */
class AddCrontabItemDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'unikey' => ['string', 'required', 'exists:App\Models\Plugin,unikey'],
            'cmdWord' => ['string', 'required'],
            'cronTableFormat' => ['string', 'required'],
        ];
    }
}
