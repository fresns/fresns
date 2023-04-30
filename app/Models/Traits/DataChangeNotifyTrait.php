<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Fresns\Subscribe\Subscribe;

trait DataChangeNotifyTrait
{
    public static function bootDataChangeNotifyTrait(): void
    {
        static::created(function ($model) {
            notifyDataChange([
                'tableName' => static::class,
                'primaryId' => $model->id,
                'changeType' => Subscribe::CHANGE_TYPE_CREATED,
            ]);
        });

        static::updated(function ($model) {
            notifyDataChange([
                'tableName' => static::class,
                'primaryId' => $model->id,
                'changeType' => Subscribe::CHANGE_TYPE_UPDATED,
            ]);
        });

        static::deleted(function ($model) {
            notifyDataChange([
                'tableName' => static::class,
                'primaryId' => $model->id,
                'changeType' => Subscribe::CHANGE_TYPE_DELETED,
            ]);
        });
    }
}
