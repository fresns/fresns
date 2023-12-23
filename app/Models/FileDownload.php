<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class FileDownload extends Model
{
    const TYPE_USER = 1;
    const TYPE_GROUP = 2;
    const TYPE_HASHTAG = 3;
    const TYPE_GEOTAG = 4;
    const TYPE_POST = 5;
    const TYPE_COMMENT = 6;
    const TYPE_POST_LOG = 7;
    const TYPE_COMMENT_LOG = 8;
    const TYPE_EXTEND = 9;
    const TYPE_CONVERSATION = 10;

    public function scopeFileType($query, int $type)
    {
        return $query->where('file_type', $type);
    }

    public function scopeType($query, int $type)
    {
        return $query->where('object_type', $type);
    }

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
