<?php

namespace App\Fresns\Words\File\DTO;

use Fresns\DTO\DTO;

/**
 * Class LogicalDeletionFile
 * @package App\Fresns\Words\File\DTO
 */
class UploadFileInfo extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'platform' => 'integer',
            'type' => 'required|in:1,2,3,4',
            'tableType' => 'integer',
            'tableName' => ['required','string'],
            'tableColumn' => 'string',
            'tableId'=>'string',
            'tableKey' => 'string',
            'fileInfo' =>['string','required'],
            'aid' => 'string',
            'uid' => 'integer'
        ];
    }
}
