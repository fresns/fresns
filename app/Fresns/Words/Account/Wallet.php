<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account;

use App\Fresns\Words\Account\DTO\WalletDecreaseDTO;
use App\Fresns\Words\Account\DTO\WalletFreezeDTO;
use App\Fresns\Words\Account\DTO\WalletIncreaseDTO;
use App\Fresns\Words\Account\DTO\WalletRechargeDTO;
use App\Fresns\Words\Account\DTO\WalletRevokeDTO;
use App\Fresns\Words\Account\DTO\WalletUnfreezeDTO;
use App\Fresns\Words\Account\DTO\WalletWithdrawDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\AccountWallet;
use App\Models\AccountWalletLog;
use App\Utilities\ConfigUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Facades\Hash;

class Wallet
{
    use CmdWordResponseTrait;

    // wallet recharge
    public function walletRecharge($wordBody)
    {
        $dtoWordBody = new WalletRechargeDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnable()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(
                34501,
                ConfigUtility::getCodeMessage(34501, 'Fresns', $langTag)
            );
        }

        $checkClosingBalance = static::checkClosingBalance($wallet, $accountId, $userId);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(
                34506,
                ConfigUtility::getCodeMessage(34506, 'Fresns', $langTag)
            );
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
            'plugin_unikey' => $dtoWordBody->transactionUnikey,
            'transaction_id' => $dtoWordBody->transactionId,
            'transaction_code' => $dtoWordBody->transactionCode,
            'amount_total' => $amountTotal,
            'transaction_amount' => $transactionAmount,
            'system_fee' => $systemFee,
            'opening_balance' => $wallet->balance,
            'closing_balance' => $closingBalance,
            'remark' => $dtoWordBody->remark,
            'more_json' => $dtoWordBody->moreJson,
        ];

        AccountWalletLog::create($logData);

        static::balanceChange($wallet, 'increment', $transactionAmount);

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // wallet withdraw
    public function walletWithdraw($wordBody)
    {
        $dtoWordBody = new WalletWithdrawDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnable()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(
                34501,
                ConfigUtility::getCodeMessage(34501, 'Fresns', $langTag)
            );
        }

        if ($wallet->password) {
            $checkWallet = static::checkWalletPassword($wallet, $dtoWordBody->password);
            // Account wallet password is incorrect
            if (! $checkWallet) {
                return $this->failure(
                    34502,
                    ConfigUtility::getCodeMessage(34502, 'Fresns', $langTag)
                );
            }
        }

        $checkClosingBalance = static::checkClosingBalance($wallet, $accountId, $userId);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(
                34506,
                ConfigUtility::getCodeMessage(34506, 'Fresns', $langTag)
            );
        }

        // amount
        $amountTotal = round($dtoWordBody->amountTotal, 2);
        $systemFee = round($dtoWordBody->systemFee, 2);
        $transactionAmount = $amountTotal - $systemFee;

        $closingBalance = $wallet->balance - $transactionAmount;

        $checkBalance = static::checkBalance($wallet, $amountTotal);
        // The counterparty wallet balance is not allowed to make payment
        if (! $checkBalance) {
            return $this->failure(
                34504,
                ConfigUtility::getCodeMessage(34504, 'Fresns', $langTag)
            );
        }

        // wallet log
        $logData = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'type' => AccountWalletLog::TYPE_DE_WITHDRAW,
            'plugin_unikey' => $dtoWordBody->transactionUnikey,
            'transaction_id' => $dtoWordBody->transactionId,
            'transaction_code' => $dtoWordBody->transactionCode,
            'amount_total' => $amountTotal,
            'transaction_amount' => $transactionAmount,
            'system_fee' => $systemFee,
            'opening_balance' => $wallet->balance,
            'closing_balance' => $closingBalance,
            'remark' => $dtoWordBody->remark,
            'more_json' => $dtoWordBody->moreJson,
        ];

        AccountWalletLog::create($logData);
        static::balanceChange($wallet, 'decrement', $amountTotal);

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // wallet freeze
    public function walletFreeze($wordBody)
    {
        $dtoWordBody = new WalletFreezeDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnable()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(
                34501,
                ConfigUtility::getCodeMessage(34501, 'Fresns', $langTag)
            );
        }

        $checkClosingBalance = static::checkClosingBalance($wallet, $accountId, $userId);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(
                34506,
                ConfigUtility::getCodeMessage(34506, 'Fresns', $langTag)
            );
        }

        // amount
        $amountTotal = round($dtoWordBody->amountTotal, 2);

        $checkBalance = static::checkBalance($wallet, $amountTotal);
        // The counterparty wallet balance is not allowed to make payment
        if (! $checkBalance) {
            return $this->failure(
                34505,
                ConfigUtility::getCodeMessage(34505, 'Fresns', $langTag)
            );
        }

        // wallet log
        $logData = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'type' => AccountWalletLog::TYPE_IN_FREEZE,
            'plugin_unikey' => $dtoWordBody->transactionUnikey,
            'transaction_id' => $dtoWordBody->transactionId,
            'transaction_code' => $dtoWordBody->transactionCode,
            'amount_total' => $amountTotal,
            'transaction_amount' => $amountTotal,
            'system_fee' => 0.00,
            'opening_balance' => $wallet->balance,
            'closing_balance' => $wallet->balance,
            'remark' => $dtoWordBody->remark,
            'more_json' => $dtoWordBody->moreJson,
        ];

        AccountWalletLog::create($logData);

        $wallet->increment('freeze_amount', $amountTotal);

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // wallet unfreeze
    public function walletUnfreeze($wordBody)
    {
        $dtoWordBody = new WalletUnfreezeDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnable()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(
                34501,
                ConfigUtility::getCodeMessage(34501, 'Fresns', $langTag)
            );
        }

        $checkClosingBalance = static::checkClosingBalance($wallet, $accountId, $userId);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(
                34506,
                ConfigUtility::getCodeMessage(34506, 'Fresns', $langTag)
            );
        }

        // amount
        $amountTotal = round($dtoWordBody->amountTotal, 2);

        // The counterparty wallet balance is not allowed to make payment
        if ($wallet->freeze_amount < $amountTotal) {
            return $this->failure(
                34505,
                ConfigUtility::getCodeMessage(34505, 'Fresns', $langTag)
            );
        }

        // wallet log
        $logData = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'type' => AccountWalletLog::TYPE_DE_UNFREEZE,
            'plugin_unikey' => $dtoWordBody->transactionUnikey,
            'transaction_id' => $dtoWordBody->transactionId,
            'transaction_code' => $dtoWordBody->transactionCode,
            'amount_total' => $amountTotal,
            'transaction_amount' => $amountTotal,
            'system_fee' => 0.00,
            'opening_balance' => $wallet->balance,
            'closing_balance' => $wallet->balance,
            'remark' => $dtoWordBody->remark,
            'more_json' => $dtoWordBody->moreJson,
        ];

        AccountWalletLog::create($logData);

        $wallet->decrement('freeze_amount', $amountTotal);

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // wallet increase
    public function walletIncrease($wordBody)
    {
        $dtoWordBody = new WalletIncreaseDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);
        $originAccountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $originUserId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnable()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(
                34501,
                ConfigUtility::getCodeMessage(34501, 'Fresns', $langTag)
            );
        }

        $checkClosingBalance = static::checkClosingBalance($wallet, $accountId, $userId);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(
                34506,
                ConfigUtility::getCodeMessage(34506, 'Fresns', $langTag)
            );
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
            'plugin_unikey' => $dtoWordBody->transactionUnikey,
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
            'more_json' => $dtoWordBody->moreJson,
        ];

        // Increase
        if (empty($originAccountId)) {
            AccountWalletLog::create($logData);
            static::balanceChange($wallet, 'increment', $transactionAmount);
        } else {
            $originWallet = AccountWallet::where('account_id', $originAccountId)->isEnable()->first();

            // The counterparty wallet not exist or has been banned
            if (empty($originWallet)) {
                return $this->failure(
                    34503,
                    ConfigUtility::getCodeMessage(34503, 'Fresns', $langTag)
                );
            }

            $checkBalance = static::checkBalance($originWallet, $amountTotal);
            // The counterparty wallet balance is not allowed to make payment
            if (! $checkBalance) {
                return $this->failure(
                    34505,
                    ConfigUtility::getCodeMessage(34505, 'Fresns', $langTag)
                );
            }

            // The closing balance of the counterparty does not match with the wallet limit
            $checkOriginClosingBalance = static::checkClosingBalance($originWallet, $originAccountId, $originUserId);
            if (! $checkOriginClosingBalance) {
                return $this->failure(
                    34507,
                    ConfigUtility::getCodeMessage(34507, 'Fresns', $langTag)
                );
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
                'plugin_unikey' => $dtoWordBody->transactionUnikey,
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
                'remark' => $dtoWordBody->remark,
                'more_json' => $dtoWordBody->moreJson,
            ];

            // decrement
            AccountWalletLog::create($originLogData);
            static::balanceChange($originWallet, 'decrement', $amountTotal);
        }

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // wallet decrease
    public function walletDecrease($wordBody)
    {
        $dtoWordBody = new WalletDecreaseDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);
        $originAccountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $originUserId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        $wallet = AccountWallet::where('account_id', $accountId)->isEnable()->first();
        // Account wallet not exist or has been banned
        if (empty($wallet)) {
            return $this->failure(
                34501,
                ConfigUtility::getCodeMessage(34501, 'Fresns', $langTag)
            );
        }

        if ($wallet->password) {
            $checkWallet = static::checkWalletPassword($wallet, $dtoWordBody->password);
            // Account wallet password is incorrect
            if (! $checkWallet) {
                return $this->failure(
                    34502,
                    ConfigUtility::getCodeMessage(34502, 'Fresns', $langTag)
                );
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
            return $this->failure(
                34504,
                ConfigUtility::getCodeMessage(34504, 'Fresns', $langTag)
            );
        }

        $checkClosingBalance = static::checkClosingBalance($wallet, $accountId, $userId);
        // The closing balance not match with the wallet limit
        if (! $checkClosingBalance) {
            return $this->failure(
                34506,
                ConfigUtility::getCodeMessage(34506, 'Fresns', $langTag)
            );
        }

        // wallet log
        $logData = [
            'account_id' => $accountId,
            'user_id' => $userId,
            'type' => AccountWalletLog::TYPE_DE_TRANSACTION,
            'plugin_unikey' => $dtoWordBody->transactionUnikey,
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
            'more_json' => $dtoWordBody->moreJson,
        ];

        // decrement
        if (empty($originAccountId)) {
            AccountWalletLog::create($logData);
            static::balanceChange($wallet, 'decrement', $amountTotal);
        } else {
            $originWallet = AccountWallet::where('account_id', $originAccountId)->isEnable()->first();

            // The counterparty wallet not exist or has been banned
            if (empty($originWallet)) {
                return $this->failure(
                    34503,
                    ConfigUtility::getCodeMessage(34503, 'Fresns', $langTag)
                );
            }

            // The closing balance of the counterparty does not match with the wallet limit
            $checkOriginClosingBalance = static::checkClosingBalance($originWallet, $originAccountId, $originUserId);
            if (! $checkOriginClosingBalance) {
                return $this->failure(
                    34507,
                    ConfigUtility::getCodeMessage(34507, 'Fresns', $langTag)
                );
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
                'plugin_unikey' => $dtoWordBody->transactionUnikey,
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
                'remark' => $dtoWordBody->remark,
                'more_json' => $dtoWordBody->moreJson,
            ];

            // increment
            AccountWalletLog::create($originLogData);
            static::balanceChange($originWallet, 'increment', $transactionAmount);
        }

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    /**
     * @param $wordBody
     * @return array
     */
    public function walletRevoke($wordBody)
    {
        $dtoWordBody = new WalletRevokeDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);

        // Account wallet password is incorrect
        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        if (empty($userId)) {
            $walletLog = AccountWalletLog::where('id', $dtoWordBody->logId)->where('account_id', $accountId)->isEnable()->first();
        } else {
            $walletLog = AccountWalletLog::where('id', $dtoWordBody->logId)->where('account_id', $accountId)->where('user_id', $userId)->isEnable()->first();
        }

        if (empty($walletLog)) {
            return $this->failure(
                32201,
                ConfigUtility::getCodeMessage(32201, 'Fresns', $langTag)
            );
        }

        // objectWalletLog
        if ($walletLog->object_wallet_log_id) {
            $objectWalletLog = AccountWalletLog::where('id', $walletLog->object_wallet_log_id)->first();

            $objectWallet = AccountWallet::where('account_id', $objectWalletLog->account_id)->first();

            $checkObjectBalance = static::checkBalance($objectWallet, $objectWalletLog->amount_total);
            // The counterparty wallet balance is not allowed to make payment
            if (! $checkObjectBalance) {
                return $this->failure(
                    34504,
                    ConfigUtility::getCodeMessage(34504, 'Fresns', $langTag)
                );
            }

            $objectWalletLog->update([
                'is_enable' => 0,
            ]);
        } else {
            $objectWalletLog = null;
        }

        $wallet = AccountWallet::where('account_id', $objectWalletLog->account_id)->first();

        $checkBalance = static::checkBalance($wallet, $walletLog->amount_total);
        // The counterparty wallet balance is not allowed to make payment
        if (! $checkBalance) {
            return $this->failure(
                34504,
                ConfigUtility::getCodeMessage(34504, 'Fresns', $langTag)
            );
        }

        $walletLog->update([
            'is_enable' => 0,
        ]);

        switch ($walletLog->type) {
            case AccountWalletLog::TYPE_IN_TRANSACTION:
                $wallet->decrement('balance', $walletLog->amount_total);

                if (! empty($objectWalletLog)) {
                    $objectWallet->increment('balance', $objectWalletLog->amount_total);
                }
            break;

            case AccountWalletLog::TYPE_DE_TRANSACTION:
                $wallet->increment('balance', $walletLog->amount_total);

                if (! empty($objectWalletLog)) {
                    $objectWallet->decrement('balance', $objectWalletLog->amount_total);
                }
            break;
        }

        CacheHelper::forgetFresnsAccount($dtoWordBody->aid);

        return $this->success();
    }

    // check wallet password
    public static function checkWalletPassword(AccountWallet $wallet, string $walletPassword): bool
    {
        return Hash::check($walletPassword, $wallet->password);
    }

    // check balance
    public static function checkBalance(AccountWallet $wallet, float $amount): bool
    {
        $balance = $wallet->balance - $wallet->freeze_amount;

        if ($balance < $amount) {
            return false;
        }

        return true;
    }

    // check closing balance
    public static function checkClosingBalance(AccountWallet $wallet, int $accountId, ?int $userId = null): bool
    {
        if (empty($userId)) {
            $walletLog = AccountWalletLog::where('account_id', $accountId)->isEnable()->first();
        } else {
            $walletLog = AccountWalletLog::where('account_id', $accountId)->where('user_id', $userId)->isEnable()->first();
        }

        $closingBalance = $walletLog?->closing_balance ?? 0.00;

        if ($closingBalance != $wallet->balance) {
            return false;
        }

        return true;
    }

    // wallet balance
    public static function balanceChange(AccountWallet $wallet, string $actionType, float $transactionAmount)
    {
        if (! in_array($actionType, ['increment', 'decrement'])) {
            return;
        }

        $wallet->$actionType('balance', $transactionAmount);
    }

    // wallet freeze amount
    public static function freezeAmountChange(AccountWallet $wallet, string $actionType, float $transactionAmount)
    {
        if (! in_array($actionType, ['increment', 'decrement'])) {
            return;
        }

        $wallet->$actionType('freeze_amount', $transactionAmount);
    }
}
