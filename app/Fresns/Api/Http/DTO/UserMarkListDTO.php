<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class UserMarkListDTO extends DTO
{
    public function rules(): array
    {
        return [
            'markType' => ['string', 'required', 'in:likes,dislikes,following,blocking'],
            'listType' => ['string', 'required', 'in:users,groups,hashtags,geotags,posts,comments'],
            'orderDirection' => ['string', 'nullable', 'in:asc,desc'],
            'filterType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterKeys' => ['string', 'nullable', 'required_with:filterType'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
