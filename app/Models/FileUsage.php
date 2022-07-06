<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class FileUsage extends Model
{
    const TYPE_OTHER = 1;
    const TYPE_SYSTEM = 2;
    const TYPE_OPERATION = 3;
    const TYPE_STICKER = 4;
    const TYPE_USER = 5;
    const TYPE_DIALOG = 6;
    const TYPE_POST = 7;
    const TYPE_COMMENT = 8;
    const TYPE_EXTEND = 9;
    const TYPE_PLUGIN = 10;

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
