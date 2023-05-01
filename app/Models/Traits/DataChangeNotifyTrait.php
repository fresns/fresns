<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Models\Config;
use App\Models\Language;
use App\Utilities\SubscribeUtility;

trait DataChangeNotifyTrait
{
    public static function bootDataChangeNotifyTrait(): void
    {
        if (static::class == Config::class || static::class == Language::class) {
            return;
        }

        static::created(function ($model) {
            SubscribeUtility::notifyDataChange(static::class, $model->id, SubscribeUtility::CHANGE_TYPE_CREATED);
        });

        static::updated(function ($model) {
            SubscribeUtility::notifyDataChange(static::class, $model->id, SubscribeUtility::CHANGE_TYPE_UPDATED);
        });

        static::deleted(function ($model) {
            SubscribeUtility::notifyDataChange(static::class, $model->id, SubscribeUtility::CHANGE_TYPE_DELETED);
        });
    }
}
