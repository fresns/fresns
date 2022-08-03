<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

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
            'type' => ['integer', 'required', 'between:1,16'],
            'platformId' => ['integer', 'required', 'between:1,13'],
            'version' => ['string', 'required'],
            'langTag' => ['string', 'nullable'],
            'pluginUnikey' => ['string', 'nullable', 'exists:App\Models\Plugin,unikey'],
            'aid' => ['string', 'nullable', 'exists:App\Models\Account,aid'],
            'uid' => ['integer', 'nullable', 'exists:App\Models\User,uid'],
            'objectName' => ['string', 'required'],
            'objectAction' => ['string', 'required'],
            'objectResult' => ['integer', 'required', 'in:1,2,3'],
            'objectOrderId' => ['string', 'nullable'],
            'deviceInfo' => ['array', 'nullable'],
            'deviceToken' => ['string', 'nullable'],
            'moreJson' => ['array', 'nullable'],
        ];
    }
}
