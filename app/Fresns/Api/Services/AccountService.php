<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Services;

use App\Helpers\CacheHelper;
use App\Helpers\DateHelper;
use App\Helpers\InteractionHelper;
use App\Models\Account;
use App\Models\File;
use App\Models\PluginUsage;
use App\Models\SessionLog;
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

        $accountInfo = Cache::get($cacheKey);

        if (empty($accountInfo)) {
            $accountData = $account->getAccountInfo();

            $item['connects'] = $account->getAccountConnects();
            $item['wallet'] = $account->getAccountWallet($langTag);

            $userService = new UserService;
            $userList = [];
            foreach ($account->users as $user) {
                $userList[] = $userService->userData($user, $langTag, $timezone);
            }

            $item['users'] = $userList;
            $item['interaction'] = InteractionHelper::fresnsUserInteraction($langTag);

            $accountInfo = array_merge($accountData, $item);

            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);
            CacheHelper::put($accountInfo, $cacheKey, ['fresnsAccounts', 'fresnsAccountData'], null, $cacheTime);
        }

        return self::handleAccountDate($accountInfo, $timezone, $langTag);
    }

    public function accountData(Account $account, string $langTag, string $timezone)
    {
        $data['items'] = [
            'walletRecharges' => ExtendUtility::getExtendsByEveryone(PluginUsage::TYPE_WALLET_RECHARGE, null, null, $langTag),
            'walletWithdraws' => ExtendUtility::getExtendsByEveryone(PluginUsage::TYPE_WALLET_WITHDRAW, null, null, $langTag),
        ];

        $service = new AccountService();
        $data['detail'] = $service->accountDetail($account, $langTag, $timezone);

        return $data;
    }

    // handle account data date
    public static function handleAccountDate(?array $accountData, string $timezone, string $langTag)
    {
        if (empty($accountData)) {
            return $accountData;
        }

        $accountData['verifyDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['verifyDateTime'], $timezone, $langTag);
        $accountData['registerDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['registerDateTime'], $timezone, $langTag);
        $accountData['waitDeleteDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['waitDeleteDateTime'], $timezone, $langTag);
        $accountData['deactivateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['deactivateTime'], $timezone, $langTag);

        return $accountData;
    }

    // register account
    public static function registerAccount(array $sessionLog, array $addAccountWordBody, array $addUserWordBody)
    {
        $response['account'] = null;
        $response['user'] = null;

        // add account
        $addAccountResp = \FresnsCmdWord::plugin('Fresns')->addAccount($addAccountWordBody);

        if ($addAccountResp->isErrorResponse()) {
            // upload session log
            $sessionLog['objectAction'] = 'addAccount / '.$addAccountWordBody['account'];
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            $response['account'] = $addAccountResp->errorResponse();

            return $response;
        }

        // upload session log
        $sessionLog['aid'] = $addAccountResp->getData('aid');
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        $response['account'] = [
            'code' => $addAccountResp->getCode(),
            'message' => $addAccountResp->getMessage(),
            'data' => $addAccountResp->getData(),
        ];

        $addUserWordBody['aid'] = $addAccountResp->getData('aid');

        // add user
        $addUserResp = \FresnsCmdWord::plugin('Fresns')->addUser($addUserWordBody);

        if ($addUserResp->isErrorResponse()) {
            // upload session log
            $sessionLog['type'] = SessionLog::TYPE_USER_ADD;
            $sessionLog['objectAction'] = 'addUser';
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            $sessionLog['aid'] = $addAccountResp->getData('aid');
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            $response['account'] = $addAccountResp->errorResponse();
            $response['user'] = $addUserResp->errorResponse();

            return $response;
        }

        // upload session log
        $sessionLog['type'] = SessionLog::TYPE_USER_ADD;
        $sessionLog['aid'] = $addAccountResp->getData('aid');
        $sessionLog['uid'] = $addUserResp->getData('uid');
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        $response['user'] = [
            'code' => $addUserResp->getCode(),
            'message' => $addUserResp->getMessage(),
            'data' => $addUserResp->getData(),
        ];

        return $response;
    }
}
