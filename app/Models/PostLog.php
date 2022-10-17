<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class PostLog extends Model
{
    protected $casts = [
        'map_json' => 'json',
        'allow_json' => 'json',
        'user_list_json' => 'json',
        'comment_btn_json' => 'json',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function files()
    {
        return $this->hasMany(FileUsage::class, 'id', 'table_id')->where('table_name', 'post_logs')->where('table_column', 'id');
    }

    public function extends()
    {
        return $this->hasMany(ExtendUsage::class, 'id', 'usage_id')->where('usage_type', ExtendUsage::TYPE_POST_LOG);
    }
}
