<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class CommentLog extends Model
{
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(FileUsage::class, 'id', 'table_id')->where('table_name', 'comment_logs')->where('table_column', 'id');
    }

    public function extends()
    {
        return $this->hasMany(ExtendUsage::class, 'id', 'usage_id')->where('usage_type', ExtendUsage::TYPE_COMMENT_LOG);
    }
}
