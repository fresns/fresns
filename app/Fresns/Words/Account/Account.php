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
use App\Fresns\Words\Account\DTO\GetAccountDeviceTokenDTO;
use App\Fresns\Words\Account\DTO\SetAccountConnectDTO;
use App\Fresns\Words\Account\DTO\VerifyAccountDTO;
use App\Fresns\Words\Account\DTO\VerifyAccountTokenDTO;
use App\Helpers\CacheHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Account as AccountModel;
use App\Models\AccountConnect;
use App\Models\AccountWallet;
use App\Models\SessionToken;
use App\Models\TempVerifyCode;
use App\Utilities\ConfigUtility;
use Carbon\Carbon;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Account
{
    use CmdWordResponseTrait;

    public function createAccount($wordBody)
    {
        $dtoWordBody = new CreateAccountDTO($wordBody);

        $typeInt = (int) $dtoWordBody->type;

        switch ($typeInt) {
            case AccountModel::CREATE_TYPE_AID:
                // aid
                $checkAccount = null;
                break;

            case AccountModel::CREATE_TYPE_EMAIL:
                // email
                $checkAccount = AccountModel::where('email', $dtoWordBody->account)->first();
                break;

            case AccountModel::CREATE_TYPE_PHONE:
                // phone
                $checkAccount = AccountModel::where('phone', $dtoWordBody->countryCallingCode.$dtoWordBody->account)->first();
                break;

            case AccountModel::CREATE_TYPE_CONNECT:
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
                    return $this->failure(34403, ConfigUtility::getCodeMessage(34403));
                }
                break;
        }

        if ($checkAccount) {
            return $this->failure(34204, ConfigUtility::getCodeMessage(34204));
        }

        $inputArr = [];
        $inputArr = match ($typeInt) {
            AccountModel::CREATE_TYPE_EMAIL => [
                'email' => $dtoWordBody->account,
            ],
            AccountModel::CREATE_TYPE_PHONE => [
                'country_calling_code' => $dtoWordBody->countryCallingCode,
                'phone' => $dtoWordBody->countryCallingCode.$dtoWordBody->account,
            ],
            AccountModel::CREATE_TYPE_CONNECT => [
                'email' => $dtoWordBody->connectEmail,
                'country_calling_code' => $dtoWordBody->connectPurePhone ? $dtoWordBody->connectCountryCallingCode : null,
                'phone' => $dtoWordBody->connectPurePhone ? $dtoWordBody->connectCountryCallingCode.$dtoWordBody->connectPurePhone : null,
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
                if (empty($info['connectPlatformId']) || empty($info['connectAccountId']) || empty($info['appFskey'])) {
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
                    'app_fskey' => $info['appFskey'],
                    'more_info' => $info['moreInfo'] ?? null,
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
                'pin' => $userInfo['pin'] ?? null,
                'avatarFid' => $userInfo['avatarFid'] ?? null,
                'avatarUrl' => $userInfo['avatarUrl'] ?? null,
                'gender' => $userInfo['gender'] ?? null,
                'gender_pronoun' => $userInfo['genderPronoun'] ?? null,
                'gender_custom' => $userInfo['genderCustom'] ?? null,
            ];

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->createUser($userWordBody);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getErrorResponse();
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

        switch ($dtoWordBody->type) {
            case AccountModel::VERIFY_TYPE_AUTO:
                $account = AccountModel::whereAny(['aid', 'email', 'phone'], $dtoWordBody->account)->first();

                if (! $dtoWordBody->password) {
                    return $this->failure(34111, ConfigUtility::getCodeMessage(34111));
                }
                break;

            case AccountModel::VERIFY_TYPE_AID:
                $account = AccountModel::where('aid', $dtoWordBody->account)->first();
                break;

            case AccountModel::VERIFY_TYPE_EMAIL:
                $account = AccountModel::where('email', $dtoWordBody->account)->first();
                break;

            case AccountModel::VERIFY_TYPE_PHONE:
                $phoneNumber = $dtoWordBody->countryCallingCode.$dtoWordBody->account;
                $account = AccountModel::where('phone', $phoneNumber)->first();
                break;

            case AccountModel::VERIFY_TYPE_CONNECT:
                $accountConnect = AccountConnect::with(['account'])->where('connect_platform_id', $dtoWordBody->connectPlatformId)->where('connect_account_id', $dtoWordBody->connectAccountId)->first();
                if (empty($accountConnect)) {
                    return $this->failure(34301, ConfigUtility::getCodeMessage(34301));
                }

                if (! $accountConnect->is_enabled) {
                    return $this->failure(34404, ConfigUtility::getCodeMessage(34404));
                }

                $account = $accountConnect?->account;
                break;
        }

        if (empty($account)) {
            return $this->failure(31502, ConfigUtility::getCodeMessage(31502));
        }

        $loginErrorCount = ConfigUtility::getLoginErrorCount($account->id);

        if ($loginErrorCount >= 5) {
            return $this->failure(34306, ConfigUtility::getCodeMessage(34306));
        }

        if ($dtoWordBody->password) {
            if (! Hash::check($dtoWordBody->password, $account->password)) {
                return $this->failure(34304, ConfigUtility::getCodeMessage(34304));
            }
        }

        if ($dtoWordBody->verifyCode) {
            $accountType = 2;
            $countryCallingCode = $account->country_calling_code;
            $accountInfo = $account->getPurePhone();

            $isEmail = filter_var($dtoWordBody->account, FILTER_VALIDATE_EMAIL);
            if ($isEmail) {
                $accountType = 1;
                $countryCallingCode = null;
                $accountInfo = $account->email;
            }

            $codeWordBody = [
                'type' => $accountType,
                'account' => $accountInfo,
                'countryCallingCode' => $countryCallingCode,
                'verifyCode' => $dtoWordBody->verifyCode,
                'templateId' => TempVerifyCode::TEMPLATE_LOGIN_ACCOUNT,
            ];

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getErrorResponse();
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

        $accountConnect = AccountConnect::withTrashed()->with(['account'])->where('connect_platform_id', $dtoWordBody->connectPlatformId)->where('connect_account_id', $dtoWordBody->connectAccountId)->first();

        $account = $accountConnect?->account;

        // I already have connected an account
        if ($accountConnect && $account?->aid != $dtoWordBody->aid) {
            return $this->failure(34405, ConfigUtility::getCodeMessage(34405));
        }

        $accountModel = AccountModel::where('aid', $dtoWordBody->aid)->first();

        try {
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
                'more_info' => $dtoWordBody->moreInfo,
                'app_fskey' => $dtoWordBody->fskey,
                'deleted_at' => null,
            ]);
        } catch (\Exception $e) {
            return $this->failure(32302, ConfigUtility::getCodeMessage(32302));
        }

        if (empty($accountModel->email) && $dtoWordBody->connectEmail) {
            $accountModel->update([
                'email' => $dtoWordBody->connectEmail,
            ]);
        }

        if (empty($accountModel->phone) && $dtoWordBody->connectPurePhone) {
            $accountModel->update([
                'phone' => $dtoWordBody->connectCountryCallingCode.$dtoWordBody->connectPurePhone,
                'country_calling_code' => $dtoWordBody->connectCountryCallingCode,
            ]);
        }

        CacheHelper::forgetFresnsAccount($accountModel->aid);

        return $this->success();
    }

    public function disconnectAccountConnect($wordBody)
    {
        $dtoWordBody = new DisconnectAccountConnectDTO($wordBody);

        $accountModel = AccountModel::where('aid', $dtoWordBody->aid)->first();

        $connectArr = AccountConnect::withTrashed()
            ->where('account_id', $accountModel->id)
            ->where('connect_platform_id', $dtoWordBody->connectPlatformId)
            ->whereNotIn('connect_platform_id', [
                AccountConnect::PLATFORM_WECHAT_OPEN_PLATFORM,
                AccountConnect::PLATFORM_QQ_OPEN_PLATFORM,
            ])
            ->get();

        if ($connectArr->count() == 1 && empty($accountModel->email) && empty($accountModel->phone)) {
            return $this->failure(34406, ConfigUtility::getCodeMessage(34406));
        }

        foreach ($connectArr as $connect) {
            $connect->forceDelete();
        }

        $wechatArr = [
            AccountConnect::PLATFORM_WECHAT_OFFICIAL_ACCOUNT,
            AccountConnect::PLATFORM_WECHAT_MINI_PROGRAM,
            AccountConnect::PLATFORM_WECHAT_MOBILE_APPLICATION,
            AccountConnect::PLATFORM_WECHAT_WEBSITE_APPLICATION,
        ];

        if (in_array($dtoWordBody->connectPlatformId, $wechatArr)) {
            $connects = AccountConnect::where('account_id', $accountModel->id)->whereIn('connect_platform_id', [
                AccountConnect::PLATFORM_WECHAT_OFFICIAL_ACCOUNT,
                AccountConnect::PLATFORM_WECHAT_MINI_PROGRAM,
                AccountConnect::PLATFORM_WECHAT_MOBILE_APPLICATION,
                AccountConnect::PLATFORM_WECHAT_WEBSITE_APPLICATION,
            ])->get();

            // Delete WeChat unionid
            if ($connects->isEmpty()) {
                AccountConnect::withTrashed()
                    ->where('account_id', $accountModel->id)
                    ->where('connect_platform_id', AccountConnect::PLATFORM_WECHAT_OPEN_PLATFORM)
                    ->forceDelete();
            }
        }

        $qqArr = [
            AccountConnect::PLATFORM_QQ_MINI_PROGRAM,
            AccountConnect::PLATFORM_QQ_MOBILE_APPLICATION,
            AccountConnect::PLATFORM_QQ_WEBSITE_APPLICATION,
        ];

        if (in_array($dtoWordBody->connectPlatformId, $qqArr)) {
            $connects = AccountConnect::where('account_id', $accountModel->id)->whereIn('connect_platform_id', [
                AccountConnect::PLATFORM_QQ_MINI_PROGRAM,
                AccountConnect::PLATFORM_QQ_MOBILE_APPLICATION,
                AccountConnect::PLATFORM_QQ_WEBSITE_APPLICATION,
            ])->get();

            // Delete WeChat unionid
            if ($connects->isEmpty()) {
                AccountConnect::withTrashed()
                    ->where('account_id', $accountModel->id)
                    ->where('connect_platform_id', AccountConnect::PLATFORM_QQ_OPEN_PLATFORM)
                    ->forceDelete();
            }
        }

        CacheHelper::forgetFresnsAccount($accountModel->aid);

        return $this->success();
    }

    public function createAccountToken($wordBody)
    {
        $dtoWordBody = new CreateAccountTokenDTO($wordBody);

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);

        if (empty($accountId)) {
            return $this->failure(31502, ConfigUtility::getCodeMessage(31502));
        }

        $keyInfo = PrimaryHelper::fresnsModelByFsid('key', $dtoWordBody->appId);

        if (empty($keyInfo) || ! $keyInfo->is_enabled) {
            return $this->failure(31301, ConfigUtility::getCodeMessage(31301));
        }

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

        $token = Str::random(64);

        $condition = [
            'app_id' => $dtoWordBody->appId,
            'platform_id' => $dtoWordBody->platformId,
            'version' => $dtoWordBody->version,
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

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);

        if (empty($accountId)) {
            return $this->failure(31502, ConfigUtility::getCodeMessage(31502));
        }

        $aidToken = $dtoWordBody->aidToken;

        $cacheKey = "fresns_token_account_{$accountId}_{$aidToken}";
        $cacheTag = 'fresnsAccounts';

        $accountToken = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($accountToken)) {
            $accountToken = SessionToken::where('account_id', $accountId)
                ->where('account_token', $aidToken)
                ->whereNull('user_id')
                ->first();

            if (empty($accountToken)) {
                return $this->failure(31505, ConfigUtility::getCodeMessage(31505));
            }

            CacheHelper::put($accountToken, $cacheKey, $cacheTag);
        }

        if ($accountToken->platform_id != $dtoWordBody->platformId) {
            return $this->failure(31103, ConfigUtility::getCodeMessage(31103));
        }

        if ($accountToken->expired_at && $accountToken->expired_at < now()) {
            return $this->failure(31504, ConfigUtility::getCodeMessage(31504));
        }

        return $this->success();
    }

    public function getAccountDeviceToken($wordBody)
    {
        $dtoWordBody = new GetAccountDeviceTokenDTO($wordBody);

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);

        $tokenQuery = SessionToken::where('account_id', $accountId)->whereNotNull('device_token');

        $tokenQuery->when($dtoWordBody->platformId, function ($query, $value) {
            $query->where('platform_id', $value);
        });

        $tokens = $tokenQuery->latest()->get();

        $tokenArr = [];
        foreach ($tokens as $token) {
            $item['platformId'] = $token->platform_id;
            $item['uid'] = $token->user_id ? PrimaryHelper::fresnsModelById('user', $token->user_id)?->uid : null;
            $item['deviceToken'] = $token->device_token;
            $item['datetime'] = $token->created_at;

            $tokenArr[] = $item;
        }

        return $this->success($tokenArr);
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
