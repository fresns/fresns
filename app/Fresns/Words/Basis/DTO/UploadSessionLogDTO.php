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
            'type' => ['integer', 'required', 'between:1,16'],
            'pluginUnikey' => ['string', 'nullable', 'exists:App\Models\Plugin,unikey'],
            'platformId' => ['integer', 'required', 'between:1,13'],
            'version' => ['string', 'required'],
            'langTag' => ['string', 'nullable'],
            'aid' => ['string', 'nullable', 'exists:App\Models\Account,aid'],
            'uid' => ['integer', 'nullable', 'exists:App\Models\User,uid'],
            'objectName' => ['string', 'required'],
            'objectAction' => ['string', 'required'],
            'objectResult' => ['integer', 'required', 'in:1,2,3'],
            'objectOrderId' => ['string', 'nullable'],
            'deviceInfo' => ['string', 'nullable'],
            'deviceToken' => ['string', 'nullable'],
            'moreJson' => ['string', 'nullable'],
        ];
    }
}
