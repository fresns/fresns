<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basis\DTO;

use Fresns\DTO\DTO;

/**
 * Class UploadSessionLogDTO.
 *
 * @property int $platform
 * @property string $version
 */
class UploadSessionLogDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', 'integer'],
            'version' => ['required'],
            'langTag' => ['string'],
            'aid' => ['string'],
            'uid' => ['integer'],
            'objectType' => ['required', 'integer'],
            'objectName' => ['required', 'string'],
            'objectAction' => ['required', 'string'],
            'objectResult' => ['required', 'integer'],
            'objectOrderId' => ['string'],
            'deviceInfo' => ['string'],
            'deviceToken' => ['string'],
            'moreJson' => ['string'],
        ];
    }
}
