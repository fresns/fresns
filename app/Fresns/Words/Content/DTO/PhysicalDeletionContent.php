<?php

namespace App\Fresns\Words\Content\DTO;

use Fresns\DTO\DTO;

class PhysicalDeletionContent extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'integer'],
            'contentType' => ['required', 'integer'],
            'contentId' => 'integer',
            'contentFsid' => 'string',
        ];
    }
}
