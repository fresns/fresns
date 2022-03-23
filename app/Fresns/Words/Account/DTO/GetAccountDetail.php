<?php

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class GetAccountDetail extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return [
            'aid' => ['required','string']
        ];
    }
}
