<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Wallet;

use App\Fresns\Words\Wallet\DTO\WalletDecrease;
use App\Fresns\Words\Wallet\DTO\WalletIncrease;
use App\Helpers\PrimaryHelper;
use App\Models\Account;
use App\Models\AccountWallet;
use App\Models\AccountWalletLog;
use App\Models\User;

class Wallet
{
    /**
     * @param  WalletIncrease  $wordBody
     * @return array
     */
    public function walletIncrease(WalletIncrease $wordBody)
    {
        $accountId = PrimaryHelper::fresnsAccountIdByAid($wordBody->aid);
        $userId = null;
        if (isset($wordBody->uid)) {
            $userId = PrimaryHelper::fresnsUserIdByUid($wordBody->uid);
        }
        if (empty($accountId) || (isset($userId) && empty($userId))) {
            return ['msg' => 'error'];
        }

        if (empty($wordBody->originAid)) {
            $result = $this->emptyOriginAidIncrease($wordBody, $accountId, $userId);
        } else {
            $result = $this->existOriginAidIncrease($wordBody, $accountId, $userId);
        }

        return $result;
    }

    /**
     * @param  WalletDecrease  $wordBody
     * @return array
     */
    public function walletDecrease(WalletDecrease $wordBody)
    {
        $accountId = PrimaryHelper::fresnsAccountIdByAid($wordBody->aid);
        $userId = null;
        if (isset($wordBody->uid)) {
            $userId = PrimaryHelper::fresnsUserIdByUid($wordBody->uid);
        }
        if (empty($accountId) || (isset($userId) && empty($userId))) {
            return ['msg' => 'error'];
        }
        if (empty($wordBody->originAid)) {
            $result = $this->emptyOriginAidDecrease($wordBody, $accountId, $userId);
        } else {
            $result = $this->existOriginAidDecrease($wordBody, $accountId, $userId);
        }

        return $result;
    }

    /**
     * @param $accountId
     * @return array
     */
    protected function verifyWalletBalance($accountId)
    {
        $balance = AccountWallet::where(['account_id' => $accountId, 'is_enable' => 1])->value('balance');
        $closingBalance = AccountWalletLog::where(['account_id' => $accountId, 'is_enable' => 1])->orderByDesc('id')->value('closing_balance');
        if ($closingBalance === null) {
            $closingBalance = 0;
        }
        if ($balance != $closingBalance || $balance === null) {
            return [];
        }

        return ['balance' => $balance, 'closingBalance' => $closingBalance];
    }

    /**
     * @param $type
     * @return int
     */
    protected function walletLogObjType($type)
    {
        switch ($type) {
            case 1:
                $decreaseType = 4;
                break;
            case 2:
                $decreaseType = 5;
                break;
            case 3:
                $decreaseType = 6;
                break;
            case 4:
                $decreaseType = 1;
                break;
            case 5:
                $decreaseType = 2;
                break;
            case 6:
                $decreaseType = 3;
                break;
        }

        return $decreaseType;
    }

    /**
     * @param $wordBody
     * @param $accountId
     * @param $userId
     * @return array
     */
    protected function emptyOriginAidIncrease($wordBody, $accountId, $userId)
    {
        $verifyWalletBalance = $this->verifyWalletBalance($accountId);
        if (empty($verifyWalletBalance)) {
            return ['code' => 500, 'msg' => 'Balance Error'];
        }
        $objectType = $wordBody->type;
        $addAccountWallet = $this->AddAccountWallet($wordBody, $verifyWalletBalance['balance'], $objectType, $accountId, $userId);
        $userWalletsArr = [
            'balance' => $verifyWalletBalance['balance'] + $wordBody->transactionAmount,
        ];
        AccountWallet::where('account_id', $accountId)->update($userWalletsArr);

        return ['code' => 200, 'msg' => 'success'];
    }

    /**
     * @param $wordBody
     * @param $accountId
     * @param $userId
     * @return array
     */
    protected function emptyOriginAidDecrease($wordBody, $accountId, $userId)
    {
        $verifyWalletBalance = $this->verifyWalletBalance($accountId);
        if (empty($verifyWalletBalance)) {
            return ['code' => 500, 'msg' => 'Balance Error'];
        }
        $objectType = $this->walletLogObjType($wordBody->type);
        $addAccountWallet = $this->reduceAccountWallet($wordBody, $verifyWalletBalance['balance'], $objectType, $accountId, $userId);
        $userWalletsArr = [
            'balance' => $verifyWalletBalance['balance'] - $wordBody->amount,
        ];
        AccountWallet::where('account_id', $accountId)->update($userWalletsArr);

        return ['code' => 200, 'msg' => 'success'];
    }

