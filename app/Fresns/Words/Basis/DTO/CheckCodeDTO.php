<?php

namespace App\Fresns\Words\Basis\DTO;

use Fresns\DTO\DTO;

/**
 * Class CheckCodeDTO
 * @package App\Fresns\Words\Basis\DTO
 */
class CheckCodeDTO extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return [
            'type' => ['required','integer'],
            'account' => ['required','string'],
            'countryCode' => 'integer',
            'verifyCode' => ['required','string'],
        ];
    }
}
