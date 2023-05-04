<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

trait IsEnabledTrait
{
    public function scopeIsEnabled($query, bool $isEnabled = true): mixed
    {
        return $query->where('is_enabled', $isEnabled);
    }
}
