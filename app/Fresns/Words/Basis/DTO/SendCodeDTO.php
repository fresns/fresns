<?php

namespace App\Fresns\Words\Basis\DTO;

use Fresns\DTO\DTO;

/**
 * Class SendCodeDTO
 * @package App\Fresns\Words\Basis\DTO
 */
class SendCodeDTO extends DTO
{
    /**
    * @return array
    */
    public function rules(): array
    {
        return [
            'type' => 'required|integer',
            'account' => 'required|string',
            'countryCode' => 'nullable|integer',
            'templateId' => 'required|integer',
            'langTag' => 'required|string',
        ];
    }
}
