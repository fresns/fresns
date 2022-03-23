<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Language extends Model
{
    protected $guarded = ['id'];

    public function scopeOfConfig($query)
    {
        return $query->where('table_name', 'configs');
    }

    public function scopeTableName($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }
}
