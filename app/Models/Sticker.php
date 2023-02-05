<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Sticker extends Model
{
    const TYPE_STICKER = 1;
    const TYPE_GROUP = 2;

    use Traits\LangNameTrait;
    use Traits\IsEnableTrait;

    public function scopeGroup($query)
    {
        return $query->where('type', 2);
    }

    public function stickers()
    {
        return $this->hasMany(self::class, 'parent_id', 'id');
    }
}
