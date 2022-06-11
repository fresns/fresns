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
            'type' => ['integer', 'required', 'in:1,2,3'],
            'aid' => ['string', 'required', 'exists:App\Models\Account,aid'],
            'uid' => ['integer', 'nullable', 'exists:App\Models\User,uid'],
            'amount' => ['numeric', 'required'],
            'transactionAmount' => ['numeric', 'required'],
            'systemFee' => ['numeric', 'required'],
            'originAid' => ['string', 'nullable', 'exists:App\Models\Account,aid'],
            'originUid' => ['integer', 'nullable', 'exists:App\Models\User,uid'],
            'originUnikey' => ['string', 'required', 'exists:App\Models\Plugin,unikey'],
            'originId' => ['integer', 'nullable'],
        ];
    }
}
