<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic\DTO;

use Fresns\DTO\DTO;

/**
 * Class IpInfoDTO.
 */
class IpInfoDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'ipv4' => ['string', 'nullable', 'required_without:ipv6'],
            'ipv6' => ['string', 'nullable', 'required_without:ipv4'],
        ];
    }
}
