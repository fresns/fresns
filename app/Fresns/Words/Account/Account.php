<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account;

use App\Fresns\Words\Account\DTO\AddAccountDTO;
use App\Fresns\Words\Account\DTO\CreateSessionTokenDTO;
use App\Fresns\Words\Account\DTO\LogicalDeletionAccountDTO;
use App\Fresns\Words\Account\DTO\VerifyAccountDTO;
use App\Fresns\Words\Account\DTO\VerifySessionTokenDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Account as AccountModel;
use App\Models\AccountConnect;
use App\Models\AccountWallet;
use App\Models\SessionToken;
use App\Utilities\ConfigUtility;
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

        if (empty($dtoWordBody->password)) {
            $codeWordBody = [
                'type' => $dtoWordBody->type,
                'account' => $dtoWordBody->account,
                'countryCode' => $dtoWordBody->countryCode,
                'verifyCode' => $dtoWordBody->verifyCode,
            ];

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getOrigin();
            }

            if ($fresnsResp->isSuccessResponse()) {
                return $this->success([
                    'type' => $account->type,
                    'aid' => $account->aid,
                ]);
            }
        }

        if (! Hash::check($dtoWordBody->password, $account->password)) {
            return $this->failure(
                34304,
                ConfigUtility::getCodeMessage(34304, 'Fresns', $langTag),
            );
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
    public function createSessionToken($wordBody)
    {
        $dtoWordBody = new CreateSessionTokenDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $platformId = $dtoWordBody->platformId;
        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);

        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        $tokenInfo = SessionToken::where('platform_id', $platformId)
            ->where('account_id', $accountId)
            ->where('user_id', $userId)
            ->first();

        if (! empty($tokenInfo)) {
            SessionToken::where('platform_id', $platformId)
                ->where('account_id', $accountId)
                ->where('user_id', $userId)
                ->delete();
        }

        $token = \Str::random(32);
        $expiredAt = null;
        if ($dtoWordBody->expiredTime) {
            $now = time();
            $time = $dtoWordBody->expiredTime * 3600;
            $expiredTime = $now + $time;
            $expiredAt = date('Y-m-d H:i:s', $expiredTime);
        }

        $condition = [
            'platform_id' => $platformId,
            'account_id' => $accountId,
            'user_id' => $userId,
            'token' => $token,
            'expired_at' => $expiredAt,
        ];

        SessionToken::create($condition);

        return $this->success([
            'aid' => $dtoWordBody->aid,
            'uid' => $dtoWordBody->uid,
            'token' => $token,
            'expiredTime' => $expiredAt,
        ]);
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function verifySessionToken($wordBody)
    {
        $dtoWordBody = new VerifySessionTokenDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $platformId = $dtoWordBody->platformId;
        $account = PrimaryHelper::fresnsModelByFsid('account', $dtoWordBody->aid);
        $user = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody->uid);
        $accountId = $account?->id;
        $userId = $user?->id;
        $token = $dtoWordBody->token;

        $cacheKey = "fresns_api_token_{$platformId}_{$accountId}_{$userId}_{$token}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $session = Cache::remember($cacheKey, $cacheTime, function () use ($accountId, $token) {
            return SessionToken::where('account_id', $accountId)->where('token', $token)->first();
        });

        if (is_null($session)) {
            Cache::forget($cacheKey);

            if (empty($userId)) {
                return $this->failure(
                    31505,
                    ConfigUtility::getCodeMessage(31505, 'Fresns', $langTag)
                );
            }

            return $this->failure(
                31603,
                ConfigUtility::getCodeMessage(31603, 'Fresns', $langTag)
            );
        }

        if ($session->platform_id != $platformId) {
            return $this->failure(
                31102,
                ConfigUtility::getCodeMessage(31102, 'Fresns', $langTag)
            );
        }

        if ($userId) {
            if ($session->user_id != $userId) {
                return $this->failure(
                    31603,
                    ConfigUtility::getCodeMessage(31603, 'Fresns', $langTag)
                );
            }

            if ($user->account_id != $accountId) {
                return $this->failure(
                    35201,
                    ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
                );
            }
        }

        if ($session->expired_at && $session->expired_at < now()) {
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
