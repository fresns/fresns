<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Service;

use App\Helpers\InteractiveHelper;
use App\Models\Account;
use App\Models\User;

class AccountService
{
    public function getAccountDetail($accountId, $langTag, $timezone)
    {
        $account = Account::withTrashed()->find($accountId);

        $userArr = User::where('account_id', $accountId)->get();
        $userList = [];
        foreach ($userArr as $user) {
            $userProfile = $user->getUserProfile($timezone);
            $userMainRole = $user->getUserMainRole($timezone, $langTag);
            $userList[] = array_merge($userProfile, $userMainRole);
        }

        $accountInfo = $account->getAccountInfo($timezone);
        $item['connects'] = $account->getAccountConnects();
        $item['wallet'] = $account->getAccountWallet($langTag);
        $item['users'] = $userList;
        $userInteractive = InteractiveHelper::fresnsUserInteractive($langTag);

        $detail = array_merge($accountInfo, $item, $userInteractive);

        return $detail;
    }
}
