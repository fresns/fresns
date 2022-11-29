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
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);

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
        $cacheKey = "fresns_api_account_wallet_extends_{$account->aid}_{$langTag}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_IMAGE);

        $items = Cache::remember($cacheKey, $cacheTime, function () use ($account, $langTag) {
            $item['walletRecharges'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_WALLET_RECHARGE, null, null, $account->id, $langTag);
            $item['walletWithdraws'] = ExtendUtility::getPluginUsages(PluginUsage::TYPE_WALLET_WITHDRAW, null, null, $account->id, $langTag);

            return $item;
        });

        $data['items'] = $items;

        $service = new AccountService();
        $data['detail'] = $service->accountDetail($account, $langTag, $timezone);

        return $data;
    }

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
