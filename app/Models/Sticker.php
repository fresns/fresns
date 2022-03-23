<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Sticker extends Model
{
    use Traits\LangName;

    protected $table = 'stickers';

    public function scopeGroup($query)
    {
        return $query->where('type', 2);
    }

    public function stickers()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
}
