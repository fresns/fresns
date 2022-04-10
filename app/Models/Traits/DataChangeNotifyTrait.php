<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

trait DataChangeNotifyTrait
{
    public static function bootDataChangeNotifyTrait()
    {
        static::created(function ($model) {
            \FresnsCmdWord::plugin('Fresns')->notifyDataChange(notifyDataChange([
                'tableName' => static::class,
                'primaryId' => $model->id,
                'changeType' => 'created',
            ]));
        });

        static::deleted(function ($model) {
            \FresnsCmdWord::plugin('Fresns')->notifyDataChange(notifyDataChange([
                'tableName' => static::class,
                'primaryId' => $model->id,
                'changeType' => 'deleted',
            ]));
        });
    }
}
