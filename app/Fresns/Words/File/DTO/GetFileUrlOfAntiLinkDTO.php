<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\File\DTO;

use Fresns\DTO\DTO;

/***
 * Class GetFileUrlOfAntiLinkDTO
 * @package App\Fresns\Words\File\DTO
 */
class GetFileUrlOfAntiLinkDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'fileId' => ['required_without:fid', 'nullable', 'integer'],
            'fid' => ['required_without:fileId', 'nullable', 'string'],
        ];
    }
}
