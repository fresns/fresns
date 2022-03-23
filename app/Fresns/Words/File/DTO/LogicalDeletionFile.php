<?php

namespace App\Fresns\Words\File\DTO;

use Fresns\DTO\DTO;

/**
 * Class LogicalDeletionFile
 * @package App\Fresns\Words\File\DTO
 */
class LogicalDeletionFile extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'fileId' => ['integer','required_without:fid'],
            'fid' => ['integer','required_without:fileId'],
        ];
    }
}
