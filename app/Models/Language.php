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

    public function formatConfigItemValue(string $itemType)
    {
        $content = $this->lang_content;

        if (in_array($itemType, ['array', 'plugins', 'object'])) {
            $content = json_decode($this->lang_content, true) ?: [];
        } elseif ($itemType == 'boolean') {
            $content = filter_var($this->lang_content, FILTER_VALIDATE_BOOLEAN);
        } elseif ($itemType == 'number') {
            $content = intval($this->lang_content);
        }

        return $content;
    }
}
