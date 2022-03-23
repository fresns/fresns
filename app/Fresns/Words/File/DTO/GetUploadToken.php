<?php

namespace App\Fresns\Words\File\DTO;

use Fresns\DTO\DTO;

/**
 * Class GetUploadToken
 * @package App\Fresns\Words\File\DTO
 */
class GetUploadToken extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => 'integer',
            'scene' => 'integer',
        ];
    }
}
