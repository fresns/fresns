<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account;

use App\Fresns\Words\Account\DTO\WalletCheckPasswordDTO;
use App\Fresns\Words\Account\DTO\WalletDecreaseDTO;
use App\Fresns\Words\Account\DTO\WalletFreezeDTO;
use App\Fresns\Words\Account\DTO\WalletIncreaseDTO;
use App\Fresns\Words\Account\DTO\WalletRechargeDTO;
use App\Fresns\Words\Account\DTO\WalletReversalDTO;
use App\Fresns\Words\Account\DTO\WalletUnfreezeDTO;
use App\Fresns\Words\Account\DTO\WalletUpdateStateDTO;
use App\Fresns\Words\Account\DTO\WalletWithdrawDTO;
use App\Helpers\CacheHelper;
use App\Helpers\PrimaryHelper;
use App\Models\AccountWallet;
use App\Models\AccountWalletLog;
use App\Utilities\ConfigUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Facades\Hash;

class Wallet
{
    use CmdWordResponseTrait;

    // cmd word: wallet check password
    public function walletCheckPassword($wordBody)
    {
        $dtoWordBody = new WalletCheckPasswordDTO($wordBody);

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);

        $wallet = AccountWallet::where('account_id', $accountId)->isEnabled()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(34501, ConfigUtility::getCodeMessage(34501));
        }

        if (empty($wallet->password) && empty($dtoWordBody->password)) {
            return $this->success();
        }

        if ($wallet->password) {
            $checkWallet = static::checkWalletPassword($wallet, $dtoWordBody->password);
            // Account wallet password is incorrect
            if (! $checkWallet) {
                return $this->failure(34502, ConfigUtility::getCodeMessage(34502));
            }
        }

        return $this->success();
    }

    // cmd word: wallet recharge
    public function walletRecharge($wordBody)
    {
        $dtoWordBody = new WalletRechargeDTO($wordBody);

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(31502, ConfigUtility::getCodeMessage(31502));
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnabled()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(34501, ConfigUtility::getCodeMessage(34501));
        }

        $checkClosingBalance = static::checkClosingBalance($wallet);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(34506, ConfigUtility::getCodeMessage(34506));
        }

        // amount
        $amountTotal = round($dtoWordBody->amountTotal, 2);
        $systemFee = round($dtoWordBody->systemFee, 2);
        $transactionAmount = $amountTotal - $systemFee;

        $closingBalance = $wallet->balance + $transactionAmount;

        // wallet log
        $logData = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'type' => AccountWalletLog::TYPE_IN_RECHARGE,
            'app_fskey' => $dtoWordBody->transactionFskey,
            'transaction_id' => $dtoWordBody->transactionId,
            'transaction_code' => $dtoWordBody->transactionCode,
            'amount_total' => $amountTotal,
            'transaction_amount' => $transactionAmount,
            'system_fee' => $systemFee,
            'opening_balance' => $wallet->balance,
            'closing_balance' => $closingBalance,
            'state' => $dtoWordBody->immediate ? AccountWalletLog::STATE_SUCCESS : AccountWalletLog::STATE_PENDING,
            'remark' => $dtoWordBody->remark,
            'more_info' => $dtoWordBody->moreInfo,
            'success_at' => $dtoWordBody->immediate ? now() : null,
        ];

        AccountWalletLog::create($logData);

        if ($dtoWordBody->immediate) {
            static::balanceChange($wallet, 'increment', $transactionAmount);
        }

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // cmd word: wallet withdraw
    public function walletWithdraw($wordBody)
    {
        $dtoWordBody = new WalletWithdrawDTO($wordBody);

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(31502, ConfigUtility::getCodeMessage(31502));
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnabled()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(34501, ConfigUtility::getCodeMessage(34501));
        }

        if ($wallet->password) {
            $checkWallet = static::checkWalletPassword($wallet, $dtoWordBody->password);
            // Account wallet password is incorrect
            if (! $checkWallet) {
                return $this->failure(34502, ConfigUtility::getCodeMessage(34502));
            }
        }

        $checkClosingBalance = static::checkClosingBalance($wallet);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(34506, ConfigUtility::getCodeMessage(34506));
        }

        // amount
        $amountTotal = round($dtoWordBody->amountTotal, 2);
        $systemFee = round($dtoWordBody->systemFee, 2);
        $transactionAmount = $amountTotal - $systemFee;

        $closingBalance = $wallet->balance - $transactionAmount;

        $checkBalance = static::checkBalance($wallet, $amountTotal);
        // The counterparty wallet balance is not allowed to make payment
        if (! $checkBalance) {
            return $this->failure(34504, ConfigUtility::getCodeMessage(34504));
        }

        // wallet log
        $logData = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'type' => AccountWalletLog::TYPE_DE_WITHDRAW,
            'app_fskey' => $dtoWordBody->transactionFskey,
            'transaction_id' => $dtoWordBody->transactionId,
            'transaction_code' => $dtoWordBody->transactionCode,
            'amount_total' => $amountTotal,
            'transaction_amount' => $transactionAmount,
            'system_fee' => $systemFee,
            'opening_balance' => $wallet->balance,
            'closing_balance' => $closingBalance,
            'state' => $dtoWordBody->immediate ? AccountWalletLog::STATE_SUCCESS : AccountWalletLog::STATE_PENDING,
            'remark' => $dtoWordBody->remark,
            'more_info' => $dtoWordBody->moreInfo,
            'success_at' => $dtoWordBody->immediate ? now() : null,
        ];

        AccountWalletLog::create($logData);

        if ($dtoWordBody->immediate) {
            static::balanceChange($wallet, 'decrement', $amountTotal);
        }

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // cmd word: wallet update state
    public function walletUpdateState($wordBody)
    {
        $dtoWordBody = new WalletUpdateStateDTO($wordBody);

        if (empty($dtoWordBody->logId) && empty($dtoWordBody->transactionId) && empty($dtoWordBody->transactionCode)) {
            return $this->failure(21005, ConfigUtility::getCodeMessage(21005));
        }

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(31502, ConfigUtility::getCodeMessage(31502));
        }

        $walletLogQuery = AccountWalletLog::where('account_id', $accountId)->whereIn('state', [
            AccountWalletLog::STATE_PENDING,
            AccountWalletLog::STATE_PROCESSING,
        ]);

        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $walletLogQuery->when($userId, function ($query, $value) {
            $query->where('user_id', $value);
        });

        $walletLogQuery->when($dtoWordBody->logId, function ($query, $value) {
            $query->where('id', $value);
        });

        $walletLogQuery->when($dtoWordBody->transactionId, function ($query, $value) {
            $value = (int) $value;
            $query->where('transaction_id', $value);
        });

        $walletLogQuery->when($dtoWordBody->transactionCode, function ($query, $value) {
            $query->where('transaction_code', $value);
        });

        $walletLog = $walletLogQuery->first();

        if (empty($walletLog)) {
            return $this->failure(32201, ConfigUtility::getCodeMessage(32201));
        }

        if ($walletLog->state == AccountWalletLog::STATE_SUCCESS) {
            return $this->failure(32205, ConfigUtility::getCodeMessage(32205));
        }

        if (! in_array($walletLog->type, [
            AccountWalletLog::TYPE_IN_RECHARGE,
            AccountWalletLog::TYPE_DE_WITHDRAW,
        ])) {
            return $this->failure(21007, ConfigUtility::getCodeMessage(21007));
        }

        if ($dtoWordBody->updateState != AccountWalletLog::STATE_SUCCESS) {
            $walletLog->update([
                'state' => $dtoWordBody->updateState,
            ]);

            CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

            return $this->success();
        }

        $wallet = AccountWallet::where('account_id', $accountId)->first();
        if (empty($wallet)) {
            return $this->failure(34501, ConfigUtility::getCodeMessage(34501));
        }

        switch ($walletLog->type) {
            case AccountWalletLog::TYPE_IN_RECHARGE:
                $actionName = 'increment';
                $amount = $walletLog->transaction_amount;

                $closingBalance = $wallet->balance + $walletLog->transaction_amount;
                break;

            case AccountWalletLog::TYPE_DE_WITHDRAW:
                $checkBalance = static::checkBalance($wallet, $walletLog->amount_total);

                // The counterparty wallet balance is not allowed to make payment
                if (! $checkBalance) {
                    return $this->failure(34504, ConfigUtility::getCodeMessage(34504));
                }

                $actionName = 'decrement';
                $amount = $walletLog->amount_total;

                $closingBalance = $wallet->balance - $walletLog->amount_total;
                break;
        }

        $walletLog->update([
            'opening_balance' => $wallet->balance,
            'closing_balance' => $closingBalance,
            'state' => AccountWalletLog::STATE_SUCCESS,
            'success_at' => now(),
        ]);

        static::balanceChange($wallet, $actionName, $amount);

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // cmd word: wallet freeze
    public function walletFreeze($wordBody)
    {
        $dtoWordBody = new WalletFreezeDTO($wordBody);

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(31502, ConfigUtility::getCodeMessage(31502));
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnabled()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(34501, ConfigUtility::getCodeMessage(34501));
        }

        $checkClosingBalance = static::checkClosingBalance($wallet);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(34506, ConfigUtility::getCodeMessage(34506));
        }

        // amount
        $amountTotal = round($dtoWordBody->amountTotal, 2);

        $checkBalance = static::checkBalance($wallet, $amountTotal);
        // The counterparty wallet balance is not allowed to make payment
        if (! $checkBalance) {
            return $this->failure(34505, ConfigUtility::getCodeMessage(34505));
        }

        // wallet log
        $logData = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'type' => AccountWalletLog::TYPE_IN_FREEZE,
            'app_fskey' => $dtoWordBody->transactionFskey,
            'transaction_id' => $dtoWordBody->transactionId,
            'transaction_code' => $dtoWordBody->transactionCode,
            'amount_total' => $amountTotal,
            'transaction_amount' => $amountTotal,
            'system_fee' => 0.00,
            'opening_balance' => $wallet->balance,
            'closing_balance' => $wallet->balance,
            'state' => AccountWalletLog::STATE_SUCCESS,
            'remark' => $dtoWordBody->remark,
            'more_info' => $dtoWordBody->moreInfo,
            'success_at' => now(),
        ];

        AccountWalletLog::create($logData);

        $wallet->increment('freeze_amount', $amountTotal);

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // cmd word: wallet unfreeze
    public function walletUnfreeze($wordBody)
    {
        $dtoWordBody = new WalletUnfreezeDTO($wordBody);

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(31502, ConfigUtility::getCodeMessage(31502));
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnabled()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(34501, ConfigUtility::getCodeMessage(34501));
        }

        $checkClosingBalance = static::checkClosingBalance($wallet);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(34506, ConfigUtility::getCodeMessage(34506));
        }

        // amount
        $amountTotal = round($dtoWordBody->amountTotal, 2);

        // The counterparty wallet balance is not allowed to make payment
        if ($wallet->freeze_amount < $amountTotal) {
            return $this->failure(34505, ConfigUtility::getCodeMessage(34505));
        }

        // wallet log
        $logData = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'type' => AccountWalletLog::TYPE_DE_UNFREEZE,
            'app_fskey' => $dtoWordBody->transactionFskey,
            'transaction_id' => $dtoWordBody->transactionId,
            'transaction_code' => $dtoWordBody->transactionCode,
            'amount_total' => $amountTotal,
            'transaction_amount' => $amountTotal,
            'system_fee' => 0.00,
            'opening_balance' => $wallet->balance,
            'closing_balance' => $wallet->balance,
            'state' => AccountWalletLog::STATE_SUCCESS,
            'remark' => $dtoWordBody->remark,
            'more_info' => $dtoWordBody->moreInfo,
            'success_at' => now(),
        ];

        AccountWalletLog::create($logData);

        $wallet->decrement('freeze_amount', $amountTotal);

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // cmd word: wallet increase
    public function walletIncrease($wordBody)
    {
        $dtoWordBody = new WalletIncreaseDTO($wordBody);

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $originAccountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->originAid);
        $originUserId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->originUid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(31502, ConfigUtility::getCodeMessage(31502));
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnabled()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(34501, ConfigUtility::getCodeMessage(34501));
        }

        $checkClosingBalance = static::checkClosingBalance($wallet);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(34506, ConfigUtility::getCodeMessage(34506));
        }

        // amount
        $amountTotal = round($dtoWordBody->amountTotal, 2);
        $systemFee = round($dtoWordBody->systemFee, 2);
        $transactionAmount = $amountTotal - $systemFee;

        $closingBalance = $wallet->balance + $transactionAmount;

        // wallet log
        $logData = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'type' => AccountWalletLog::TYPE_IN_TRANSACTION,
            'app_fskey' => $dtoWordBody->transactionFskey,
            'transaction_id' => $dtoWordBody->transactionId,
            'transaction_code' => $dtoWordBody->transactionCode,
            'amount_total' => $amountTotal,
            'transaction_amount' => $transactionAmount,
            'system_fee' => $systemFee,
            'opening_balance' => $wallet->balance,
            'closing_balance' => $closingBalance,
            'object_account_id' => $originAccountId,
            'object_user_id' => $originUserId,
            'state' => AccountWalletLog::STATE_SUCCESS,
            'remark' => $dtoWordBody->remark,
            'more_info' => $dtoWordBody->moreInfo,
            'success_at' => now(),
        ];

        // Increase
        if (empty($originAccountId)) {
            AccountWalletLog::create($logData);
            static::balanceChange($wallet, 'increment', $transactionAmount);
        } else {
            $originWallet = AccountWallet::where('account_id', $originAccountId)->isEnabled()->first();

            // The counterparty wallet not exist or has been banned
            if (empty($originWallet)) {
                return $this->failure(34503, ConfigUtility::getCodeMessage(34503));
            }

            $checkBalance = static::checkBalance($originWallet, $amountTotal);
            // The counterparty wallet balance is not allowed to make payment
            if (! $checkBalance) {
                return $this->failure(34505, ConfigUtility::getCodeMessage(34505));
            }

            // The closing balance of the counterparty does not match with the wallet limit
            $checkOriginClosingBalance = static::checkClosingBalance($originWallet);
            if (! $checkOriginClosingBalance) {
                return $this->failure(34507, ConfigUtility::getCodeMessage(34507));
            }

            // increase
            $increaseLog = AccountWalletLog::create($logData);
            static::balanceChange($wallet, 'increment', $transactionAmount);

            $originClosingBalance = $originWallet->balance - $amountTotal;

            // origin wallet log
            $originLogData = [
                'account_id' => $originAccountId,
                'user_id' => $originUserId,
                'type' => AccountWalletLog::TYPE_DE_TRANSACTION,
                'app_fskey' => $dtoWordBody->transactionFskey,
                'transaction_id' => $dtoWordBody->transactionId,
                'transaction_code' => $dtoWordBody->transactionCode,
                'amount_total' => $amountTotal,
                'transaction_amount' => $transactionAmount,
                'system_fee' => $systemFee,
                'opening_balance' => $originWallet->balance,
                'closing_balance' => $originClosingBalance,
                'object_account_id' => $accountId,
                'object_user_id' => $userId,
                'object_wallet_log_id' => $increaseLog->id,
                'state' => AccountWalletLog::STATE_SUCCESS,
                'remark' => $dtoWordBody->remark,
                'more_info' => $dtoWordBody->moreInfo,
                'success_at' => now(),
            ];

            // decrement
            AccountWalletLog::create($originLogData);
            static::balanceChange($originWallet, 'decrement', $amountTotal);
        }

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);
        CacheHelper::forgetFresnsAccount($dtoWordBody->originAid);

        return $this->success();
    }

    // cmd word: wallet decrease
    public function walletDecrease($wordBody)
    {
        $dtoWordBody = new WalletDecreaseDTO($wordBody);

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $originAccountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->originAid);
        $originUserId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->originUid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(31502, ConfigUtility::getCodeMessage(31502));
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnabled()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(34501, ConfigUtility::getCodeMessage(34501));
        }

        if ($wallet->password) {
            $checkWallet = static::checkWalletPassword($wallet, $dtoWordBody->password);
            // Account wallet password is incorrect
            if (! $checkWallet) {
                return $this->failure(34502, ConfigUtility::getCodeMessage(34502));
            }
        }

        // amount
        $amountTotal = round($dtoWordBody->amountTotal, 2);
        $systemFee = round($dtoWordBody->systemFee, 2);
        $transactionAmount = $amountTotal - $systemFee;

        $closingBalance = $wallet->balance - $transactionAmount;

        $checkBalance = static::checkBalance($wallet, $amountTotal);
        // The counterparty wallet balance is not allowed to make payment
        if (! $checkBalance) {
            return $this->failure(34504, ConfigUtility::getCodeMessage(34504));
        }

        $checkClosingBalance = static::checkClosingBalance($wallet);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(34506, ConfigUtility::getCodeMessage(34506));
        }

        // wallet log
        $logData = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'type' => AccountWalletLog::TYPE_DE_TRANSACTION,
            'app_fskey' => $dtoWordBody->transactionFskey,
            'transaction_id' => $dtoWordBody->transactionId,
            'transaction_code' => $dtoWordBody->transactionCode,
            'amount_total' => $amountTotal,
            'transaction_amount' => $transactionAmount,
            'system_fee' => $systemFee,
            'opening_balance' => $wallet->balance,
            'closing_balance' => $closingBalance,
            'object_account_id' => $originAccountId,
            'object_user_id' => $originUserId,
            'remark' => $dtoWordBody->remark,
            'state' => AccountWalletLog::STATE_SUCCESS,
            'more_info' => $dtoWordBody->moreInfo,
            'success_at' => now(),
        ];

        // decrement
        if (empty($originAccountId)) {
            AccountWalletLog::create($logData);
            static::balanceChange($wallet, 'decrement', $amountTotal);
        } else {
            $originWallet = AccountWallet::where('account_id', $originAccountId)->isEnabled()->first();

            // The counterparty wallet not exist or has been banned
            if (empty($originWallet)) {
                return $this->failure(34503, ConfigUtility::getCodeMessage(34503));
            }

            // The closing balance of the counterparty does not match with the wallet limit
            $checkOriginClosingBalance = static::checkClosingBalance($originWallet);
            if (! $checkOriginClosingBalance) {
                return $this->failure(34507, ConfigUtility::getCodeMessage(34507));
            }

            // decrement
            $decrementLog = AccountWalletLog::create($logData);
            static::balanceChange($wallet, 'decrement', $amountTotal);

            $originClosingBalance = $originWallet->balance + $transactionAmount;

            // origin wallet log
            $originLogData = [
                'account_id' => $originAccountId,
                'user_id' => $originUserId,
                'type' => AccountWalletLog::TYPE_IN_TRANSACTION,
                'app_fskey' => $dtoWordBody->transactionFskey,
                'transaction_id' => $dtoWordBody->transactionId,
                'transaction_code' => $dtoWordBody->transactionCode,
                'amount_total' => $amountTotal,
                'transaction_amount' => $transactionAmount,
                'system_fee' => $systemFee,
                'opening_balance' => $originWallet->balance,
                'closing_balance' => $originClosingBalance,
                'object_account_id' => $accountId,
                'object_user_id' => $userId,
                'object_wallet_log_id' => $decrementLog->id,
                'state' => AccountWalletLog::STATE_SUCCESS,
                'remark' => $dtoWordBody->remark,
                'more_info' => $dtoWordBody->moreInfo,
                'success_at' => now(),
            ];

            // increment
            AccountWalletLog::create($originLogData);
            static::balanceChange($originWallet, 'increment', $transactionAmount);
        }

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);
        CacheHelper::forgetFresnsAccount($dtoWordBody->originAid);

        return $this->success();
    }

    // cmd word: wallet reversal
    public function walletReversal($wordBody)
    {
        $dtoWordBody = new WalletReversalDTO($wordBody);

        if (empty($dtoWordBody->logId) && empty($dtoWordBody->transactionId) && empty($dtoWordBody->transactionCode)) {
            return $this->failure(21005, ConfigUtility::getCodeMessage(21005));
        }

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(31502, ConfigUtility::getCodeMessage(31502));
        }

        $walletLogQuery = AccountWalletLog::where('account_id', $accountId)->where('state', AccountWalletLog::STATE_SUCCESS);

        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $walletLogQuery->when($userId, function ($query, $value) {
            $query->where('user_id', $value);
        });

        $walletLogQuery->when($dtoWordBody->logId, function ($query, $value) {
            $query->where('id', $value);
        });

        $walletLogQuery->when($dtoWordBody->transactionId, function ($query, $value) {
            $query->where('transaction_id', $value);
        });

        $walletLogQuery->when($dtoWordBody->transactionCode, function ($query, $value) {
            $query->where('transaction_code', $value);
        });

        $walletLog = $walletLogQuery->first();

        if (empty($walletLog)) {
            return $this->failure(32201, ConfigUtility::getCodeMessage(32201));
        }

        if (! in_array($walletLog->type, [
            AccountWalletLog::TYPE_IN_TRANSACTION,
            AccountWalletLog::TYPE_DE_TRANSACTION,
        ])) {
            return $this->failure(21007, ConfigUtility::getCodeMessage(21007));
        }

        $wallet = AccountWallet::where('account_id', $accountId)->first();
        if (empty($wallet)) {
            return $this->failure(34501, ConfigUtility::getCodeMessage(34501));
        }

        // object wallet and log
        $objectWalletLog = null;
        $objectWallet = null;
        if ($walletLog->object_wallet_log_id) {
            $objectWalletLog = AccountWalletLog::where('id', $walletLog->object_wallet_log_id)->first();

            $objectWallet = AccountWallet::where('account_id', $objectWalletLog?->account_id)->first();

            if ($objectWalletLog && $objectWallet && $objectWalletLog->type == AccountWalletLog::TYPE_IN_TRANSACTION) {
                $checkObjectBalance = static::checkBalance($objectWallet, $objectWalletLog->amount_total);
                // The counterparty wallet balance is not allowed to make payment
                if (! $checkObjectBalance) {
                    return $this->failure(34504, ConfigUtility::getCodeMessage(34504));
                }

                $objectWalletLog->update([
                    'state' => AccountWalletLog::STATE_REVERSED,
                ]);
            }
        }

        if ($walletLog->type == AccountWalletLog::TYPE_IN_TRANSACTION) {
            // The counterparty wallet balance is not allowed to make payment
            $checkBalance = static::checkBalance($wallet, $walletLog->amount_total);
            if (! $checkBalance) {
                return $this->failure(34504, ConfigUtility::getCodeMessage(34504));
            }
        }

        $walletLog->update([
            'state' => AccountWalletLog::STATE_REVERSED,
        ]);

        switch ($walletLog->type) {
            case AccountWalletLog::TYPE_IN_TRANSACTION:
                $wallet->decrement('balance', $walletLog->amount_total);
                $newBalance = $wallet->balance - $walletLog->amount_total;
                $reversalType = AccountWalletLog::TYPE_DE_REVERSAL;

                if ($objectWalletLog && $objectWallet) {
                    $objectWallet->increment('balance', $objectWalletLog->amount_total);
                    $objectNewBalance = $objectWallet->balance + $objectWalletLog->amount_total;

                    $originLogData = [
                        'account_id' => $objectWalletLog->account_id,
                        'user_id' => $objectWalletLog->user_id,
                        'type' => AccountWalletLog::TYPE_IN_REVERSAL,
                        'app_fskey' => $objectWalletLog->app_fskey,
                        'transaction_id' => $objectWalletLog->transaction_id,
                        'transaction_code' => $objectWalletLog->transaction_code,
                        'amount_total' => $objectWalletLog->amount_total,
                        'transaction_amount' => 0.00,
                        'system_fee' => 0.00,
                        'opening_balance' => $objectWallet->balance,
                        'closing_balance' => $objectNewBalance,
                        'object_account_id' => $objectWalletLog->object_account_id,
                        'object_user_id' => $objectWalletLog->object_user_id,
                        'object_wallet_log_id' => $objectWalletLog->id,
                        'state' => AccountWalletLog::STATE_SUCCESS,
                        'success_at' => now(),
                    ];
                    AccountWalletLog::create($originLogData);
                }
                break;

            case AccountWalletLog::TYPE_DE_TRANSACTION:
                $wallet->increment('balance', $walletLog->amount_total);
                $newBalance = $wallet->balance + $walletLog->amount_total;
                $reversalType = AccountWalletLog::TYPE_IN_REVERSAL;

                if ($objectWalletLog && $objectWallet) {
                    $objectWallet->decrement('balance', $objectWalletLog->amount_total);
                    $objectNewBalance = $objectWallet->balance - $objectWalletLog->amount_total;

                    $originLogData = [
                        'account_id' => $objectWalletLog->account_id,
                        'user_id' => $objectWalletLog->user_id,
                        'type' => AccountWalletLog::TYPE_DE_REVERSAL,
                        'app_fskey' => $objectWalletLog->app_fskey,
                        'transaction_id' => $objectWalletLog->transaction_id,
                        'transaction_code' => $objectWalletLog->transaction_code,
                        'amount_total' => $objectWalletLog->amount_total,
                        'transaction_amount' => 0.00,
                        'system_fee' => 0.00,
                        'opening_balance' => $objectWallet->balance,
                        'closing_balance' => $objectNewBalance,
                        'object_account_id' => $objectWalletLog->object_account_id,
                        'object_user_id' => $objectWalletLog->object_user_id,
                        'object_wallet_log_id' => $objectWalletLog->id,
                        'state' => AccountWalletLog::STATE_SUCCESS,
                        'success_at' => now(),
                    ];
                    AccountWalletLog::create($originLogData);
                }
                break;
        }

        // wallet log
        $logData = [
            'account_id' => $accountId,
            'user_id' => $walletLog->user_id,
            'type' => $reversalType,
            'app_fskey' => $walletLog->app_fskey,
            'transaction_id' => $walletLog->transaction_id,
            'transaction_code' => $walletLog->transaction_code,
            'amount_total' => $walletLog->amount_total,
            'transaction_amount' => 0.00,
            'system_fee' => 0.00,
            'opening_balance' => $wallet->balance,
            'closing_balance' => $newBalance,
            'object_account_id' => $walletLog->object_account_id,
            'object_user_id' => $walletLog->object_user_id,
            'object_wallet_log_id' => $walletLog->id,
            'state' => AccountWalletLog::STATE_SUCCESS,
            'success_at' => now(),
        ];
        AccountWalletLog::create($logData);

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // check wallet password
    private static function checkWalletPassword(AccountWallet $wallet, ?string $walletPassword = null): bool
    {
        if ($wallet->password && empty($walletPassword)) {
            return false;
        }

        return Hash::check($walletPassword, $wallet->password);
    }

    // check balance
    private static function checkBalance(AccountWallet $wallet, float $amount): bool
    {
        $balance = $wallet->balance - $wallet->freeze_amount;

        if ($balance < $amount) {
            return false;
        }

        return true;
    }

    // check closing balance
    private static function checkClosingBalance(AccountWallet $wallet): bool
    {
        $walletLog = AccountWalletLog::where('account_id', $wallet->account_id)
            ->where('state', AccountWalletLog::STATE_SUCCESS)
            ->whereNotNull('success_at')
            ->orderByDesc('success_at')
            ->first();

        $closingBalance = $walletLog?->closing_balance ?? 0.00;

        return $wallet->balance == $closingBalance;
    }

    // wallet balance
    private static function balanceChange(AccountWallet $wallet, string $actionType, float $transactionAmount)
    {
        if (! in_array($actionType, ['increment', 'decrement'])) {
            return;
        }

        $wallet->$actionType('balance', $transactionAmount);
    }

    // wallet freeze amount
    private static function freezeAmountChange(AccountWallet $wallet, string $actionType, float $transactionAmount)
    {
        if (! in_array($actionType, ['increment', 'decrement'])) {
            return;
        }

        $wallet->$actionType('freeze_amount', $transactionAmount);
    }
}
