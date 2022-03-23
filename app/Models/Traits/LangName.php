<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Models\Language;

trait LangName
{
    public function names()
    {
        return $this->hasMany(Language::class, 'table_id', 'id')
            ->where('table_column', 'name')
            ->where('table_name', $this->getTable());
    }

    public function getLangName($langTag)
    {
        return $this->names->where('lang_tag', $langTag)->first()?->lang_content ?: $this->name;
    }
}
