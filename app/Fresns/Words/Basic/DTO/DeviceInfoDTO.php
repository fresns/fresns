<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

use Fresns\DTO\DTO;

class DeviceInfoDTO extends DTO
{
    public function rules(): array
    {
        return [
            'agent' => ['string', 'nullable'],
            'type' => ['string', 'nullable'],
            'platformName' => ['string', 'nullable'],
            'platformFamily' => ['string', 'nullable'],
            'platformVersion' => ['string', 'nullable'],
            'browserName' => ['string', 'nullable'],
            'browserFamily' => ['string', 'nullable'],
            'browserVersion' => ['string', 'nullable'],
            'browserEngine' => ['string', 'nullable'],
            'deviceFamily' => ['string', 'nullable'],
            'deviceModel' => ['string', 'nullable'],
            'deviceMac' => ['mac_address', 'nullable'],
            'appImei' => ['string', 'nullable'],
            'appAndroidId' => ['string', 'nullable'],
            'appOaid' => ['string', 'nullable'],
            'appIdfa' => ['string', 'nullable'],
            'simImsi' => ['string', 'nullable'],
            'networkType' => ['string', 'nullable'],
            'networkIpv4' => ['ipv4', 'nullable', 'required_without:networkIpv6'],
            'networkIpv6' => ['ipv6', 'nullable', 'required_without:networkIpv4'],
            'networkPort' => ['string', 'nullable'],
            'networkTimezone' => ['string', 'nullable'],
            'networkOffset' => ['integer', 'nullable'],
            'networkIsp' => ['string', 'nullable'],
            'networkOrg' => ['string', 'nullable'],
            'networkAs' => ['string', 'nullable'],
            'networkAsName' => ['string', 'nullable'],
            'networkMobile' => ['boolean', 'nullable'],
            'networkProxy' => ['boolean', 'nullable'],
            'networkHosting' => ['boolean', 'nullable'],
            'mapId' => ['integer', 'nullable'],
            'latitude' => ['numeric', 'nullable', 'min:-90', 'max:90'],
            'longitude' => ['numeric', 'nullable', 'min:-180', 'max:180'],
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
