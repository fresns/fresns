<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\AccountApplyDeleteDTO;
use App\Fresns\Api\Http\DTO\AccountEditDTO;
use App\Fresns\Api\Http\DTO\AccountEmailDTO;
use App\Fresns\Api\Http\DTO\AccountLoginDTO;
use App\Fresns\Api\Http\DTO\AccountPhoneDTO;
use App\Fresns\Api\Http\DTO\AccountRegisterDTO;
use App\Fresns\Api\Http\DTO\AccountResetPasswordDTO;
use App\Fresns\Api\Http\DTO\AccountVerifyIdentityDTO;
use App\Fresns\Api\Http\DTO\AccountWalletLogsDTO;
use App\Fresns\Api\Services\AccountService;
use App\Fresns\Api\Services\UserService;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\AccountWallet;
use App\Models\AccountWalletLog;
use App\Models\BlockWord;
use App\Models\PluginUsage;
use App\Models\SessionLog;
use App\Models\SessionToken;
use App\Models\VerifyCode;
use App\Utilities\ExtendUtility;
use App\Utilities\SubscribeUtility;
use App\Utilities\ValidationUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
            'site_email_register',
            'site_phone_register',
        ]);

        if ($configs['site_mode'] == 'private' || ! $configs['site_public_status'] || $configs['site_public_service']) {
            throw new ApiException(34201);
        }

        if ($dtoRequest->type == 'email') {
            if (! $configs['site_email_register']) {
                throw new ApiException(34202);
            }

            new AccountEmailDTO($request->all());

            $checkEmail = ValidationUtility::disposableEmail($dtoRequest->account);
            if (! $checkEmail) {
                throw new ApiException(34110);
            }
        }

        if ($dtoRequest->type == 'phone') {
            new AccountPhoneDTO($request->all());

            if (! $configs['site_phone_register']) {
                throw new ApiException(34203);
            }
        }

        $accountType = match ($dtoRequest->type) {
            'email' => 1,
            'phone' => 2,
        };

        $password = base64_decode($dtoRequest->password, true);

        $validatePassword = ValidationUtility::password($password);

        if (! $validatePassword['length']) {
            throw new ApiException(34105);
        }

        if (! $validatePassword['number']) {
            throw new ApiException(34106);
        }

        if (! $validatePassword['lowercase']) {
            throw new ApiException(34107);
        }

        if (! $validatePassword['uppercase']) {
            throw new ApiException(34108);
        }

        if (! $validatePassword['symbols']) {
            throw new ApiException(34109);
        }

        // check nickname
        $nicknameIsEmpty = Str::of($dtoRequest->nickname)->trim()->isEmpty();
        if ($nicknameIsEmpty) {
            throw new ApiException(35107);
        }

        $nickname = Str::of($dtoRequest->nickname)->trim();

        $validateNickname = ValidationUtility::nickname($nickname);

        if (! $validateNickname['formatString'] || ! $validateNickname['formatSpace']) {
            throw new ApiException(35107);
        }

        if (! $validateNickname['minLength']) {
            throw new ApiException(35109);
        }

        if (! $validateNickname['maxLength']) {
            throw new ApiException(35108);
        }

        if (! $validateNickname['use']) {
            throw new ApiException(35111);
        }

        if (! $validateNickname['banName']) {
            throw new ApiException(35110);
        }

        $blockWords = BlockWord::where('user_mode', 2)->get('word', 'replace_word');

        $newNickname = str_ireplace($blockWords->pluck('word')->toArray(), $blockWords->pluck('replace_word')->toArray(), $nickname);

        // check code
        $checkCodeWordBody = [
            'type' => $accountType,
            'account' => $dtoRequest->account,
            'countryCode' => $dtoRequest->countryCode,
            'verifyCode' => $dtoRequest->verifyCode,
            'templateId' => VerifyCode::TEMPLATE_REGISTER,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($checkCodeWordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->errorResponse();
        }

        // create account
        $createAccountWordBody = [
            'type' => $accountType,
            'account' => $dtoRequest->account,
            'countryCode' => $dtoRequest->countryCode,
            'password' => $password,
            'createUser' => true,
            'userInfo' => [
                'nickname' => $newNickname,
            ],
        ];

        $createAccountResp = \FresnsCmdWord::plugin('Fresns')->createAccount($createAccountWordBody);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_REGISTER,
            'fskey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => null,
            'uid' => null,
            'objectName' => \request()->path(),
            'objectAction' => 'Account Register',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => $dtoRequest->deviceToken,
            'moreInfo' => null,
        ];

        if ($createAccountResp->isErrorResponse()) {
            // upload session log
            $sessionLog['objectAction'] = 'createAccount';
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;

            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            return $createAccountResp->errorResponse();
        }

        // upload session log
        $sessionLog['aid'] = $createAccountResp->getData('aid');
        $sessionLog['uid'] = $createAccountResp->getData('uid');
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        // create token
        $createTokenWordBody = [
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'aid' => $createAccountResp->getData('aid'),
            'deviceToken' => $dtoRequest->deviceToken,
            'expiredTime' => null,
        ];
        $fresnsTokenResponse = \FresnsCmdWord::plugin('Fresns')->createAccountToken($createTokenWordBody);

        if ($fresnsTokenResponse->isErrorResponse()) {
            return $fresnsTokenResponse->errorResponse();
        }

        // create token session log
        $tokenSessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_LOGIN,
            'fskey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => $createAccountResp->getData('aid'),
            'uid' => $createAccountResp->getData('uid'),
            'objectName' => \request()->path(),
            'objectAction' => 'Login after account registration',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => $fresnsTokenResponse->getData('aidTokenId'),
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => $dtoRequest->deviceToken,
            'moreInfo' => null,
        ];
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($tokenSessionLog);

        // get account data
        $account = Account::whereAid($createAccountResp->getData('aid'))->first();

        $service = new AccountService();
        $data = [
            'sessionToken' => [
                'aid' => $fresnsTokenResponse->getData('aid'),
                'token' => $fresnsTokenResponse->getData('aidToken'),
                'expiredHours' => $fresnsTokenResponse->getData('expiredHours'),
                'expiredDays' => $fresnsTokenResponse->getData('expiredDays'),
                'expiredDateTime' => $fresnsTokenResponse->getData('expiredDateTime'),
            ],
            'detail' => $service->accountData($account, $this->langTag(), $this->timezone()),
        ];

        // notify subscribe
        SubscribeUtility::notifyAccountAndUserLogin($account->id, $data['sessionToken'], $data['detail']);

        return $this->success($data);
    }

    // login
    public function login(Request $request)
    {
        $dtoRequest = new AccountLoginDTO($request->all());

        if ($dtoRequest->type == 'email') {
            new AccountEmailDTO($request->all());
        } else {
            new AccountPhoneDTO($request->all());
        }

        $accountType = match ($dtoRequest->type) {
            'email' => 1,
            'phone' => 2,
        };

        $password = base64_decode($dtoRequest->password, true);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_LOGIN,
            'fskey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => null,
            'uid' => null,
            'objectName' => \request()->path(),
            'objectAction' => 'Account Login',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => $dtoRequest->deviceToken,
            'moreInfo' => null,
        ];

        // login
        $wordBody = [
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'type' => $accountType,
            'account' => $dtoRequest->account,
            'countryCode' => $dtoRequest->countryCode,
            'password' => $password,
            'verifyCode' => $dtoRequest->verifyCode,
        ];
        $fresnsResponse = \FresnsCmdWord::plugin('Fresns')->verifyAccount($wordBody);

        // verifyCode login
        if ($fresnsResponse->isErrorResponse()) {
            // upload session log
            $sessionLog['aid'] = $fresnsResponse->getData('aid') ?? null;
            $sessionLog['objectAction'] = 'verifyAccount';
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            $siteConfigs = ConfigHelper::fresnsConfigByItemKeys([
                'site_login_or_register',
                'site_email_register',
                'site_phone_register',
            ]);

            if (! $siteConfigs['site_login_or_register'] || empty($dtoRequest->verifyCode)) {
                return $fresnsResponse->errorResponse();
            }

            if ($dtoRequest->type == 'email') {
                if (! $siteConfigs['site_email_register']) {
                    return $fresnsResponse->errorResponse();
                }

                $checkEmail = ValidationUtility::disposableEmail($dtoRequest->account);
                if (! $checkEmail) {
                    throw new ApiException(34110);
                }
            }

            if ($dtoRequest->type == 'phone' && ! $siteConfigs['site_phone_register']) {
                return $fresnsResponse->errorResponse();
            }

            // check code
            $wordBody['templateId'] = VerifyCode::TEMPLATE_LOGIN;
            $fresnsCheckCodeResp = \FresnsCmdWord::plugin('Fresns')->checkCode($wordBody);

            if ($fresnsCheckCodeResp->isErrorResponse()) {
                return $fresnsCheckCodeResp->errorResponse();
            }

            // create account
            $createAccountWordBody = [
                'type' => $accountType,
                'account' => $dtoRequest->account,
                'countryCode' => $dtoRequest->countryCode,
                'password' => $password,
                'createUser' => true,
            ];

            $createAccountResp = \FresnsCmdWord::plugin('Fresns')->createAccount($createAccountWordBody);

            if ($createAccountResp->isErrorResponse()) {
                // upload session log
                $sessionLog['type'] = SessionLog::TYPE_ACCOUNT_REGISTER;
                $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;

                \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

                return $createAccountResp->errorResponse();
            }

            // upload session log
            $sessionLog['type'] = SessionLog::TYPE_ACCOUNT_REGISTER;
            $sessionLog['aid'] = $createAccountResp->getData('aid');
            $sessionLog['uid'] = $createAccountResp->getData('uid');
            $sessionLog['objectAction'] = 'No account is automatically registered when the verify code is logged in';
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);
        }

        // aid
        $aid = $fresnsResponse->getData('aid') ?? $createAccountResp->getData('aid');

        // create token
        $createTokenWordBody = [
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'aid' => $aid,
            'deviceToken' => $dtoRequest->deviceToken,
            'expiredTime' => null,
        ];
        $fresnsTokenResponse = \FresnsCmdWord::plugin('Fresns')->createAccountToken($createTokenWordBody);

        if ($fresnsTokenResponse->isErrorResponse()) {
            // upload session log
            $sessionLog['aid'] = $aid;
            $sessionLog['objectAction'] = 'createAccountToken';
            $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

            return $fresnsTokenResponse->errorResponse();
        }

        // upload session log
        $sessionLog['aid'] = $aid;
        $sessionLog['objectOrderId'] = $fresnsResponse->getData('aidTokenId');
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        // get account data
        $account = Account::whereAid($aid)->first();

        $service = new AccountService();
        $data = [
            'sessionToken' => [
                'aid' => $fresnsTokenResponse->getData('aid'),
                'token' => $fresnsTokenResponse->getData('aidToken'),
                'expiredHours' => $fresnsTokenResponse->getData('expiredHours'),
                'expiredDays' => $fresnsTokenResponse->getData('expiredDays'),
                'expiredDateTime' => $fresnsTokenResponse->getData('expiredDateTime'),
            ],
            'detail' => $service->accountData($account, $this->langTag(), $this->timezone()),
        ];

        // notify subscribe
        SubscribeUtility::notifyAccountAndUserLogin($account->id, $data['sessionToken'], $data['detail']);

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

        // check new password
        $newPassword = base64_decode($dtoRequest->newPassword, true);
        $validatePassword = ValidationUtility::password($newPassword);

        if (! $validatePassword['length']) {
            throw new ApiException(34105);
        }

        if (! $validatePassword['number']) {
            throw new ApiException(34106);
        }

        if (! $validatePassword['lowercase']) {
            throw new ApiException(34107);
        }

        if (! $validatePassword['uppercase']) {
            throw new ApiException(34108);
        }

        if (! $validatePassword['symbols']) {
            throw new ApiException(34109);
        }

        $dataPassword = Hash::make($newPassword);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_EDIT_PASSWORD,
            'fskey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => null,
            'uid' => null,
            'objectName' => \request()->path(),
            'objectAction' => 'Account Reset Password',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreInfo' => null,
        ];

        // check code
        $checkCodeWordBody = [
            'type' => $accountType,
            'account' => $dtoRequest->account,
            'countryCode' => $dtoRequest->countryCode,
            'verifyCode' => $dtoRequest->verifyCode,
            'templateId' => VerifyCode::TEMPLATE_RESET_LOGIN_PASSWORD,
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

        $items = [
            'walletRecharges' => ExtendUtility::getExtendsByEveryone(PluginUsage::TYPE_WALLET_RECHARGE, null, null, $this->langTag()),
            'walletWithdraws' => ExtendUtility::getExtendsByEveryone(PluginUsage::TYPE_WALLET_WITHDRAW, null, null, $this->langTag()),
        ];

        $service = new AccountService();
        $detail = $service->accountData($authAccount, $this->langTag(), $this->timezone());

        $data = [
            'items' => $items,
            'detail' => $detail,
        ];

        return $this->success($data);
    }

    // walletLogs
    public function walletLogs(Request $request)
    {
        $dtoRequest = new AccountWalletLogsDTO($request->all());

        $authAccount = $this->account();
        $authUser = $this->user();
        $langTag = $this->langTag();
        $timezone = $this->timezone();

        $walletLogQuery = AccountWalletLog::with(['user'])->where('account_id', $authAccount->id);

        $walletLogQuery->orderByDesc(DB::raw('COALESCE(success_at, created_at)'));

        if ($dtoRequest->state) {
            $walletLogQuery->where('state', $dtoRequest->state);
        }

        if ($dtoRequest->type) {
            $typeArr = array_filter(explode(',', $dtoRequest->keys));
            $walletLogQuery->whereIn('type', $typeArr);
        }

        $walletLogs = $walletLogQuery->paginate($dtoRequest->pageSize ?? 15);

        $service = new UserService();

        $logList = [];
        foreach ($walletLogs as $log) {
            $datetime = $log->success_at ?? $log->created_at;

            $item['type'] = $log->type;
            $item['fskey'] = $log->plugin_fskey;
            $item['transactionId'] = $log->transaction_id;
            $item['transactionCode'] = $log->transaction_code;
            $item['amountTotal'] = $log->amount_total;
            $item['transactionAmount'] = $log->transaction_amount;
            $item['systemFee'] = $log->system_fee;
            $item['openingBalance'] = $log->opening_balance;
            $item['closingBalance'] = $log->closing_balance;
            $item['user'] = $log?->user ? $service->userData($log?->user, 'list', $langTag, $timezone, $authUser?->id) : null;
            $item['remark'] = $log->remark;
            $item['datetime'] = DateHelper::fresnsDateTimeByTimezone($datetime, $timezone, $langTag);
            $item['datetimeFormat'] = DateHelper::fresnsFormatDateTime($datetime, $timezone, $langTag);
            $item['timeAgo'] = DateHelper::fresnsHumanReadableTime($datetime, $langTag);
            $item['state'] = $log->state;

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
            'is_enabled' => true,
        ];
        $verifyInfo = VerifyCode::where($term)->where('expired_at', '>', now())->first();

        if (! $verifyInfo) {
            throw new ApiException(33203);
        }

        return $this->success();
    }

    // edit
    public function edit(Request $request)
    {
        $dtoRequest = new AccountEditDTO($request->all());
        if ($dtoRequest->isEmpty()) {
            throw new ApiException(30001);
        }

        $authAccount = $this->account();

        if (! $authAccount->is_enabled) {
            throw new ApiException(34307);
        }

        $authUser = $this->user();
        if ($authUser && ! $authUser?->is_enabled) {
            throw new ApiException(35202);
        }

        // check code
        $codeWordBody = match ($dtoRequest->codeType) {
            'email' => [
                'type' => 1,
                'account' => $authAccount->email,
                'countryCode' => null,
                'verifyCode' => $dtoRequest->verifyCode,
                'templateId' => VerifyCode::TEMPLATE_CHANGE,
            ],
            'sms' => [
                'type' => 2,
                'account' => $authAccount->pure_phone,
                'countryCode' => $authAccount->country_code,
                'verifyCode' => $dtoRequest->verifyCode,
                'templateId' => VerifyCode::TEMPLATE_CHANGE,
            ],
            default => null,
        };

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_EDIT_DATA,
            'fskey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => $authAccount->aid,
            'uid' => null,
            'objectName' => \request()->path(),
            'objectAction' => 'Account Edit Data',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => $dtoRequest->deviceToken,
            'moreInfo' => null,
        ];

        // edit email
        if ($dtoRequest->newEmail) {
            $checkDisposableEmail = ValidationUtility::disposableEmail($dtoRequest->newEmail);
            if (! $checkDisposableEmail) {
                throw new ApiException(34110);
            }

            if ($authAccount->email && empty($dtoRequest->verifyCode)) {
                throw new ApiException(33202);
            }

            $checkEmail = Account::where('email', $dtoRequest->newEmail)->first();
            if ($checkEmail) {
                throw new ApiException(34205);
            }

            if ($dtoRequest->verifyCode) {
                $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);

                if ($fresnsResp->isErrorResponse()) {
                    return $fresnsResp->getOrigin();
                }
            }

            $newCodeWordBody = [
                'type' => 1,
                'account' => $dtoRequest->newEmail,
                'countryCode' => null,
                'verifyCode' => $dtoRequest->newVerifyCode,
                'templateId' => $authAccount->email ? VerifyCode::TEMPLATE_EDIT : VerifyCode::TEMPLATE_CHANGE,
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($newCodeWordBody);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getOrigin();
            }

            $authAccount->fill([
                'email' => $dtoRequest->newEmail,
            ]);
        }

        // edit phone
        if ($dtoRequest->newPhone) {
            if ($authAccount->phone && empty($dtoRequest->verifyCode)) {
                throw new ApiException(33202);
            }

            if ($dtoRequest->verifyCode) {
                $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);

                if ($fresnsResp->isErrorResponse()) {
                    return $fresnsResp->getOrigin();
                }
            }

            $newCodeWordBody = [
                'type' => 2,
                'account' => $dtoRequest->newPhone,
                'countryCode' => $dtoRequest->newCountryCode,
                'verifyCode' => $dtoRequest->newVerifyCode,
                'templateId' => $authAccount->phone ? VerifyCode::TEMPLATE_EDIT : VerifyCode::TEMPLATE_CHANGE,
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($newCodeWordBody);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getOrigin();
            }

            $newPhone = $dtoRequest->newCountryCode.$dtoRequest->newPhone;
            $checkPhone = Account::where('phone', $newPhone)->first();
            if ($checkPhone) {
                throw new ApiException(34206);
            }

            $authAccount->fill([
                'country_code' => $dtoRequest->newCountryCode,
                'pure_phone' => $dtoRequest->newPhone,
                'phone' => $newPhone,
            ]);
        }

        // edit password
        if ($dtoRequest->newPassword) {
            if (empty($dtoRequest->currentPassword) && empty($dtoRequest->verifyCode)) {
                throw new ApiException(34111);
            }

            $newPassword = base64_decode($dtoRequest->newPassword, true);
            $validatePassword = ValidationUtility::password($newPassword);

            if (! $validatePassword['length']) {
                throw new ApiException(34105);
            }

            if (! $validatePassword['number']) {
                throw new ApiException(34106);
            }

            if (! $validatePassword['lowercase']) {
                throw new ApiException(34107);
            }

            if (! $validatePassword['uppercase']) {
                throw new ApiException(34108);
            }

            if (! $validatePassword['symbols']) {
                throw new ApiException(34109);
            }

            if ($dtoRequest->verifyCode) {
                $codeWordBody['templateId'] = VerifyCode::TEMPLATE_RESET_LOGIN_PASSWORD;

                $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);

                if ($fresnsResp->isErrorResponse()) {
                    return $fresnsResp->getOrigin();
                }
            }

            if ($dtoRequest->currentPassword) {
                $currentPassword = base64_decode($dtoRequest->currentPassword, true);

                if (! Hash::check($currentPassword, $authAccount->password)) {
                    // upload session log
                    $sessionLog['type'] = SessionLog::TYPE_ACCOUNT_EDIT_PASSWORD;
                    $sessionLog['objectAction'] = 'checkPassword';
                    $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
                    \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

                    throw new ApiException(34304);
                }
            }

            $authAccount->fill([
                'password' => Hash::make($newPassword),
            ]);

            // upload session log
            $sessionLog['type'] = SessionLog::TYPE_ACCOUNT_EDIT_PASSWORD;
            $sessionLog['objectAction'] = 'Account Edit Password';
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);
        }

        // edit wallet password
        if ($dtoRequest->newWalletPassword) {
            if (empty($dtoRequest->currentWalletPassword) && empty($dtoRequest->verifyCode)) {
                throw new ApiException(34111);
            }

            $wallet = AccountWallet::where('account_id', $authAccount->id)->first();
            if (empty($wallet)) {
                throw new ApiException(34501);
            }

            if ($dtoRequest->verifyCode) {
                $codeWordBody['templateId'] = VerifyCode::TEMPLATE_RESET_WALLET_PASSWORD;

                $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);

                if ($fresnsResp->isErrorResponse()) {
                    return $fresnsResp->getOrigin();
                }
            }

            if ($dtoRequest->currentWalletPassword) {
                $currentWalletPassword = base64_decode($dtoRequest->currentWalletPassword, true);

                if (! Hash::check($currentWalletPassword, $wallet->password)) {
                    // upload session log
                    $sessionLog['type'] = SessionLog::TYPE_WALLET_EDIT_PASSWORD;
                    $sessionLog['objectAction'] = 'checkPassword';
                    $sessionLog['objectResult'] = SessionLog::STATE_FAILURE;
                    \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

                    throw new ApiException(34502);
                }
            }

            $newWalletPassword = base64_decode($dtoRequest->newWalletPassword, true);

            $wallet->update([
                'password' => Hash::make($newWalletPassword),
            ]);

            // upload session log
            $sessionLog['type'] = SessionLog::TYPE_WALLET_EDIT_PASSWORD;
            $sessionLog['objectAction'] = 'Account Edit Wallet Password';
            \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);
        }

        // edit last login time
        if ($dtoRequest->updateLastLoginTime) {
            $authAccount->fill([
                'last_login_at' => now(),
            ]);
        }

        // edit save
        if ($authAccount->isDirty()) {
            $authAccount->save();
        }

        if ($dtoRequest->disconnectPlatformId) {
            $wordBody = [
                'aid' => $authAccount->aid,
                'connectPlatformId' => $dtoRequest->disconnectPlatformId,
            ];

            $disconnectResp = \FresnsCmdWord::plugin('Fresns')->disconnectAccountConnect($wordBody);

            if ($disconnectResp->isErrorResponse()) {
                return $disconnectResp->errorResponse();
            }
        }

        // edit device token
        if ($dtoRequest->deviceToken) {
            $platformId = $this->platformId();
            $appId = $this->appId();
            $authAccountToken = $this->accountToken();

            $sessionToken = SessionToken::where('platform_id', $platformId)->where('app_id', $appId)->where('account_id', $authAccount->id)->where('account_token', $authAccountToken)->first();
            $sessionToken->update([
                'device_token' => $dtoRequest->deviceToken,
            ]);
        }

        // upload session log
        $sessionLog['type'] = SessionLog::TYPE_ACCOUNT_EDIT_DATA;
        $sessionLog['objectAction'] = 'Account Edit Data';
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        CacheHelper::forgetFresnsAccount($authAccount->aid);

        $authUser = $this->user();
        CacheHelper::forgetFresnsUser($authUser?->id, $authUser?->uid);

        return $this->success();
    }

    // logout
    public function logout()
    {
        $authAccount = $this->account();
        $authUser = $this->user();
        $aidToken = \request()->header('X-Fresns-Aid-Token');

        if (empty($authAccount)) {
            throw new ApiException(31502);
        }

        if (empty($aidToken)) {
            throw new ApiException(31505);
        }

        SessionToken::where('account_id', $authAccount->id)->where('account_token', $aidToken)->delete();

        CacheHelper::forgetFresnsAccount($authAccount->aid);
        CacheHelper::forgetFresnsUser($authUser?->id, $authUser?->uid);

        return $this->success();
    }

    // applyDelete
    public function applyDelete(Request $request)
    {
        $dtoRequest = new AccountApplyDeleteDTO($request->all());
        $authAccount = $this->account();

        $deleteType = ConfigHelper::fresnsConfigByItemKey('delete_account_type');

        if ($deleteType == 1) {
            throw new ApiException(33100);
        }

        $todoDay = ConfigHelper::fresnsConfigByItemKey('delete_account_todo');

        if ($dtoRequest->password) {
            $password = base64_decode($dtoRequest->password, true);

            if (! Hash::check($password, $authAccount->password)) {
                throw new ApiException(34304);
            }
        } else {
            if ($dtoRequest->codeType == 'email') {
                $codeWordBody = [
                    'type' => 1,
                    'account' => $authAccount->email,
                    'countryCode' => null,
                    'verifyCode' => $dtoRequest->verifyCode,
                    'templateId' => VerifyCode::TEMPLATE_DELETE_ACCOUNT,
                ];
            } else {
                $codeWordBody = [
                    'type' => 2,
                    'account' => $authAccount->pure_phone,
                    'countryCode' => $authAccount->country_code,
                    'verifyCode' => $dtoRequest->verifyCode,
                    'templateId' => VerifyCode::TEMPLATE_DELETE_ACCOUNT,
                ];
            }

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($codeWordBody);
            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getOrigin();
            }
        }

        $authAccount->update([
            'wait_delete' => true,
            'wait_delete_at' => now()->addDays($todoDay),
        ]);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_DELETE,
            'fskey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => $authAccount->aid,
            'uid' => null,
            'objectName' => \request()->path(),
            'objectAction' => 'Apply Delete Account',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreInfo' => null,
        ];
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        CacheHelper::forgetFresnsAccount($authAccount->aid);

        return $this->success([
            'day' => $todoDay,
            'dateTime' => DateHelper::fresnsDateTimeByTimezone($authAccount->wait_delete_at, $this->timezone(), $this->langTag()),
        ]);
    }

    // recallDelete
    public function recallDelete()
    {
        $authAccount = $this->account();

        $authAccount->update([
            'wait_delete' => false,
            'wait_delete_at' => null,
        ]);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_DELETE,
            'fskey' => 'Fresns',
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'langTag' => $this->langTag(),
            'aid' => $authAccount->aid,
            'uid' => null,
            'objectName' => \request()->path(),
            'objectAction' => 'Revoke Delete Account',
            'objectResult' => SessionLog::STATE_SUCCESS,
            'objectOrderId' => null,
            'deviceInfo' => $this->deviceInfo(),
            'deviceToken' => null,
            'moreInfo' => null,
        ];
        \FresnsCmdWord::plugin('Fresns')->uploadSessionLog($sessionLog);

        CacheHelper::forgetFresnsAccount($authAccount->aid);

        return $this->success();
    }
}
