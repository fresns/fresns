<?php

namespace App\Fresns\Words\Send\DTO;

use Fresns\DTO\DTO;

/**
 * Class SendSmsDTO
 * @package App\Fresns\Words\Send\DTO
 */
class SendSmsDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'countryCode' => ['required', 'integer'],
            'phoneNumber' => ['required', 'integer'],
            'signName' => 'string',
            'templateCode' => ['required', 'string'],
            'templateParam' => ['string'],
        ];
    }
}
