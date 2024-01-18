<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class FileUsage extends Model
{
    const TYPE_OTHER = 1;
    const TYPE_SYSTEM = 2;
    const TYPE_STICKER = 3;
    const TYPE_USER = 4;
    const TYPE_CONVERSATION = 5;
    const TYPE_POST = 6;
    const TYPE_COMMENT = 7;
    const TYPE_EXTEND = 8;
    const TYPE_App = 9;

    public function scopeFileType($query, int $type)
    {
        return $query->where('file_type', $type);
    }

    public function scopeType($query, int $type)
    {
        return $query->where('usage_type', $type);
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id', 'id');
    }
}
