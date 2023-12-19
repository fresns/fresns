<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Models\CodeMessage;
use App\Models\Config;
use App\Models\LanguagePack;
use App\Models\Plugin;
use App\Models\Seo;
use App\Models\Sticker;
use App\Models\Theme;
use App\Models\UserStat;
use App\Models\VerifyCode;
use App\Utilities\SubscribeUtility;

trait DataChangeNotifyTrait
{
    public static function bootDataChangeNotifyTrait(): void
    {
        $excludedClasses = [
            Config::class,
            CodeMessage::class,
            LanguagePack::class,
            Plugin::class,
            Theme::class,
            Sticker::class,
            UserStat::class,
            Seo::class,
            VerifyCode::class,
        ];

        if (in_array(static::class, $excludedClasses)) {
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
