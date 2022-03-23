<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

use Illuminate\Support\Collection;

class SessionKey extends Model
{
    protected $fillable = [
        'platform_id',
        'name',
        'type',
        'is_enable',
        'plugin_unikey',
    ];

    /**
     * get config platform name.
     *
     * @param  array  $platforms
     * @return string
     */
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
