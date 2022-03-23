<?php

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class VerifyUser extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return [
            'uid' => ['integer','required'],
            'password' => 'string'
        ];
    }
}
