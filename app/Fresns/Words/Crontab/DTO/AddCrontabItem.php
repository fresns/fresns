<?php

namespace App\Fresns\Words\Crontab\DTO;

use Fresns\DTO\DTO;

/**
 * Class AddCrontabItem
 * @package App\Fresns\Words\File\DTO
 */
class AddCrontabItem extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'unikey'=>'string',
            'cmdWord'=>'string',
            'taskPeriod' => 'string'
        ];
    }
}
