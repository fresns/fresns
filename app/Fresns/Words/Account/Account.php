<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account;

use App\Fresns\Words\Account\DTO\AddAccountDTO;
use App\Fresns\Words\Account\DTO\CreateAccountTokenDTO;
use App\Fresns\Words\Account\DTO\LogicalDeletionAccountDTO;
use App\Fresns\Words\Account\DTO\VerifyAccountDTO;
use App\Fresns\Words\Account\DTO\VerifyAccountTokenDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Account as AccountModel;
use App\Models\AccountConnect;
use App\Models\AccountWallet;
use App\Models\SessionToken;
use App\Models\VerifyCode;
use App\Utilities\ConfigUtility;
use Carbon\Carbon;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class Account
{
    use CmdWordResponseTrait;

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function addAccount($wordBody)
    {
        $dtoWordBody = new AddAccountDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        if ($dtoWordBody->type == 1) {
            $checkAccount = AccountModel::where('email', $dtoWordBody->account)->first();
        } else {
            $checkAccount = AccountModel::where('phone', $dtoWordBody->countryCode.$dtoWordBody->account)->first();
        }

        if (! empty($checkAccount)) {
            return $this->failure(
                34204,
                ConfigUtility::getCodeMessage(34204, 'Fresns', $langTag)
            );
        }

        $connectInfoArr = [];
        if (isset($dtoWordBody->connectInfo)) {
            $connectInfoArr = json_decode($dtoWordBody->connectInfo, true);
            $connectTokenArr = [];
            foreach ($connectInfoArr as $v) {
                $connectTokenArr[] = $v['connectToken'];
            }
            $count = AccountConnect::whereIn('connect_token', $connectTokenArr)->count();
            if ($count > 0) {
                ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_DATA_ERROR)::throw();
            }
        }

        $inputArr = [];
        $inputArr = match ($dtoWordBody->type) {
            1 => ['email' => $dtoWordBody->account],
            2 => [
                'country_code' => $dtoWordBody->countryCode,
                'pure_phone' => $dtoWordBody->account,
                'phone' => $dtoWordBody->countryCode.$dtoWordBody->account,
            ],
            default => [],
        };
        $inputArr['password'] = isset($dtoWordBody->password) ? Hash::make($dtoWordBody->password) : null;
        $inputArr['last_login_at'] = now();

        $accountModel = AccountModel::create($inputArr);

        // Account Wallet Table
        $accountWalletsInput = [
            'account_id' => $accountModel->id,
        ];
        AccountWallet::create($accountWalletsInput);

        // Account Connects Table
        if ($connectInfoArr) {
            $itemArr = [];
            foreach ($connectInfoArr as $info) {
                $item['account_id'] = $accountModel->id;
                $item['connect_id'] = $info['connectId'];
                $item['connect_token'] = $info['connectToken'];
                $item['connect_refresh_token'] = $info['connectRefreshToken'];
                $item['connect_name'] = $info['connectName'];
                $item['connect_nickname'] = $info['connectNickname'];
                $item['connect_avatar'] = $info['connectAvatar'];
                $item['plugin_unikey'] = 'fresns_cmd_user_register';
                $itemArr[] = $item;
            }

            AccountConnect::create($itemArr);
        }

        return $this->success([
            'type' => $accountModel->type,
            'aid' => $accountModel->aid,
        ]);
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function verifyAccount($wordBody)
    {
        $dtoWordBody = new VerifyAccountDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        if ($dtoWordBody->type == 1) {
            $accountName = $dtoWordBody->account;
            $account = AccountModel::where('email', $accountName)->first();
        } else {
            $accountName = $dtoWordBody->countryCode.$dtoWordBody->account;
            $account = AccountModel::where('phone', $accountName)->first();
        }

        if (empty($account)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag),
            );
        }

        $loginErrorCount = ConfigUtility::getLoginErrorCount($account->id);

        if ($loginErrorCount >= 5) {
            return $this->failure(
                34306,
                ConfigUtility::getCodeMessage(34306, 'Fresns', $langTag),
            );
        }

        if ($dtoWordBody->password) {
            if (! Hash::check($dtoWordBody->password, $account->password)) {
                return $this->failure(
                    34304,
                    ConfigUtility::getCodeMessage(34304, 'Fresns', $langTag),
                );
            }
        } else {
            $codeWordBody = [
                'type' => $dtoWordBody->type,
                'account' => $dtoWordBody->account,
                'countryCode' => $dtoWordBody->countryCode,
                'verifyCode' => $dtoWordBody->verifyCode,
                'templateId' => VerifyCode::TEMPLATE_LOGIN,
            ];

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getOrigin();
            }
        }

        $account->update([
            'last_login_at' => now(),
        ]);

        return $this->success([
            'type' => $account->type,
            'aid' => $account->aid,
        ]);
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function createAccountToken($wordBody)
    {
        $dtoWordBody = new CreateAccountTokenDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $keyInfo = PrimaryHelper::fresnsModelByFsid('key', $dtoWordBody->appId);

        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        if (empty($keyInfo) || ! $keyInfo->is_enable) {
            return $this->failure(
                31301,
                ConfigUtility::getCodeMessage(31301, 'Fresns', $langTag),
            );
        }

        $token = \Str::random(32);
        $expiredHours = null;
        $expiredDays = null;
        $expiredDateTime = null;
        if ($dtoWordBody->expiredTime) {
            $now = time();
            $time = $dtoWordBody->expiredTime * 3600;
            $expiredTime = $now + $time;

            $dt = Carbon::parse($expiredTime);

            $expiredHours = $dtoWordBody->expiredTime;
            $expiredDays = $dt->diffInDays(Carbon::now());
            $expiredDateTime = date('Y-m-d H:i:s', $expiredTime);
        }

        $condition = [
            'platform_id' => $dtoWordBody->platformId,
            'version' => $dtoWordBody->version,
            'app_id' => $dtoWordBody->appId,
            'account_id' => $accountId,
            'account_token' => $token,
            'expired_at' => $expiredDateTime,
        ];

        $tokenModel = SessionToken::create($condition);

        return $this->success([
            'aid' => $dtoWordBody->aid,
            'aidToken' => $token,
            'aidTokenId' => $tokenModel->id,
            'expiredHours' => $expiredHours,
            'expiredDays' => $expiredDays,
            'expiredDateTime' => $expiredDateTime,
        ]);
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function verifyAccountToken($wordBody)
    {
        $dtoWordBody = new VerifyAccountTokenDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);

        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        $aidToken = $dtoWordBody->aidToken;

        $cacheKey = "fresns_token_account_{$accountId}_{$aidToken}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        // Cache::tags(['fresnsSystems'])
        $accountToken = Cache::remember($cacheKey, $cacheTime, function () use ($accountId, $aidToken) {
            return SessionToken::where('account_id', $accountId)
                ->where('account_token', $aidToken)
                ->whereNull('user_id')
                ->first();
        });

        if (is_null($accountToken)) {
            Cache::forget($cacheKey);

            return $this->failure(
                31505,
                ConfigUtility::getCodeMessage(31505, 'Fresns', $langTag)
            );
        }

        if ($accountToken->platform_id != $dtoWordBody->platformId) {
            return $this->failure(
                31103,
                ConfigUtility::getCodeMessage(31103, 'Fresns', $langTag)
            );
        }

        if ($accountToken->expired_at && $accountToken->expired_at < now()) {
            return $this->failure(
                31504,
                ConfigUtility::getCodeMessage(31504, 'Fresns', $langTag)
            );
        }

        return $this->success();
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function logicalDeletionAccount($wordBody)
    {
        $dtoWordBody = new LogicalDeletionAccountDTO($wordBody);

        $account = AccountModel::whereAid($dtoWordBody->aid)->first();

        $oldEmail = $account->email;
        $oldPhone = $account->phone;
        $dateTime = 'deleted#'.date('YmdHis').'#';

        $account->update([
            'phone' => $dateTime.$oldEmail,
            'email' => $dateTime.$oldPhone,
        ]);

        $account->delete();

        AccountConnect::where('account_id', $account->id)->forceDelete();

        return $this->success();
    }
}
