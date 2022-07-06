<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Traits;

use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Models\Account;
use App\Models\User;
use App\Utilities\PermissionUtility;
use Illuminate\Support\Facades\Cache;

trait ApiHeaderTrait
{
    // platformId
    public function platformId(): int
    {
        return \request()->header('platformId');
    }

    // version
    public function version(): string
    {
        return \request()->header('version');
    }

    // appId
    public function appId(): string
    {
        return \request()->header('appId');
    }

    // langTag
    public function langTag(): string
    {
        $defaultLanguage = ConfigHelper::fresnsConfigDefaultLangTag();

        return \request()->header('langTag', $defaultLanguage);
    }

    // timezone
    public function timezone(): string
    {
        $defaultTimezone = ConfigHelper::fresnsConfigDefaultTimezone();

        return \request()->header('timezone', $defaultTimezone);
    }

    // deviceInfo
    public function deviceInfo(): array
    {
        return \request()->header('deviceInfo');
    }

    // auth account
    public function account(): ?Account
    {
        $aid = \request()->header('aid');

        if (empty($aid)) {
            return null;
        }

        $langTag = $this->langTag();

        $cacheKey = 'fresns_api_auth_account_'.$aid.'_'.$langTag;
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $authAccount = Cache::remember($cacheKey, $cacheTime, function () use ($aid) {
            return Account::withTrashed()->where('aid', $aid)->first();
        });

        if (is_null($authAccount)) {
            Cache::forget($cacheKey);
        }

        return $authAccount;
    }

    // auth user
    public function user(): ?User
    {
        $uid = \request()->header('uid');

        if (empty($uid)) {
            return null;
        }

        $langTag = $this->langTag();

        $cacheKey = 'fresns_api_auth_user_'.$uid.'_'.$langTag;
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $authUser = Cache::remember($cacheKey, $cacheTime, function () use ($uid) {
            return User::withTrashed()->where('uid', $uid)->first();
        });

        if (is_null($authUser)) {
            Cache::forget($cacheKey);
        }

        return $authUser;
    }

    // auth user expire info
    public function userExpireInfo(): array
    {
        $authUser = $this->user();

        if (empty($authUser)) {
            $cacheKey = 'fresns_api_guest_expire_info';
        } else {
            $cacheKey = 'fresns_api_user_'.$authUser->uid.'_expire_info';
        }

        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $expireInfo = Cache::remember($cacheKey, $cacheTime, function () use ($authUser) {
            return PermissionUtility::checkUserStatusOfSiteMode($authUser?->id);
        });

        if (is_null($expireInfo)) {
            Cache::forget($cacheKey);
        }

        return $expireInfo;
    }

    // user content view perm permission
    public function userContentViewPerm(): array
    {
        $authUser = $this->user();

        $cacheKey = 'fresns_api_user_'.$authUser->uid.'_content_view_perm';
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $config = Cache::remember($cacheKey, $cacheTime, function () use ($authUser) {
            return PermissionUtility::getUserContentViewPerm($authUser->id);
        });

        if (is_null($config)) {
            Cache::forget($cacheKey);
        }

        return $config;
    }
}
