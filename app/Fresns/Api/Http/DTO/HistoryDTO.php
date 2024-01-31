<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\DTO;

use Fresns\DTO\DTO;

class HistoryDTO extends DTO
{
    public function rules(): array
    {
        return [
            'filterType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterKeys' => ['string', 'nullable', 'required_with:filterType'],
            'filterAuthorType' => ['string', 'nullable', 'in:whitelist,blacklist'],
            'filterAuthorKeys' => ['string', 'nullable', 'required_with:filterAuthorType'],
            'pageSize' => ['integer', 'nullable', 'between:1,30'],
            'page' => ['integer', 'nullable'],
        ];
    }
}
