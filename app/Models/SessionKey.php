<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

use Illuminate\Support\Collection;

class SessionKey extends Model
{
    const TYPE_CORE = 1;
    const TYPE_MANAGE = 2;
    const TYPE_PLUGIN = 3;

    use Traits\IsEnableTrait;

    public function platformName($platforms = []): string
    {
        if (! $platforms instanceof Collection) {
            $platforms = collect($platforms);
        }

        $platform = $platforms->where('id', $this->platform_id)->first();
        if (! $platform) {
            return '';
        }

        return $platform['name'] ?? '';
    }

    public function plugin()
    {
        return $this->belongsTo(Plugin::class, 'plugin_unikey', 'unikey');
    }
}
