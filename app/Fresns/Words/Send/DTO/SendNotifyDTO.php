<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send\DTO;

use Fresns\DTO\DTO;

/**
 * Class SendNotifyDTO.
 */
class SendNotifyDTO extends DTO
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'uid' => ['integer', 'required'],
            'type' => ['integer', 'required', 'between:1,8'],
            'content' => ['string', 'nullable'],
            'isMarkdown' => ['Boolean', 'nullable'],
            'isMultilingual' => ['Boolean', 'nullable', 'required_with:content'],
            'isAccessPlugin' => ['Boolean', 'nullable'],
            'pluginUnikey' => ['integer', 'nullable'],
            'actionUid' => ['integer', 'nullable'],
            'actionType' => ['integer', 'nullable', 'between:1,10', 'required_with:actionObject', 'required_with:actionFsid'],
            'actionObject' => ['integer', 'nullable', 'between:1,8', 'required_with:actionFsid'],
            'actionFsid' => ['string', 'nullable', 'required_with:actionObject'],
            'actionCid' => ['string', 'nullable'],
        ];
    }
}
