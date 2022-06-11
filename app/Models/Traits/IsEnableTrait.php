<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

trait IsEnableTrait
{
    public function scopeIsEnable($query, bool $isEnable = true)
    {
        return $query->where('is_enable', $isEnable);
    }
}
