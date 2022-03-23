<?php

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

/**
 * Class VerifySessionToken
 * @package App\Fresns\Words\Account\DTO
 */
class VerifySessionToken extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return [
            'platform' => ['required','integer'],
            'aid' => ['required','string'],
            'uid' => 'integer',
            'token' => ['required','string']
        ];
    }
}
