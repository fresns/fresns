<?php

namespace App\Fresns\Words\Basis\DTO;

use Fresns\DTO\DTO;

/**
 * Class UploadSessionLogDTO
 * @property integer $platform
 * @property string $version
 * @property integer $versionInt
 * @package App\Fresns\Words\Basis\DTO
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
            'versionInt' => ['required', 'integer'],
            'langTag' => ['string'],
            'aid' => ['string'],
            'uid' => ['integer'],
            'objectType' => ['required','integer'],
            'objectName' => ['required','string'],
            'objectAction' => ['required','string'],
            'objectResult' => ['required','integer'],
            'objectOrderId' => ['string'],
            'deviceInfo' => ['string'],
            'deviceToken' => ['string'],
            'moreJson' => ['string']
        ];
    }
}
