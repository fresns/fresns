<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Models\Account;
use App\Models\AccountConnect;
use App\Models\App;
use App\Models\AppBadge;
use App\Models\AppUsage;
use App\Models\City;
use App\Models\CodeMessage;
use App\Models\Config;
use App\Models\LanguagePack;
use App\Models\Seo;
use App\Models\SessionKey;
use App\Models\SessionLog;
use App\Models\SessionToken;
use App\Models\Sticker;
use App\Models\TempCallbackContent;
use App\Models\TempVerifyCode;
use App\Models\User;
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
            SessionKey::class,
            SessionToken::class,
            SessionLog::class,
            App::class,
            AppBadge::class,
            AppUsage::class,
            Sticker::class,
            City::class,
            Seo::class,
            TempVerifyCode::class,
            TempCallbackContent::class,
            AccountConnect::class,
            UserStat::class,
        ];

        if (in_array(static::class, $excludedClasses)) {
            return;
        }

        static::created(function ($model) {
            SubscribeUtility::notifyDataChange(static::class, $model->id, SubscribeUtility::CHANGE_TYPE_CREATED);
        });

        static::updated(function ($model) {
            if (($model instanceof Account || $model instanceof User) && ($model->isDirty('last_login_at') || $model->isDirty('last_activity_at'))) {
                return;
            }

            SubscribeUtility::notifyDataChange(static::class, $model->id, SubscribeUtility::CHANGE_TYPE_UPDATED);
        });

        static::deleted(function ($model) {
            SubscribeUtility::notifyDataChange(static::class, $model->id, SubscribeUtility::CHANGE_TYPE_DELETED);
        });
    }
}
