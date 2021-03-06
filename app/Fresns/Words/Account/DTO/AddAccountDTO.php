<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

/**
 * Class AddAccountDTO.
 *
 * @property int $type
 * @property string $account
 * @property int $countryCode
 * @property int $connectInfo
 * @property string $password
 */
class AddAccountDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['integer', 'required', 'in:1,2,3'],
            'account' => ['string', 'nullable', 'required_if:type,1,2'],
            'countryCode' => ['integer', 'nullable', 'required_if:type,2'],
            'connectInfo' => ['array', 'nullable', 'required_if:type,3'],
            'password' => ['string', 'nullable'],
        ];
    }
}
