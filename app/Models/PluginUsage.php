<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class PluginUsage extends Model
{
    const TYPE_WALLET_RECHARGE = 1;
    const TYPE_WALLET_WITHDRAW = 2;
    const TYPE_EDITOR = 3;
    const TYPE_CONTENT = 4;
    const TYPE_MANAGE = 5;
    const TYPE_GROUP = 6;
    const TYPE_FEATURE = 7;
    const TYPE_PROFILE = 8;
    const TYPE_MAP = 9;

    const SCENE_POST = 1;
    const SCENE_COMMENT = 2;
    const SCENE_USER = 3;

    use Traits\LangNameTrait;
    use Traits\PluginUsageServiceTrait;
    use Traits\IsEnableTrait;

    protected $casts = [
        'data_sources' => 'json',
    ];

    public function scopeType($query, int $type)
    {
        return $query->where('usage_type', $type);
    }

    public function plugin()
    {
        return $this->belongsTo(Plugin::class, 'plugin_unikey', 'unikey');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }
}