    /**
     * @param $wordBody
     * @param $accountId
     * @param $userId
     * @return array
     */
    protected function existOriginAidIncrease($wordBody, $accountId, $userId)
    {
        if (isset($wordBody->originUid)) {
            $originUserId = User::where('uid', $wordBody->originUid)->value('id');
        }
        $originUserId = null;
        $originAccountId = Account::where('aid', $wordBody->originAid)->value('id');
        $verifyWalletBalance = $this->verifyWalletBalance($accountId);
        if (empty($verifyWalletBalance)) {
            return ['code' => 500, 'msg' => 'Balance Error'];
        }
        $verifyOriginWalletBalance = $this->verifyOriginWalletBalance($originAccountId, $wordBody->amount);
        if (empty($verifyOriginWalletBalance)) {
            return ['code' => 500, 'msg' => 'OriginAig Balance Error'];
        }
        if (empty($originAccountId) || (isset($originUserId) && empty($originUserId))) {
            return ['code' => 500, 'msg' => 'OriginUser or OriginAccount is not exist'];
        }
        $objectType = $this->walletLogObjType($wordBody->type);
        $reduceOriginAccountWallet = $this->AddAccountWallet($wordBody, $verifyOriginWalletBalance['balance'], $objectType, $originAccountId, $accountId, $originUserId, $userId);
        $addAccountWallet = $this->reduceAccountWallet($wordBody, $verifyWalletBalance['balance'], $wordBody->type, $accountId, $userId, $originAccountId, $originUserId);
        $userWalletArr = ['balance' => $verifyWalletBalance['balance'] + $wordBody->transactionAmount];
        AccountWallet::where('account_id', $accountId)->update($userWalletArr);
        $OriginWallet = ['balance' => $verifyOriginWalletBalance['balance'] - $wordBody->amount];
        AccountWallet::where('account_id', $originAccountId)->update($OriginWallet);

        return ['code' => 200, 'msg' => 'success'];
    }

    /**
     * @param $wordBody
     * @param $accountId
     * @param $userId
     * @return array
     */
    protected function existOriginAidDecrease($wordBody, $accountId, $userId)
    {
        if (isset($wordBody->originUid)) {
            $originUserId = User::where('uid', $wordBody->originUid)->value('id');
        }
        $originUserId = null;
        $originAccountId = Account::where('aid', $wordBody->originAid)->value('id');
        //校验收款方余额
        $ReceivingBalance = $this->verifyWalletBalance($originAccountId);
        if (empty($ReceivingBalance)) {
            return ['code' => 500, 'msg' => 'Balance Error'];
        }
        //验证支出方余额
        $WalletBalance = $this->verifyOriginWalletBalance($accountId, $wordBody->amount);
        if (empty($WalletBalance)) {
            return ['code' => 500, 'msg' => 'OriginAig Balance Error'];
        }
        if (empty($originAccountId) || (isset($originUserId) && empty($originUserId))) {
            return ['code' => 500, 'msg' => 'OriginUser or OriginAccount is not exist'];
        }
        $objectType = $this->walletLogObjType($wordBody->type);
        $reduceOriginAccountWallet = $this->reduceAccountWallet($wordBody, $WalletBalance['balance'], $objectType, $originAccountId, $accountId, $originUserId, $userId);
        $addAccountWallet = $this->AddAccountWallet($wordBody, $ReceivingBalance['balance'], $wordBody->type, $originAccountId, $originUserId, $accountId, $userId);
        $userWalletArr = ['balance' => $ReceivingBalance['balance'] + $wordBody->transactionAmount];
        AccountWallet::where('account_id', $originAccountId)->update($userWalletArr);
        $OriginWallet = ['balance' => $WalletBalance['balance'] - $wordBody->amount];
        AccountWallet::where('account_id', $accountId)->update($OriginWallet);

        return ['code' => 200, 'msg' => 'success'];
    }

    /**
     * @param $accountId
     * @param $amount
     * @return array
     */
    protected function verifyOriginWalletBalance($accountId, $amount)
    {
        $balance = AccountWallet::where(['account_id' => $accountId, 'is_enable' => 1])->value('balance');

        if ($balance >= $amount) {
            return ['balance' => $balance];
        }

        return [];
    }

    /**
     * @param $wordBody
     * @param $balance
     * @param $objectType
     * @param $accountId
     * @param  null  $userId
     * @param  null  $originAccountId
     * @param  null  $originUserId
     * @return bool
     */
    protected function AddAccountWallet($wordBody, $balance, $objectType, $accountId, $userId = null, $originAccountId = null, $originUserId = null)
    {
        $walletArr = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'object_type' => $objectType,
            'amount' => $wordBody->amount,
            'transaction_amount' => $wordBody->transactionAmount,
            'system_fee' => $wordBody->systemFee,
            'object_account_id' => $originAccountId,
            'object_user_id' => $originUserId,
            'object_name' => $wordBody->originName ?? null,
            'object_id' => $wordBody->object_id ?? null,
            'opening_balance' => $balance,
            'closing_balance' => $balance + $wordBody->transactionAmount,
        ];

        AccountWalletLog::insert($walletArr);

        return true;
    }

    /**
     * @param $wordBody
     * @param $balance
     * @param $objectType
     * @param $accountId
     * @param  null  $originAccountId
     * @param  null  $userId
     * @param  null  $originUserId
     * @return bool
     */
    protected function reduceAccountWallet($wordBody, $balance, $objectType, $accountId, $originAccountId = null, $userId = null, $originUserId = null)
    {
        $input = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'object_type' => $objectType,
            'amount' => $wordBody->amount,
            'transaction_amount' => $wordBody->transactionAmount,
            'system_fee' => $wordBody->systemFee,
            'object_account_id' => $originAccountId,
            'object_user_id' => $originUserId,
            'object_name' => $wordBody->originName ?? null,
            'object_id' => $wordBody->object_id ?? null,
            'opening_balance' => $balance,
            'closing_balance' => $balance - $wordBody->amount,
        ];

        AccountWalletLog::insert($input);

        return true;
    }
}
