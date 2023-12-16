<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\StrHelper;

trait LangContentTrait
{
    public function getLangContent(string $column, ?string $langTag = null): ?string
    {
        return StrHelper::languageContent($this->$column, $langTag);
    }
}
