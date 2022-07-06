<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\InteractiveHelper;
use App\Models\Account;
use App\Models\PluginUsage;
use App\Utilities\ExtendUtility;

class AccountService
{
    public function accountDetail(Account $account, string $langTag, string $timezone)
    {
        $userArr = $account->users;
        $userList = null;
        foreach ($userArr as $user) {
            $userProfile = $user->getUserProfile($langTag, $timezone);
            $userMainRole = $user->getUserMainRole($langTag, $timezone);
            $userStats['stats'] = $user->getUserStats($langTag);

            $userList[] = array_merge($userProfile, $userMainRole, $userStats);
        }

        $accountInfo = $account->getAccountInfo($langTag, $timezone);

        $item['connects'] = $account->getAccountConnects();
        $item['wallet'] = $account->getAccountWallet($langTag);
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
