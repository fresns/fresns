<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

trait DataChangeNotifyTrait
{
    public static function bootDataChangeNotifyTrait(): void
    {
        static::created(function ($model) {
            notifyDataChange([
                'tableName' => static::class,
                'primaryId' => $model->id,
                'changeType' => 'created',
            ]);
        });

        static::deleted(function ($model) {
            notifyDataChange([
                'tableName' => static::class,
                'primaryId' => $model->id,
                'changeType' => 'deleted',
            ]);
        });
    }
}
