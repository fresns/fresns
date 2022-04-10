<?php

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
