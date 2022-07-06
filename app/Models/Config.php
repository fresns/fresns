<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class Config extends Model
{
    public function getItemValueAttribute($value)
    {
        if (in_array($this->item_type, ['array', 'plugins', 'object'])) {
            $value = json_decode($value, true) ?: [];
        } elseif ($this->item_type == 'boolean') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        } elseif ($this->item_type == 'number') {
            $value = intval($value);
        }

        return $value;
    }

    public function setItemValueAttribute($value)
    {
        if (in_array($this->item_type, ['array', 'plugins', 'object']) || is_array($value)) {
            $value = json_encode($value);
        }

        if ($this->item_type == 'boolean') {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        }

        if ($this->item_type == 'number') {
            $value = intval($value);
        }

        $this->attributes['item_value'] = $value;
    }

    public function scopePlatform($query)
    {
        return $query->where('item_key', 'platforms');
    }

    public function scopeTag($query, $value)
    {
        return $query->where('item_tag', $value);
    }

    public function setDefaultValue()
    {
        if ($this->item_type == 'boolean') {
            $this->item_value = false;
        } elseif ($this->item_type == 'number') {
            $this->item_value = 0;
        } elseif ($this->item_type == 'array') {
            $this->item_value = [];
        } else {
            $this->item_value = null;
        }

        return $this;
    }

    public function languages()
    {
        return $this->hasMany(Language::class, 'table_key', 'item_key')->where('table_name', 'configs');
    }
}
