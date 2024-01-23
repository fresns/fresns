<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class UserEditDTO extends DTO
{
    public function rules(): array
    {
        return [
            'username' => ['string', 'nullable', 'alpha_dash'],
            'nickname' => ['string', 'nullable'],
            'avatarFid' => ['string', 'nullable'],
            'avatarUrl' => ['url', 'nullable'],
            'bannerFid' => ['string', 'nullable'],
            'bannerUrl' => ['url', 'nullable'],
            'gender' => ['integer', 'nullable', 'in:1,2,3'],
            'genderCustom' => ['string', 'nullable'],
            'genderPronoun' => ['integer', 'nullable', 'in:1,2,3'],
            'birthday' => ['date', 'nullable', 'before:today', 'after_or_equal:1920-01-01 00:00:00'],
            'birthdayDisplayType' => ['integer', 'nullable', 'in:1,2,3,4'],
            'bio' => ['string', 'nullable'],
            'location' => ['string', 'nullable'],
            'conversationLimit' => ['integer', 'nullable', 'in:1,2,3,4'],
            'commentLimit' => ['integer', 'nullable', 'in:1,2,3,4'],
            'contentLimit' => ['integer', 'nullable'],
            'moreInfo' => ['array', 'nullable'],
            'archives' => ['array', 'nullable'],
        ];
    }
}
