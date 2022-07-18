<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\AccountApplyDeleteDTO;
use App\Fresns\Api\Http\DTO\AccountEditDTO;
use App\Fresns\Api\Http\DTO\AccountLoginDTO;
use App\Fresns\Api\Http\DTO\AccountRegisterDTO;
use App\Fresns\Api\Http\DTO\AccountResetPasswordDTO;
use App\Fresns\Api\Http\DTO\AccountVerifyIdentityDTO;
use App\Fresns\Api\Http\DTO\AccountWalletLogsDTO;
use App\Fresns\Api\Services\AccountService;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\AccountWallet;
use App\Models\AccountWalletLog;
use App\Models\SessionLog;
use App\Models\SessionToken;
use App\Models\VerifyCode;
use App\Utilities\ValidationUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    // register
    public function register(Request $request)
    {
        $dtoRequest = new AccountRegisterDTO($request->all());

        $configs = ConfigHelper::fresnsConfigByItemKeys([
            'site_mode',
            'site_public_status',
            'site_public_service',
            'site_register_email',
            'site_register_phone',
        ]);

        if ($configs['site_mode'] == 'private' || ! $configs['site_public_status'] || ! empty($configs['site_public_service'])) {
            throw new ApiException(34201);
        }

        if ($dtoRequest->type == 'email') {
            if (! $configs['site_register_email']) {
                throw new ApiException(34202);
            }

            $checkEmail = ValidationUtility::disposableEmail($dtoRequest->account);
            if (! $checkEmail) {
                throw new ApiException(34109);
            }
        }

        if ($dtoRequest->type == 'phone' && ! $configs['site_register_phone']) {
            throw new ApiException(34203);
        }

        $accountType = match ($dtoRequest->type) {
            'email' => 1,
            'phone' => 2,
        };

        // check code
        $checkCodeWordBody = [
            'type' => $accountType,
            'account' => $dtoRequest->account,
            'countryCode' => $dtoRequest->countryCode,
            'verifyCode' => $dtoRequest->verifyCode,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($checkCodeWordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->errorResponse();
        }

        $password = base64_decode($dtoRequest->password, true);

        $validatePassword = ValidationUtility::password($password);

        if (! $validatePassword['length']) {
            throw new ApiException(34104);
        }

        if (! $validatePassword['number']) {
            throw new ApiException(34105);
        }

        if (! $validatePassword['lowercase']) {
            throw new ApiException(34106);
        }

        if (! $validatePassword['uppercase']) {
            throw new ApiException(34107);
        }

        if (! $validatePassword['symbols']) {
            throw new ApiException(34108);
        }

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_REGISTER,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => null,
            'uid' => null,
            'objectName' => route('api.account.register'),
            'objectAction' => 'Account Register',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => $dtoRequest->deviceToken,
            'moreJson' => null,
        ];

        // add account
        $addAccountWordBody = [
            'type' => $accountType,
            'account' => $dtoRequest->account,
            'countryCode' => $dtoRequest->countryCode,
            'connectInfo' => null,
            'password' => $password,
        ];

        $fresnsAccountResp = \FresnsCmdWord::plugin('Fresns')->addAccount($addAccountWordBody);

        if ($fresnsAccountResp->isErrorResponse()) {
            // upload session log
            $sessionLog['objectAction'] = 'addAccount';
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            return $fresnsAccountResp->errorResponse();
        }

        // upload session log
        $sessionLog['aid'] = $fresnsAccountResp->getData('aid');
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        // add user
        $addUserWordBody = [
            'aid' => $fresnsAccountResp->getData('aid'),
            'nickname' => $dtoRequest->nickname,
            'username' => null,
            'password' => null,
            'avatarFid' => null,
            'avatarUrl' => null,
            'gender' => null,
            'birthday' => null,
            'timezone' => null,
            'language' => null,
        ];
        $fresnsUserResp = \FresnsCmdWord::plugin('Fresns')->addUser($addUserWordBody);

        if ($fresnsUserResp->isErrorResponse()) {
            // upload session log
            $sessionLog['type'] = SessionLog::TYPE_USER_ADD;
            $sessionLog['objectAction'] = 'addUser';
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            return $fresnsUserResp->errorResponse();
        }

        // upload session log
        $sessionLog['type'] = SessionLog::TYPE_USER_ADD;
        $sessionLog['aid'] = $fresnsAccountResp->getData('aid');
        $sessionLog['uid'] = $fresnsUserResp->getData('uid');
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        // create token
        $createTokenWordBody = [
            'platformId' => $this->platformId(),
            'aid' => $fresnsUserResp->getData('aid'),
            'uid' => $fresnsUserResp->getData('uid'),
            'expiredTime' => null,
        ];
        $fresnsTokenResponse = \FresnsCmdWord::plugin('Fresns')->createSessionToken($createTokenWordBody);

        if ($fresnsTokenResponse->isErrorResponse()) {
            return $fresnsTokenResponse->errorResponse();
        }

        // get account token
        $token['token'] = $fresnsTokenResponse->getData('token');
        $token['expiredTime'] = $fresnsTokenResponse->getData('expiredTime');
        $sessionToken['sessionToken'] = $token;

        // get account data
        $account = Account::whereAid($fresnsTokenResponse->getData('aid'))->first();

        $service = new AccountService();
        $detail = $service->accountData($account, $this->langTag(), $this->timezone());

        $data = array_merge($sessionToken, $detail);

        return $this->success($data);
    }

    // login
    public function login(Request $request)
    {
        $dtoRequest = new AccountLoginDTO($request->all());

        $accountType = match ($dtoRequest->type) {
            'email' => 1,
            'phone' => 2,
        };

        $password = base64_decode($dtoRequest->password, true);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_LOGIN,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => null,
            'uid' => null,
            'objectName' => route('api.account.login'),
            'objectAction' => 'Account Login',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => $dtoRequest->deviceToken,
            'moreJson' => null,
        ];

        // login
        $wordBody = [
            'type' => $accountType,
            'account' => $dtoRequest->account,
            'countryCode' => $dtoRequest->countryCode,
            'password' => $password,
            'verifyCode' => $dtoRequest->verifyCode,
        ];
        $fresnsResponse = \FresnsCmdWord::plugin('Fresns')->verifyAccount($wordBody);

        if ($fresnsResponse->isErrorResponse()) {
            // upload session log
            $sessionLog['aid'] = $fresnsResponse->getData('aid') ?? null;
            $sessionLog['objectAction'] = 'verifyAccount';
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            return $fresnsResponse->errorResponse();
        }

        // create token
        $createTokenWordBody = [
            'platformId' => $this->platformId(),
            'aid' => $fresnsResponse->getData('aid'),
            'uid' => null,
            'expiredTime' => null,
        ];
        $fresnsTokenResponse = \FresnsCmdWord::plugin('Fresns')->createSessionToken($createTokenWordBody);

        if ($fresnsTokenResponse->isErrorResponse()) {
            // upload session log
            $sessionLog['aid'] = $fresnsResponse->getData('aid');
            $sessionLog['objectAction'] = 'createSessionToken';
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            return $fresnsTokenResponse->errorResponse();
        }

        // upload session log
        $sessionLog['aid'] = $fresnsResponse->getData('aid');
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        // get account token
        $token['token'] = $fresnsTokenResponse->getData('token');
        $token['expiredTime'] = $fresnsTokenResponse->getData('expiredTime');
        $sessionToken['sessionToken'] = $token;

        // get account data
        $account = Account::whereAid($fresnsTokenResponse->getData('aid'))->first();

        $service = new AccountService();
        $detail = $service->accountData($account, $this->langTag(), $this->timezone());

        $data = array_merge($sessionToken, $detail);

        return $this->success($data);
    }

    // reset password
    public function resetPassword(Request $request)
    {
        $dtoRequest = new AccountResetPasswordDTO($request->all());

        $accountType = match ($dtoRequest->type) {
            'email' => 1,
            'phone' => 2,
        };

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_EDIT_PASSWORD,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => null,
            'uid' => null,
            'objectName' => route('api.account.reset.password'),
            'objectAction' => 'Account Reset Password',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreJson' => null,
        ];

        // check code
        $checkCodeWordBody = [
            'type' => $accountType,
            'account' => $dtoRequest->account,
            'countryCode' => $dtoRequest->countryCode,
            'verifyCode' => $dtoRequest->verifyCode,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($checkCodeWordBody);

        if ($fresnsResp->isErrorResponse()) {
            // upload session log
            $sessionLog['objectAction'] = "checkCode ({$accountType} {$dtoRequest->account} {$dtoRequest->countryCode})";
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            return $fresnsResp->errorResponse();
        }

        if ($dtoRequest->type == 'email') {
            $account = Account::where('email', $dtoRequest->account)->first();
        } else {
            $account = Account::where('phone', $dtoRequest->countryCode.$dtoRequest->account)->first();
        }

        if (empty($account)) {
            // upload session log
            $sessionLog['objectAction'] = "empty ({$accountType} {$dtoRequest->account} {$dtoRequest->countryCode})";
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            throw new ApiException(34301);
        }

        $newPassword = base64_decode($dtoRequest->newPassword, true);
        $validatePassword = ValidationUtility::password($newPassword);

        if (! $validatePassword['length']) {
            throw new ApiException(34104);
        }

        if (! $validatePassword['number']) {
            throw new ApiException(34105);
        }

        if (! $validatePassword['lowercase']) {
            throw new ApiException(34106);
        }

        if (! $validatePassword['uppercase']) {
            throw new ApiException(34107);
        }

        if (! $validatePassword['symbols']) {
            throw new ApiException(34108);
        }

        $dataPassword = Hash::make($newPassword);

        $account->update([
            'password' => $dataPassword,
        ]);

        // upload session log
        $sessionLog['aid'] = $account->aid;
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        return $this->success();
    }

    // detail
    public function detail()
    {
        $authAccount = $this->account();

        if (empty($authAccount)) {
            throw new ApiException(31502);
        }

        if ($authAccount->is_enable == 0) {
            throw new ApiException(34307);
        }

        $service = new AccountService();
        $data = $service->accountData($authAccount, $this->langTag(), $this->timezone());

        return $this->success($data);
    }

    // walletLogs
    public function walletLogs(Request $request)
    {
        $dtoRequest = new AccountWalletLogsDTO($request->all());

        $authAccount = $this->account();
        $langTag = $this->langTag();
        $timezone = $this->timezone();

        $status = $dtoRequest->status ?? 1;

        $walletLogQuery = AccountWalletLog::where('account_id', $authAccount->id)->isEnable($status)->orderBy('created_at', 'desc');

        if (! empty($dtoRequest->type)) {
            $typeArr = array_filter(explode(',', $dtoRequest->keys));
            $walletLogQuery->whereIn('object_type', $typeArr);
        }

        $walletLogs = $walletLogQuery->paginate($request->get('pageSize', 15));

        $logList = null;
        foreach ($walletLogs as $log) {
            $item['type'] = $log->object_type;
            $item['amount'] = $log->amount;
            $item['transactionAmount'] = $log->transaction_amount;
            $item['systemFee'] = $log->system_fee;
            $item['openingBalance'] = $log->opening_balance;
            $item['closingBalance'] = $log->closing_balance;
            $info['createTime'] = DateHelper::fresnsFormatDateTime($log->created_at, $timezone, $langTag);
            $info['createTimeFormat'] = DateHelper::fresnsFormatTime($log->created_at, $langTag);
            $item['remark'] = $log->remark;
            $item['pluginUnikey'] = $log->object_unikey;
            $item['status'] = (bool) $log->is_enable;
            $logList[] = $item;
        }

        return $this->fresnsPaginate($logList, $walletLogs->total(), $walletLogs->perPage());
    }

    // verifyIdentity
    public function verifyIdentity(Request $request)
    {
        $dtoRequest = new AccountVerifyIdentityDTO($request->all());
        $authAccount = $this->account();

        if ($dtoRequest->type == 'email') {
            $accountName = $authAccount->email;
        } else {
            $accountName = $authAccount->phone;
        }

        $codeType = match ($dtoRequest->type) {
            'email' => 1,
            'sms' => 2,
        };

        $term = [
            'type' => $codeType,
            'account' => $accountName,
            'code' => $dtoRequest->verifyCode,
            'is_enable' => 1,
        ];
        $verifyInfo = VerifyCode::where($term)->where('expired_at', '>', date('Y-m-d H:i:s'))->first();

        if (! $verifyInfo) {
            throw new ApiException(33203);
        }

        return $this->success();
    }

    // edit
    public function edit(Request $request)
    {
        $dtoRequest = new AccountEditDTO($request->all());
        $authAccount = $this->account();

        // check code
        if ($dtoRequest->verifyCode) {
            if ($dtoRequest->codeType == 'email') {
                $codeWordBody = [
                    'type' => 1,
                    'account' => $authAccount->email,
                    'countryCode' => null,
                    'verifyCode' => $dtoRequest->verifyCode,
                ];
            } else {
                $codeWordBody = [
                    'type' => 2,
                    'account' => $authAccount->pure_phone,
                    'countryCode' => $authAccount->country_code,
                    'verifyCode' => $dtoRequest->verifyCode,
                ];
            }

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getOrigin();
            }
        }

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_EDIT_DATA,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => $authAccount->aid,
            'uid' => null,
            'objectName' => route('api.account.edit'),
            'objectAction' => 'Account Edit Data',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreJson' => null,
        ];

        // edit email
        if ($dtoRequest->editEmail) {
            $checkEmail = ValidationUtility::disposableEmail($dtoRequest->editEmail);
            if (! $checkEmail) {
                throw new ApiException(34109);
            }

            if ($authAccount->email && empty($dtoRequest->verifyCode)) {
                throw new ApiException(33202);
            }

            $codeWordBody = [
                'type' => 1,
                'account' => $dtoRequest->editEmail,
                'countryCode' => null,
                'verifyCode' => $dtoRequest->newVerifyCode,
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getOrigin();
            }

            $checkEmail = Account::where('email', $dtoRequest->editEmail)->first();
            if ($checkEmail) {
                throw new ApiException(34205);
            }

            $authAccount->update([
                'email' => $dtoRequest->editEmail,
            ]);
        }

        // edit phone
        if ($dtoRequest->editPhone) {
            if ($authAccount->phone && empty($dtoRequest->verifyCode)) {
                throw new ApiException(33202);
            }

            $codeWordBody = [
                'type' => 2,
                'account' => $dtoRequest->editPhone,
                'countryCode' => $dtoRequest->editCountryCode,
                'verifyCode' => $dtoRequest->newVerifyCode,
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getOrigin();
            }

            $newPhone = $dtoRequest->editCountryCode.$dtoRequest->editPhone;
            $checkPhone = Account::where('phone', $newPhone)->first();
            if ($checkPhone) {
                throw new ApiException(34206);
            }

            $authAccount->update([
                'country_code' => $dtoRequest->editCountryCode,
                'pure_phone' => $dtoRequest->editPhone,
                'phone' => $newPhone,
            ]);
        }

        // edit password
        if ($dtoRequest->editPassword) {
            if (empty($dtoRequest->password) && empty($dtoRequest->verifyCode)) {
                throw new ApiException(31410);
            }

            if ($dtoRequest->password) {
                $password = base64_decode($dtoRequest->password, true);

                if (! Hash::check($password, $authAccount->password)) {
                    // upload session log
                    $sessionLog['type'] = SessionLog::TYPE_ACCOUNT_EDIT_PASSWORD;
                    $sessionLog['objectAction'] = 'checkPassword';
                    $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
                    \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

                    throw new ApiException(34304);
                }
            }

            $newPassword = base64_decode($dtoRequest->editPassword, true);
            $authAccount->update([
                'password' => Hash::make($newPassword),
            ]);

            // upload session log
            $sessionLog['type'] = SessionLog::TYPE_ACCOUNT_EDIT_PASSWORD;
            $sessionLog['objectAction'] = 'Account Edit Password';
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);
        }

        // edit wallet password
        if ($dtoRequest->editWalletPassword) {
            if (empty($dtoRequest->walletPassword) && empty($dtoRequest->verifyCode)) {
                throw new ApiException(31410);
            }

            $wallet = AccountWallet::where('account_id', $authAccount->id)->first();
            if (empty($wallet)) {
                throw new ApiException(34501);
            }

            if ($dtoRequest->walletPassword) {
                $walletPassword = base64_decode($dtoRequest->walletPassword, true);

                if (! Hash::check($walletPassword, $wallet->password)) {
                    // upload session log
                    $sessionLog['type'] = SessionLog::TYPE_WALLET_EDIT_PASSWORD;
                    $sessionLog['objectAction'] = 'checkPassword';
                    $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
                    \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

                    throw new ApiException(34502);
                }
            }

            $newWalletPassword = base64_decode($dtoRequest->editWalletPassword, true);
            $wallet->update([
                'password' => Hash::make($newWalletPassword),
            ]);

            // upload session log
            $sessionLog['type'] = SessionLog::TYPE_WALLET_EDIT_PASSWORD;
            $sessionLog['objectAction'] = 'Account Edit Wallet Password';
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);
        }

        // edit last login time
        if ($dtoRequest->editLastLoginTime) {
            $authAccount->update([
                'last_login_at' => DateHelper::fresnsDatabaseCurrentDateTime(),
            ]);
        }

        // upload session log
        $sessionLog['type'] = SessionLog::TYPE_ACCOUNT_EDIT_DATA;
        $sessionLog['objectAction'] = 'Account Edit Data';
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        CacheHelper::forgetApiAccount($authAccount->aid);
        CacheHelper::forgetApiUser($this->user()?->uid);

        return $this->success();
    }

    // logout
    public function logout()
    {
        $authAccount = $this->account();
        $authUser = $this->user();

        $condition = [
            'platform_id' => $this->platformId(),
            'account_id' => $authAccount->id,
            'user_id' => $authUser?->id,
        ];
        SessionToken::where($condition)->forceDelete();

        CacheHelper::forgetApiAccount($authAccount->aid);
        CacheHelper::forgetApiUser($authUser?->uid);

        return $this->success();
    }

    // applyDelete
    public function applyDelete(Request $request)
    {
        $dtoRequest = new AccountApplyDeleteDTO($request->all());
        $authAccount = $this->account();

        $todoDay = ConfigHelper::fresnsConfigByItemKey('delete_account_todo');

        if ($dtoRequest->password) {
            $password = base64_decode($dtoRequest->password, true);

            if (! Hash::check($password, $authAccount->password)) {
                throw new ApiException(34304);
            }

            $authAccount->update([
                'wait_delete' => 1,
                'wait_delete_at' => now()->addDays($todoDay),
            ]);
        } else {
            if ($dtoRequest->codeType == 'email') {
                $codeWordBody = [
                    'type' => 1,
                    'account' => $authAccount->email,
                    'countryCode' => null,
                    'verifyCode' => $dtoRequest->verifyCode,
                ];
            } else {
                $codeWordBody = [
                    'type' => 2,
                    'account' => $authAccount->pure_phone,
                    'countryCode' => $authAccount->country_code,
                    'verifyCode' => $dtoRequest->verifyCode,
                ];
            }

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);
            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getOrigin();
            }

            $authAccount->update([
                'wait_delete' => 1,
                'wait_delete_at' => now()->addDays($todoDay),
            ]);
        }

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_DELETE,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => $authAccount->aid,
            'uid' => null,
            'objectName' => route('api.account.apply.delete'),
            'objectAction' => 'Apply Delete Account',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreJson' => null,
        ];
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        CacheHelper::forgetApiAccount($authAccount->aid);

        return $this->success([
            'day' => $todoDay,
            'dateTime' => DateHelper::fresnsDateTimeByTimezone($authAccount->wait_delete_at, $this->timezone(), $this->langTag()),
        ]);
    }

    // revokeDelete
    public function revokeDelete()
    {
        $authAccount = $this->account();

        $authAccount->update([
            'wait_delete' => 0,
            'wait_delete_at' => null,
        ]);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_DELETE,
            'pluginUnikey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'langTag' => $this->langTag(),
            'aid' => $authAccount->aid,
            'uid' => null,
            'objectName' => route('api.account.revoke.delete'),
            'objectAction' => 'Revoke Delete Account',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreJson' => null,
        ];
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        CacheHelper::forgetApiAccount($authAccount->aid);
    }
}
