<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class UserMarkListDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'markType' => ['string', 'required', 'in:like,dislike,follow,block'],
            'listType' => ['string', 'required', 'in:users,groups,hashtags,posts,comments'],
            'timeOrder' => ['string', 'nullable', 'in:asc,desc'],
            'pageSize' => ['integer', 'nullable', 'between:1,20'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
