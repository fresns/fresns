<?php

namespace App\Fresns\Words\Basis\DTO;

use Fresns\DTO\DTO;


/**
 * Class DecodeSignDTO
 * @package App\Fresns\Words\Basis\DTO
 */

class DecodeSignDTO extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return ['name'=> 'required'];
    }

}
