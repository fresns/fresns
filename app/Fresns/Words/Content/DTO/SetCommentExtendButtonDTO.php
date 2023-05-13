<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content\DTO;

use Fresns\DTO\DTO;

class SetCommentExtendButtonDTO extends DTO
{
    public function rules(): array
    {
        return [
            'cid' => ['string', 'required'],
            'type' => ['string', 'required', 'in:add,remove'],
            'close' => ['boolean', 'nullable', 'required_without:change'],
            'change' => ['string', 'nullable', 'in:default,active', 'required_without:close'],
            'activeNameKey' => ['string', 'nullable'],
            'activeStyle' => ['string', 'nullable', 'in:primary,secondary,success,danger,warning,info'],
        ];
    }
}
