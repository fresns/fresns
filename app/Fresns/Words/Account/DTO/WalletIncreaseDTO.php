<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class WalletIncreaseDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:1,2,3'],
            'aid' => ['required', 'string'],
            'uid' => ['nullable', 'integer'],
            'amount' => ['required', 'integer'],
            'transactionAmount' => ['required', 'integer'],
            'systemFee' => ['required', 'integer'],
            'originAid' => ['nullable', 'string'],
            'originUid' => ['nullable', 'integer'],
            'originUnikey' => ['required', 'string'],
            'originId' => ['nullable', 'integer'],
        ];
    }
}
