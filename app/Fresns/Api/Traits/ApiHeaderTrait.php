<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Account;
use App\Models\User;

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
        return json_decode(\request()->header('deviceInfo'), true) ?? [];
    }

    // auth account
    public function account(): ?Account
    {
        $aid = \request()->header('aid');

        if (empty($aid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('account', $aid);
    }

    // auth user
    public function user(): ?User
    {
        $uid = \request()->header('uid');

        if (empty($uid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('user', $uid);
    }
}
