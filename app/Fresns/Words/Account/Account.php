<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account;

use App\Fresns\Words\Account\DTO\CreateAccountDTO;
use App\Fresns\Words\Account\DTO\CreateAccountTokenDTO;
use App\Fresns\Words\Account\DTO\DeletionAccountDTO;
use App\Fresns\Words\Account\DTO\DisconnectAccountConnectDTO;
use App\Fresns\Words\Account\DTO\SetAccountConnectDTO;
use App\Fresns\Words\Account\DTO\VerifyAccountDTO;
use App\Fresns\Words\Account\DTO\VerifyAccountTokenDTO;
use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Account as AccountModel;
use App\Models\AccountConnect;
use App\Models\AccountWallet;
use App\Models\SessionToken;
use App\Models\VerifyCode;
use App\Utilities\ConfigUtility;
use Carbon\Carbon;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Facades\Hash;

class Account
{
    use CmdWordResponseTrait;

    public function createAccount($wordBody)
    {
        $dtoWordBody = new CreateAccountDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $typeInt = (int) $dtoWordBody->type;

        switch ($typeInt) {
            case AccountModel::ACT_TYPE_EMAIL:
                // email
                $checkAccount = AccountModel::where('email', $dtoWordBody->account)->first();
                break;

            case AccountModel::ACT_TYPE_PHONE:
                // phone
                $checkAccount = AccountModel::where('phone', $dtoWordBody->countryCode.$dtoWordBody->account)->first();
                break;

            case AccountModel::ACT_TYPE_CONNECT:
                // connect
                $checkAccount = null;

                $connectPlatformIdArr = [];
                $connectAccountIdArr = [];
                foreach ($dtoWordBody->connectInfo as $connect) {
                    if (empty($connect['connectPlatformId']) || empty($connect['connectAccountId'])) {
                        continue;
                    }

                    $connectPlatformIdArr[] = $connect['connectPlatformId'];
                    $connectAccountIdArr[] = $connect['connectAccountId'];
                }

                $count = 0;
                if ($connectAccountIdArr) {
                    $count = AccountConnect::whereIn('connect_platform_id', $connectPlatformIdArr)->whereIn('connect_account_id', $connectAccountIdArr)->count();
                }

                if ($count > 0) {
                    return $this->failure(
                        34403,
                        ConfigUtility::getCodeMessage(34403, 'Fresns', $langTag)
                    );
                }
                break;
        }

        if ($checkAccount) {
            return $this->failure(
                34204,
                ConfigUtility::getCodeMessage(34204, 'Fresns', $langTag)
            );
        }

        $inputArr = [];
        $inputArr = match ($typeInt) {
            AccountModel::ACT_TYPE_EMAIL => [
                'email' => $dtoWordBody->account,
            ],
            AccountModel::ACT_TYPE_PHONE => [
                'country_code' => $dtoWordBody->countryCode,
                'pure_phone' => $dtoWordBody->account,
                'phone' => $dtoWordBody->countryCode.$dtoWordBody->account,
            ],
            AccountModel::ACT_TYPE_CONNECT => [
                'email' => $dtoWordBody->connectEmail,
                'country_code' => $dtoWordBody->connectCountryCode,
                'pure_phone' => $dtoWordBody->connectPhone,
                'phone' => $dtoWordBody->connectPhone ? $dtoWordBody->connectCountryCode.$dtoWordBody->connectPhone : null,
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
        if ($dtoWordBody->connectInfo) {
            foreach ($dtoWordBody->connectInfo as $info) {
                if (empty($info['connectPlatformId']) || empty($info['connectAccountId']) || empty($info['pluginFskey'])) {
                    continue;
                }

                AccountConnect::create([
                    'account_id' => $accountModel->id,
                    'connect_platform_id' => $info['connectPlatformId'],
                    'connect_account_id' => $info['connectAccountId'],
                    'connect_token' => $info['connectToken'] ?? null,
                    'connect_refresh_token' => $info['connectRefreshToken'] ?? null,
                    'connect_username' => $info['connectUsername'] ?? null,
                    'connect_nickname' => $info['connectNickname'] ?? null,
                    'connect_avatar' => $info['connectAvatar'] ?? null,
                    'plugin_fskey' => $info['pluginFskey'],
                    'more_json' => $info['moreJson'] ?? null,
                    'refresh_token_expired_at' => $info['refreshTokenExpiredDatetime'] ?? null,
                ]);
            }
        }

        $uid = null;
        $username = null;
        $nickname = null;
        if ($dtoWordBody->createUser) {
            $userInfo = $dtoWordBody->userInfo;

            $userWordBody = [
                'aid' => $accountModel->aid,
                'username' => $userInfo['username'] ?? null,
                'nickname' => $userInfo['nickname'] ?? null,
                'password' => $userInfo['password'] ?? null,
                'avatarFid' => $userInfo['avatarFid'] ?? null,
                'avatarUrl' => $userInfo['avatarUrl'] ?? null,
                'gender' => $userInfo['gender'] ?? null,
                'birthday' => $userInfo['birthday'] ?? null,
            ];

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->createUser($userWordBody);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->errorResponse();
            }

            $uid = $fresnsResp->getData('uid');
            $username = $fresnsResp->getData('username');
            $nickname = $fresnsResp->getData('nickname');
        }

        return $this->success([
            'type' => $accountModel->type,
            'aid' => $accountModel->aid,
            'uid' => $uid,
            'username' => $username,
            'nickname' => $nickname,
        ]);
    }

    public function verifyAccount($wordBody)
    {
        $dtoWordBody = new VerifyAccountDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        switch ($dtoWordBody->type) {
            case AccountModel::ACT_TYPE_EMAIL:
                $account = AccountModel::where('email', $dtoWordBody->account)->first();
                break;

            case AccountModel::ACT_TYPE_PHONE:
                $phoneNumber = $dtoWordBody->countryCode.$dtoWordBody->account;
                $account = AccountModel::where('phone', $phoneNumber)->first();
                break;

            case AccountModel::ACT_TYPE_CONNECT:
                $accountConnect = AccountConnect::with(['account'])->where('connect_platform_id', $dtoWordBody->connectPlatformId)->where('connect_account_id', $dtoWordBody->connectAccountId)->first();
                if (empty($accountConnect)) {
                    return $this->failure(
                        34301,
                        ConfigUtility::getCodeMessage(34301, 'Fresns', $langTag),
                    );
                }

                if (! $accountConnect->is_enabled) {
                    return $this->failure(
                        34404,
                        ConfigUtility::getCodeMessage(34404, 'Fresns', $langTag),
                    );
                }

                $account = $accountConnect?->account;
                break;
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
        }

        if ($dtoWordBody->verifyCode) {
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

    public function setAccountConnect($wordBody)
    {
        $dtoWordBody = new SetAccountConnectDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $accountConnect = AccountConnect::withTrashed()->with(['account'])->where('connect_platform_id', $dtoWordBody->connectPlatformId)->where('connect_account_id', $dtoWordBody->connectAccountId)->first();

        $account = $accountConnect?->account;

        // I already have connected an account
        if ($accountConnect && $account) {
            return $this->failure(
                34405,
                ConfigUtility::getCodeMessage(34405, 'Fresns', $langTag),
            );
        }

        try {
            $accountModel = AccountModel::where('aid', $dtoWordBody->aid)->first();

            AccountConnect::withTrashed()->updateOrCreate([
                'connect_platform_id' => $dtoWordBody->connectPlatformId,
                'connect_account_id' => $dtoWordBody->connectAccountId,
            ], [
                'account_id' => $accountModel->id,
                'connect_token' => $dtoWordBody->connectToken,
                'connect_refresh_token' => $dtoWordBody->connectRefreshToken,
                'refresh_token_expired_at' => $dtoWordBody->refreshTokenExpiredDatetime,
                'connect_username' => $dtoWordBody->connectUsername,
                'connect_nickname' => $dtoWordBody->connectNickname,
                'connect_avatar' => $dtoWordBody->connectAvatar,
                'more_json' => $dtoWordBody->moreJson,
                'plugin_fskey' => $dtoWordBody->fskey,
                'deleted_at' => null,
            ]);
        } catch (\Exception $e) {
            return $this->failure(
                32302,
                ConfigUtility::getCodeMessage(32302, 'Fresns', $langTag),
            );
        }

        if (empty($account->email) && $dtoWordBody->connectEmail) {
            $account->update([
                'email' => $dtoWordBody->connectEmail,
            ]);
        }

        if (empty($account->phone) && $dtoWordBody->connectPhone) {
            $account->update([
                'country_code' => $dtoWordBody->connectCountryCode,
                'pure_phone' => $dtoWordBody->connectPhone,
                'phone' => $dtoWordBody->connectCountryCode.$dtoWordBody->connectPhone,
            ]);
        }

        CacheHelper::forgetFresnsAccount($accountModel->aid);

        return $this->success();
    }

    public function disconnectAccountConnect($wordBody)
    {
        $dtoWordBody = new DisconnectAccountConnectDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $accountModel = AccountModel::where('aid', $dtoWordBody->aid)->first();

        $connectArr = AccountConnect::withTrashed()
            ->where('account_id', $accountModel->id)
            ->where('connect_platform_id', $dtoWordBody->connectPlatformId)
            ->where('connect_platform_id', '!=', AccountConnect::CONNECT_WECHAT_OPEN_PLATFORM)
            ->get();

        if ($connectArr->count() == 1 && empty($accountModel->email) && empty($accountModel->phone)) {
            return $this->failure(
                34406,
                ConfigUtility::getCodeMessage(34406, 'Fresns', $langTag),
            );
        }

        foreach ($connectArr as $connect) {
            $connect->forceDelete();
        }

        $wechatArr = [
            AccountConnect::CONNECT_WECHAT_OFFICIAL_ACCOUNT,
            AccountConnect::CONNECT_WECHAT_MINI_PROGRAM,
            AccountConnect::CONNECT_WECHAT_MOBILE_APPLICATION,
            AccountConnect::CONNECT_WECHAT_WEBSITE_APPLICATION,
        ];

        if (in_array($dtoWordBody->connectPlatformId, $wechatArr)) {
            $connects = AccountConnect::where('account_id', $accountModel->id)->whereIn('connect_platform_id', [
                AccountConnect::CONNECT_WECHAT_OFFICIAL_ACCOUNT,
                AccountConnect::CONNECT_WECHAT_MINI_PROGRAM,
                AccountConnect::CONNECT_WECHAT_WEBSITE_APPLICATION,
                AccountConnect::CONNECT_WECHAT_MOBILE_APPLICATION,
            ])->get();

            // Delete WeChat unionid
            if ($connects->isEmpty()) {
                AccountConnect::withTrashed()
                    ->where('account_id', $accountModel->id)
                    ->where('connect_platform_id', AccountConnect::CONNECT_WECHAT_OPEN_PLATFORM)
                    ->forceDelete();
            }
        }

        CacheHelper::forgetFresnsAccount($accountModel->aid);

        return $this->success();
    }

    public function createAccountToken($wordBody)
    {
        $dtoWordBody = new CreateAccountTokenDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $keyInfo = PrimaryHelper::fresnsModelByFsid('key', $dtoWordBody->appId);

        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        if (empty($keyInfo) || ! $keyInfo->is_enabled) {
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
            'device_token' => $dtoWordBody->deviceToken ?? null,
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

    public function verifyAccountToken($wordBody)
    {
        $dtoWordBody = new VerifyAccountTokenDTO($wordBody);
        $langTag = AppHelper::getLangTag();

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);

        if (empty($accountId)) {
            return $this->failure(
                31502,
                ConfigUtility::getCodeMessage(31502, 'Fresns', $langTag)
            );
        }

        $aidToken = $dtoWordBody->aidToken;

        $cacheKey = "fresns_token_account_{$accountId}_{$aidToken}";
        $cacheTag = 'fresnsAccounts';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return $this->failure(
                31505,
                ConfigUtility::getCodeMessage(31505, 'Fresns', $langTag)
            );
        }

        $accountToken = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($accountToken)) {
            $accountToken = SessionToken::where('account_id', $accountId)
                ->where('account_token', $aidToken)
                ->whereNull('user_id')
                ->first();

            if (empty($accountToken)) {
                return $this->failure(
                    31505,
                    ConfigUtility::getCodeMessage(31505, 'Fresns', $langTag)
                );
            }

            CacheHelper::put($accountToken, $cacheKey, $cacheTag);
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

    public function logicalDeletionAccount($wordBody)
    {
        $dtoWordBody = new DeletionAccountDTO($wordBody);

        $account = AccountModel::with(['connects', 'users'])->whereAid($dtoWordBody->aid)->first();

        $oldEmail = $account->email;
        $oldPhone = $account->phone;
        $dateTime = 'deleted#'.date('YmdHis').'#';

        $account->update([
            'email' => $dateTime.$oldEmail,
            'phone' => $dateTime.$oldPhone,
        ]);

        $account->delete();

        foreach ($account->connects as $connect) {
            $connect->forceDelete();
        }

        foreach ($account->users as $user) {
            \FresnsCmdWord::plugin('Fresns')->logicalDeletionUser([
                'uid' => $user->uid,
            ]);
        }

        return $this->success();
    }

    public function physicalDeletionAccount($wordBody)
    {
        $dtoWordBody = new DeletionAccountDTO($wordBody);

        if (config('queue.default') == 'sync') {
            return $this->failure(21011);
        }

        // waiting for development

        return $this->failure(21010);
    }
}
