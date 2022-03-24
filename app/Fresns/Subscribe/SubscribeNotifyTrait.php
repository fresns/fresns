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

        return str_replace(env('DB_PREFIX', null), '', $modelName);
    }

    public function notifyDataChange(array $data)
    {
        event('fresns.data.change', (object) $data);
    }

    public function notifyUserActivate(array $data)
    {
        $defaultUserActivateEventData = [
            'uri' => sprintf('/%s', ltrim(\request()->route()->uri, '/')),
            'body' => \request()->all(),
        ];

        $eventData = array_merge($defaultUserActivateEventData, $data);

        event('fresns.user.activate', (object) $eventData);
    }
}
