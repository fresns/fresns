<?php

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

/**
 * Class CreateSessionToken
 * @package App\Fresns\Words\Account\DTO
 */
class CreateSessionToken extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return [
            'platform' => ['required','integer'],
            'aid' => 'required',
            'uid' => 'integer',
            'expiredTime' => 'date'
        ];
    }
}
