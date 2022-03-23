<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\File\DTO;

use Fresns\DTO\DTO;

/***
 * Class GetFileUrlOfAntiLink
 * @package App\Fresns\Words\File\DTO
 */
class GetFileInfoOfAntiLink extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'fileId' => ['integer', 'required_without:fid'],
            'fid' => ['integer', 'required_without:fileId'],
        ];
    }
}
