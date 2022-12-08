<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

use Fresns\DTO\DTO;

/**
 * Class DeviceInfoDTO.
 */
class DeviceInfoDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'agent' => ['string', 'nullable'],
            'type' => ['string', 'nullable'],
            'mac' => ['mac_address', 'nullable'],
            'brand' => ['string', 'nullable'],
            'model' => ['string', 'nullable'],
            'platformName' => ['string', 'nullable'],
            'platformVersion' => ['string', 'nullable'],
            'browserName' => ['string', 'nullable'],
            'browserVersion' => ['string', 'nullable'],
            'browserEngine' => ['string', 'nullable'],
            'networkType' => ['string', 'nullable'],
            'networkIpv4' => ['ipv4', 'nullable', 'required_without:networkIpv6'],
            'networkIpv6' => ['ipv6', 'nullable', 'required_without:networkIpv4'],
            'networkPort' => ['string', 'nullable'],
            'networkTimezone' => ['string', 'nullable'],
            'networkOffset' => ['integer', 'nullable'],
            'networkCurrency' => ['string', 'nullable'],
            'networkIsp' => ['string', 'nullable'],
            'networkOrg' => ['string', 'nullable'],
            'networkAs' => ['string', 'nullable'],
            'networkAsName' => ['string', 'nullable'],
            'networkMobile' => ['boolean', 'nullable'],
            'networkProxy' => ['boolean', 'nullable'],
            'networkHosting' => ['boolean', 'nullable'],
            'appImei' => ['string', 'nullable'],
            'appAndroidId' => ['string', 'nullable'],
            'appOaid' => ['string', 'nullable'],
            'appIdfa' => ['string', 'nullable'],
            'simImsi' => ['string', 'nullable'],
            'mapId' => ['integer', 'nullable'],
            'latitude' => ['string', 'nullable'],
            'longitude' => ['string', 'nullable'],
            'scale' => ['string', 'nullable'],
            'continent' => ['string', 'nullable'],
            'continentCode' => ['string', 'nullable'],
            'country' => ['string', 'nullable'],
            'countryCode' => ['string', 'nullable'],
            'region' => ['string', 'nullable'],
            'regionCode' => ['string', 'nullable'],
            'city' => ['string', 'nullable'],
            'cityCode' => ['string', 'nullable'],
            'district' => ['string', 'nullable'],
            'address' => ['string', 'nullable'],
            'zip' => ['string', 'nullable'],
        ];
    }
}
