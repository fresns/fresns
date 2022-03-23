<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Wallet\DTO;

use Fresns\DTO\DTO;

class WalletIncrease extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['integer', 'required'],
            'aid' => ['string', 'required'],
            'uid' => 'integer',
            'amount' => ['integer', 'required'],
            'transactionAmount' => ['integer', 'required'],
            'systemFee' => ['integer', 'required'],
            'originAid' => 'string',
            'originUid' => 'integer',
            'originName' => ['string', 'required'],
            'originId' => ['integer'],
        ];
    }
}
