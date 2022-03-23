<?php

namespace App\Fresns\Words\Send\DTO;

use Fresns\DTO\DTO;

/**
 * Class SendEmailDTO
 * @package App\Fresns\Words\Send\DTO
 */
class SendEmailDTO extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return [
            'email' => ['required','email'],
            'title' => 'required',
            'content' => 'required',
        ];
    }
}
