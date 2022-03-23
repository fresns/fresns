<?php

namespace App\Fresns\Words\Basis\DTO;

use Fresns\DTO\DTO;

/**
 * Class VerifySignDTO
 * @property  integer $platform
 * @property  string $version
 * @property  integer $versionInt
 * @package App\Fresns\Words\Basis\DTO
 */

class VerifySignDTO extends DTO
{

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'platform' => ['required','integer'],
            'version' => ['required'],
            'versionInt' => ['required','integer'],
            'appId' => ['required'],
            'timestamp' => ['required'],
            'sign' => ['required'],
            'aid' => ['string'],
            'uid' => ['integer'],
            'token' => ['string'],
        ];
    }
}
