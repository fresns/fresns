<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class CommentAppend extends Model
{
    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id', 'id');
    }
}
