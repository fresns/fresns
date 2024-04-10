<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Models\AccountConnect;
use App\Models\App;
use App\Models\AppBadge;
use App\Models\AppUsage;
use App\Models\CodeMessage;
use App\Models\Config;
use App\Models\LanguagePack;
use App\Models\Seo;
use App\Models\Sticker;
use App\Models\TempCallbackContent;
use App\Models\TempVerifyCode;
use App\Models\UserStat;
use App\Utilities\SubscribeUtility;

trait DataChangeNotifyTrait
{
    public static function bootDataChangeNotifyTrait(): void
    {
        $excludedClasses = [
            Config::class,
            CodeMessage::class,
            LanguagePack::class,
            Sticker::class,
            App::class,
            AppBadge::class,
            AppUsage::class,
            AccountConnect::class,
            UserStat::class,
            Seo::class,
            TempVerifyCode::class,
            TempCallbackContent::class,
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
