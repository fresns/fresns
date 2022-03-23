<?php

namespace App\Fresns\Words\User\DTO;

use Fresns\DTO\DTO;

class DeactivateUserDialog extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return [
           'userId' => 'integer'
        ];
    }
}
