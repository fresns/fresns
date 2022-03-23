<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

/**
 * Class AddAccount.
 *
 * @property int $type
 * @property string $account
 * @property int $countryCode
 * @property int $connectInfo
 * @property string $password
 */
class AddAccount extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:1,2,3'],
            'account' => ['required_if:type,1,2'],
            'countryCode' => 'required_if:type,2',
            'connectInfo' => 'required_if:type,3',
            'password' => 'string',
        ];
    }
}
