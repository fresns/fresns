<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class WalletDecreaseDTO extends DTO
{
    public function rules(): array
    {
        return [
            'aid' => ['string', 'required'],
            'uid' => ['integer', 'nullable'],
            'password' => ['string', 'nullable'],
            'amountTotal' => ['numeric', 'required'],
            'systemFee' => ['numeric', 'required'],
            'originAid' => ['string', 'nullable'],
            'originUid' => ['integer', 'nullable'],
            'transactionFskey' => ['string', 'required', 'exists:App\Models\App,fskey'],
            'transactionId' => ['integer', 'nullable'],
            'transactionCode' => ['string', 'nullable'],
            'remark' => ['string', 'nullable'],
            'moreJson' => ['json', 'nullable'],
        ];
    }
}
