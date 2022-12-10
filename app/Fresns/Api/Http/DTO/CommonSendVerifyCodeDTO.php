<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class CommonSendVerifyCodeDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'type' => ['string', 'required', 'in:email,sms'],
            'useType' => ['integer', 'required', 'in:1,2,3,4,5'],
            'templateId' => ['integer', 'required', 'in:1,2,3,4,5,6,7,8'],
            'account' => ['string', 'nullable', 'required_if:useType,1', 'required_if:useType,2', 'required_if:useType,3', 'required_if:useType,5'],
            'countryCode' => ['integer', 'nullable', 'required_if:type,sms'],
        ];
    }
}
