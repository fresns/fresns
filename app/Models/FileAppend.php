<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class FileAppend extends Model
{
    protected $guarded = [];

    public function file()
    {
        return $this->belongsTo(File::class, 'file_id', 'id');
    }
}
