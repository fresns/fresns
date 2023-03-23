<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Models\Language;

trait LangDescriptionTrait
{
    public function descriptions(): mixed
    {
        return $this->hasMany(Language::class, 'table_id', 'id')
            ->where('table_column', 'description')
            ->where('table_name', $this->getTable());
    }

    public function getLangDescription($langTag): ?string
    {
        return $this->descriptions->where('lang_tag', $langTag)->first()?->lang_content ?: $this->description;
    }
}
