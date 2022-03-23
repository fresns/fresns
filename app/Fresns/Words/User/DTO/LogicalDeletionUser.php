<?php

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class LogicalDeletionUser extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return [
           'accountId' => 'integer'
        ];
    }
}
