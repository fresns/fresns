<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\CacheHelper;
use App\Helpers\DateHelper;
use App\Helpers\InteractionHelper;
use App\Models\Account;
use App\Models\File;

class AccountService
{
    public function accountData(?Account $account, string $langTag, ?string $timezone = null)
    {
        if (! $account) {
            return null;
        }

        $cacheKey = "fresns_api_account_{$account->aid}_{$langTag}";
        $cacheTag = 'fresnsAccounts';

        $accountInfo = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($accountInfo)) {
            $accountData = $account->getAccountInfo();

            $item['connects'] = $account->getAccountConnects();
            $item['wallet'] = $account->getAccountWallet($langTag);

            $userService = new UserService;
            $userList = [];
            foreach ($account->users as $user) {
                $userList[] = $userService->userData($user, 'list', $langTag, $timezone);
            }

            $item['users'] = $userList;
            $item['interaction'] = InteractionHelper::fresnsUserInteraction($langTag);

            $accountInfo = array_merge($accountData, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($accountInfo, $cacheKey, $cacheTag, null, $cacheTime);
        }

        return self::handleAccountDate($accountInfo, $timezone, $langTag);
    }

    // handle account data date
    public static function handleAccountDate(?array $accountData, ?string $timezone = null, ?string $langTag = null)
    {
        if (empty($accountData)) {
            return $accountData;
        }

        $accountData['verifyDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['verifyDateTime'], $timezone, $langTag);
        $accountData['registerDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['registerDateTime'], $timezone, $langTag);
        $accountData['waitDeleteDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['waitDeleteDateTime'], $timezone, $langTag);

        return $accountData;
    }
}
