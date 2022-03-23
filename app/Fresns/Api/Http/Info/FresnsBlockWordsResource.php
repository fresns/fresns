<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Info;

use App\Fresns\Api\Base\Resources\BaseAdminResource;

/**
 * List resource config handle.
 */
class FresnsBlockWordsResource extends BaseAdminResource
{
    public function toArray($request)
    {

        // Default Field
        $default = [
            'word' => $this->word,
            'contentMode' => $this->content_mode,
            'userMode' => $this->user_mode,
            'dialogMode' => $this->dialog_mode,
            'replaceWord' => $this->replace_word,
        ];

        return $default;
    }
}
