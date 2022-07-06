<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Subscribe;

use Illuminate\Database\Eloquent\Model;

trait SubscribeNotifyTrait
{
    public function qualifyTableName($model)
    {
        $modelName = $model;

        if (class_exists($model)) {
            $model = new $model;

            if (! ($model instanceof Model)) {
                throw new \LogicException("unknown table name of $model");
            }

            $modelName = $model->getTable();
        }

        return str_replace(config('database.connections.mysql.prefix'), '', $modelName);
    }

    public function notifyDataChange(array $data)
    {
        event('fresns.data.change', (object) $data);
    }

    public function notifyUserActivate()
    {
        $eventData = [
            'uri' => sprintf('/%s', ltrim(\request()->getRequestUri(), '/')),
            'header' => \request()->headers->all(),
            'body' => \request()->all(),
        ];

        event('fresns.user.activate', (object) $eventData);
    }
}
