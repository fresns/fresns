<?php

namespace App\Fresns\Words\Account\DTO;

use Fresns\DTO\DTO;

class LogicalDeletionAccount extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return [
            'accountId' => ['required','integer']
        ];
    }
}
