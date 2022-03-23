<?php

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class VerifyAccount extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return [
            'type' => ['integer','in:1,2'],
            'account' => 'string',
            'countryCode' => ['required_if:type,2'],
            'password' => 'string',
            'verifyCode' => 'string'
        ];
    }
}
