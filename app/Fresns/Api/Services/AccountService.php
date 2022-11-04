<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\CacheHelper;
use App\Helpers\InteractiveHelper;
use App\Models\Account;
use App\Models\PluginUsage;
use App\Utilities\ExtendUtility;
use Illuminate\Support\Facades\Cache;

class AccountService
{
    public function accountDetail(?Account $account, string $langTag, string $timezone)
    {
        if (! $account) {
            return null;
        }

        $cacheKey = "fresns_api_account_{$account->aid}_{$langTag}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $accountInfo = Cache::remember($cacheKey, $cacheTime, function () use ($account, $langTag, $timezone) {
            $accountInfo = $account->getAccountInfo($langTag, $timezone);

            $item['connects'] = $account->getAccountConnects();
            $item['wallet'] = $account->getAccountWallet($langTag);

            return array_merge($accountInfo, $item);
        });

        $userService = new UserService;

        $userList = [];
        foreach ($account->users as $user) {
            $userList[] = $userService->userData($user, $langTag, $timezone);
        }

        $item['users'] = $userList;
        $item['interactive'] = InteractiveHelper::fresnsUserInteractive($langTag);

        $data = array_merge($accountInfo, $item);

        return $data;
    }

    public function accountData(Account $account, string $langTag, string $timezone)
    {
        $item['walletRecharges'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_WALLET_RECHARGE, null, null, $account->id, $langTag);
        $item['walletWithdraws'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_WALLET_WITHDRAW, null, null, $account->id, $langTag);
        $data['items'] = $item;

        $service = new AccountService();
        $data['detail'] = $service->accountDetail($account, $langTag, $timezone);

        return $data;
    }
}
