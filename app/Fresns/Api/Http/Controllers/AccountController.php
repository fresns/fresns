<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Http\DTO\AccountApplyDeleteDTO;
use App\Fresns\Api\Http\DTO\AccountEditDTO;
use App\Fresns\Api\Http\DTO\AccountLoginDTO;
use App\Fresns\Api\Http\DTO\AccountRegisterDTO;
use App\Fresns\Api\Http\DTO\AccountResetPasswordDTO;
use App\Fresns\Api\Http\DTO\AccountVerifyIdentityDTO;
use App\Fresns\Api\Http\DTO\AccountWalletLogsDTO;
use App\Helpers\DateHelper;
use App\Fresns\Api\Services\AccountService;
use App\Exceptions\ApiException;
use App\Fresns\Api\Services\HeaderService;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Account;
use App\Models\AccountConnect;
use App\Models\AccountWallet;
use App\Models\AccountWalletLog;
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
        $headers = HeaderService::getHeaders();

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
            return $fresnsAccountResp->errorResponse();
        }

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
            return $fresnsUserResp->errorResponse();
        }

        // create token
        $createTokenWordBody = [
            'platformId' => $headers['platformId'],
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
        $data['sessionToken'] = $token;

        // get account data
        $account = Account::whereAid($fresnsTokenResponse->getData('aid'))->first();

        $service = new AccountService();
        $data[] = $service->accountData($account, $headers['langTag'], $headers['timezone']);

        return $this->success($data);
    }

    // login
    public function login(Request $request)
    {
        $dtoRequest = new AccountLoginDTO($request->all());
        $headers = HeaderService::getHeaders();

        $accountType = match ($dtoRequest->type) {
            'email' => 1,
            'phone' => 2,
        };

        $password = base64_decode($dtoRequest->password, true);

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
            return $fresnsResponse->errorResponse();
        }

        // create token
        $createTokenWordBody = [
            'platformId' => $headers['platformId'],
            'aid' => $fresnsResponse->getData('aid'),
            'uid' => null,
            'expiredTime' => null,
        ];
        $fresnsTokenResponse = \FresnsCmdWord::plugin('Fresns')->createSessionToken($createTokenWordBody);

        if ($fresnsTokenResponse->isErrorResponse()) {
            return $fresnsTokenResponse->errorResponse();
        }

        // get account token
        $token['token'] = $fresnsTokenResponse->getData('token');
        $token['expiredTime'] = $fresnsTokenResponse->getData('expiredTime');
        $data['sessionToken'] = $token;

        // get account data
        $account = Account::whereAid($fresnsTokenResponse->getData('aid'))->first();

        $service = new AccountService();
        $data[] = $service->accountData($account, $headers['langTag'], $headers['timezone']);

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

        if ($dtoRequest->type == 'email') {
            $account = Account::where('email', $dtoRequest->account)->first();
        } else {
            $account = Account::where('phone', $dtoRequest->countryCode.$dtoRequest->account)->first();
        }

        if (empty($account)) {
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

        return $this->success();
    }

    // detail
    public function detail()
    {
        $headers = HeaderService::getHeaders();

        $account = Account::whereAid($headers['aid'])->first();
        if (empty($account)) {
            throw new ApiException(31502);
        }

        $service = new AccountService();
        $data = $service->accountData($account, $headers['langTag'], $headers['timezone']);

        return $this->success($data);
    }

    // walletLogs
    public function walletLogs(Request $request)
    {
        $dtoRequest = new AccountWalletLogsDTO($request->all());
        $headers = HeaderService::getHeaders();

        $accountId = PrimaryHelper::fresnsAccountIdByAid($headers['aid']);
        $status = $dtoRequest->status ?? 1;

        $walletLogQuery = AccountWalletLog::where('account_id', $accountId)->isEnable($status)->orderBy('created_at', 'desc');

        if (!empty($dtoRequest->type)) {
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
            $info['createTime'] = DateHelper::fresnsFormatDateTime($log->created_at, $headers['timezone'], $headers['langTag']);
            $info['createTimeFormat'] = DateHelper::fresnsFormatTime($log->created_at, $headers['langTag']);
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
        $headers = HeaderService::getHeaders();

        if ($dtoRequest->type == 'email') {
            $account = Account::whereAid($headers['aid'])->value('email');
        } else {
            $account = Account::whereAid($headers['aid'])->value('phone');
        }

        $codeType = match ($dtoRequest->type) {
            'email' => 1,
            'sms' => 2,
        };

        $term = [
            'type' => $codeType,
            'account' => $account,
            'code' => $dtoRequest->verifyCode,
            'is_enable' => 1,
        ];
        $verifyInfo = VerifyCode::where($term)->where('expired_at', '>', date('Y-m-d H:i:s'))->first();

        if (! $verifyInfo) {
            throw new ApiException(33104);
        }

        return $this->success();
    }

    // edit
    public function edit(Request $request)
    {
        $dtoRequest = new AccountEditDTO($request->all());
        $headers = HeaderService::getHeaders();

        $account = Account::whereAid($headers['aid'])->first();

        // check code
        if ($dtoRequest->verifyCode) {
            if ($dtoRequest->codeType == 'email') {
                $codeWordBody = [
                    'type' => 1,
                    'account' => $account->email,
                    'countryCode' => null,
                    'verifyCode' => $dtoRequest->verifyCode,
                ];
            } else {
                $codeWordBody = [
                    'type' => 2,
                    'account' => $account->pure_phone,
                    'countryCode' => $account->country_code,
                    'verifyCode' => $dtoRequest->verifyCode,
                ];
            }

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getOrigin();
            }
        }

        // edit email
        if ($dtoRequest->editEmail) {
            $checkEmail = ValidationUtility::disposableEmail($dtoRequest->editEmail);
            if (! $checkEmail) {
                throw new ApiException(34109);
            }

            if ($account->email && empty($dtoRequest->verifyCode)) {
                throw new ApiException(33103);
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

            $account->update([
                'email' => $dtoRequest->editEmail,
            ]);
        }

        // edit phone
        if ($dtoRequest->editPhone) {
            if ($account->phone && empty($dtoRequest->verifyCode)) {
                throw new ApiException(33103);
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

            $account->update([
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

                if (! Hash::check($password, $account->password)) {
                    throw new ApiException(34304);
                }
            }

            $newPassword = base64_decode($dtoRequest->editPassword, true);
            $account->update([
                'password' => Hash::make($newPassword),
            ]);
        }

        // edit wallet password
        if ($dtoRequest->editWalletPassword) {
            if (empty($dtoRequest->walletPassword) && empty($dtoRequest->verifyCode)) {
                throw new ApiException(31410);
            }

            $wallet = AccountWallet::where('account_id', $account->id)->first();
            if (empty($wallet)) {
                throw new ApiException(34501);
            }

            if ($dtoRequest->walletPassword) {
                $walletPassword = base64_decode($dtoRequest->walletPassword, true);

                if (! Hash::check($walletPassword, $wallet->password)) {
                    throw new ApiException(34502);
                }
            }

            $newWalletPassword = base64_decode($dtoRequest->editWalletPassword, true);
            $wallet->update([
                'password' => Hash::make($newWalletPassword),
            ]);
        }

        // edit last login time
        if ($dtoRequest->editLastLoginTime) {
            $account->update([
                'last_login_at' => DateHelper::fresnsDatabaseCurrentDateTime(),
            ]);
        }

        return $this->success();
    }

    // logout
    public function logout()
    {
        $headers = HeaderService::getHeaders();

        $accountId = PrimaryHelper::fresnsAccountIdByAid($headers['aid']);
        $userId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);

        $condition = [
            'platform_id' => $headers['platformId'],
            'account_id' => $accountId,
            'user_id' => $userId,
        ];
        SessionToken::where($condition)->forceDelete();

        return $this->success();
    }

    // applyDelete
    public function applyDelete(Request $request)
    {
        $dtoRequest = new AccountApplyDeleteDTO($request->all());
        $headers = HeaderService::getHeaders();

        $account = Account::whereAid($headers['aid'])->first();
        $todoDay = ConfigHelper::fresnsConfigByItemKey('delete_account_todo');
        $dbDateTime = DateHelper::fresnsDatabaseCurrentDateTime();
        $todoTime = date('Y-m-d H:i:s', strtotime("$dbDateTime +$todoDay day"));

        if ($dtoRequest->password) {
            $password = base64_decode($dtoRequest->password, true);

            if (! Hash::check($password, $account->password)) {
                throw new ApiException(34304);
            }

            $account->update([
                'wait_delete' => 1,
                'wait_delete_at' => $todoTime,
            ]);
        } else {
            if ($dtoRequest->codeType == 'email') {
                $codeWordBody = [
                    'type' => 1,
                    'account' => $account->email,
                    'countryCode' => null,
                    'verifyCode' => $dtoRequest->verifyCode,
                ];
            } else {
                $codeWordBody = [
                    'type' => 2,
                    'account' => $account->pure_phone,
                    'countryCode' => $account->country_code,
                    'verifyCode' => $dtoRequest->verifyCode,
                ];
            }

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);
            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getOrigin();
            }

            $account->update([
                'wait_delete' => 1,
                'wait_delete_at' => $todoTime,
            ]);
        }

        return $this->success([
            'day' => $todoDay,
            'dateTime' => DateHelper::fresnsDateTimeByTimezone($todoTime, $headers['timezone'], $headers['langTag']),
        ]);
    }

    // revokeDelete
    public function revokeDelete()
    {
        $headers = HeaderService::getHeaders();

        $account = Account::whereAid($headers['aid'])->first();

        $account->update([
            'wait_delete' => 0,
            'wait_delete_at' => null,
        ]);
    }
}
