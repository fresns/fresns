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
        return \request()->header('X-Fresns-Client-Platform-Id');
    }

    // version
    public function version(): string
    {
        return \request()->header('X-Fresns-Client-Version');
    }

    // appId
    public function appId(): string
    {
        return \request()->header('X-Fresns-App-Id');
    }

    // langTag
    public function langTag(): string
    {
        $defaultLanguage = ConfigHelper::fresnsConfigDefaultLangTag();

        return \request()->header('X-Fresns-Client-Lang-Tag', $defaultLanguage);
    }

    // timezone
    public function timezone(): string
    {
        $uid = \request()->header('X-Fresns-Uid');
        $defaultTimezone = ConfigHelper::fresnsConfigDefaultTimezone();

        if (empty($uid)) {
            return \request()->header('X-Fresns-Client-Timezone', $defaultTimezone);
        }

        $authUser = $this->user();

        return \request()->header('X-Fresns-Client-Timezone', $authUser?->timezone) ?? $defaultTimezone;
    }

    // deviceInfo
    public function deviceInfo(): array
    {
        return json_decode(\request()->header('X-Fresns-Client-Device-Info'), true) ?? [];
    }

    // auth account
    public function account(): ?Account
    {
        $aid = \request()->header('X-Fresns-Aid');

        if (empty($aid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('account', $aid);
    }

    // auth user
    public function user(): ?User
    {
        $uid = \request()->header('X-Fresns-Uid');

        if (empty($uid)) {
            return null;
        }

        return PrimaryHelper::fresnsModelByFsid('user', $uid);
    }
}
