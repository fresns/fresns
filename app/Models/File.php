<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = ['fid', 'file_type', 'file_name', 'file_extension', 'file_path', 'rank_num', 'is_enable', 'table_type', 'table_name', 'table_column', 'table_id', 'table_key'];

    use HasFactory;

    public function appends()
    {
        return $this->hasOne(FileAppend::class);
    }
}
