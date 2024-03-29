<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Account\Http\Controllers;

use App\Fresns\Account\Http\DTO\SendVerifyCodeDTO;
use App\Fresns\Api\Traits\ApiResponseTrait;
use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\SignHelper;
use App\Helpers\StrHelper;
use App\Models\Account;
use App\Models\AccountWallet;
use App\Models\AppCallback;
use App\Models\SessionLog;
use App\Models\User;
use App\Models\VerifyCode;
use App\Utilities\ValidationUtility;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ApiController extends Controller
{
    use ApiResponseTrait;

    public function makeAccessToken()
    {
        $appId = Cookie::get('fresns_account_center_app_id');
        $langTag = Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag();
        $timezone = Cookie::get('fresns_account_center_timezone');

        $keyInfo = PrimaryHelper::fresnsModelByFsid('key', $appId);

        if (empty($keyInfo)) {
            return $this->failure(31301);
        }

        // headers
        $headers = [
            'X-Fresns-App-Id' => $appId,
            'X-Fresns-Client-Platform-Id' => Cookie::get('fresns_account_center_platform_id'),
            'X-Fresns-Client-Version' => Cookie::get('fresns_account_center_version'),
            'X-Fresns-Client-Device-Info' => Cookie::get('fresns_account_center_device_info'),
            'X-Fresns-Client-Timezone' => $timezone,
            'X-Fresns-Client-Lang-Tag' => $langTag,
            'X-Fresns-Client-Content-Format' => null,
            'X-Fresns-Aid' => Cookie::get('fresns_account_center_aid'),
            'X-Fresns-Aid-Token' => Cookie::get('fresns_account_center_aid_token'),
            'X-Fresns-Uid' => Cookie::get('fresns_account_center_uid'),
            'X-Fresns-Uid-Token' => Cookie::get('fresns_account_center_uid_token'),
            'X-Fresns-Signature' => null,
            'X-Fresns-Signature-Timestamp' => time(),
        ];
        $headers['X-Fresns-Signature'] = SignHelper::makeSign($headers, $keyInfo->app_key);

        $accessToken = urlencode(base64_encode(json_encode($headers)));

        $data = [
            'accessToken' => $accessToken,
        ];

        return $this->success($data);
    }

    public function guestSendVerifyCode(Request $request)
    {
        $type = $request->type;
        if (! in_array($type, ['register', 'login', 'resetPassword'])) {
            return $this->failure(30002);
        }

        $accountInfo = $request->account;
        $countryCode = $request->countryCode;

        $isEmail = filter_var($accountInfo, FILTER_VALIDATE_EMAIL);
        $isPureInt = StrHelper::isPureInt($accountInfo);

        if (! $isEmail && ! $isPureInt) {
            return $this->failure(30002);
        }

        // register
        if ($type == 'register') {
            $emailRegister = ConfigHelper::fresnsConfigByItemKey('account_email_register');
            if ($isEmail && ! $emailRegister) {
                return $this->failure(34202);
            }

            $phoneRegister = ConfigHelper::fresnsConfigByItemKey('account_phone_register');
            if ($isPureInt && ! $phoneRegister) {
                return $this->failure(34203);
            }
        }

        // login
        $loginWithCode = ConfigHelper::fresnsConfigByItemKey('account_login_with_code');
        if ($type == 'login' && ! $loginWithCode) {
            return $this->failure(33100);
        }

        // send word body
        $sendType = null;
        $sendAccount = null;
        $sendCountryCode = null;
        $sendTemplateId = match ($type) {
            'login' => VerifyCode::TEMPLATE_LOGIN_ACCOUNT,
            'register' => VerifyCode::TEMPLATE_REGISTER_ACCOUNT,
            'resetPassword' => VerifyCode::TEMPLATE_RESET_LOGIN_PASSWORD,
            default => null,
        };

        if ($isEmail) {
            $accountModel = Account::where('email', $accountInfo)->first();

            $sendType = VerifyCode::TYPE_EMAIL;
            $sendAccount = $accountModel?->email;
        } else {
            $phone = $countryCode.$accountInfo;
            $accountModel = Account::where('phone', $phone)->first();

            $sendType = VerifyCode::TYPE_SMS;
            $sendAccount = $accountModel?->pure_phone;
            $sendCountryCode = $accountModel?->country_code;
        }

        // type switch
        switch ($type) {
            case 'register':
                if ($accountModel) {
                    return $this->failure(34204);
                }

                $sendAccount = $accountInfo;
                $sendCountryCode = $countryCode;
                break;

            case 'login':
                if (empty($accountModel)) {
                    return $this->failure(31502);
                }
                break;

            case 'resetPassword':
                if (empty($accountModel)) {
                    return $this->failure(31502);
                }
                break;
        }

        $langTag = Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag();

        $wordBody = [
            'type' => $sendType,
            'account' => $sendAccount,
            'countryCode' => $sendCountryCode,
            'templateId' => $sendTemplateId,
            'langTag' => $langTag,
        ];

        $fresnsSendCodeResp = \FresnsCmdWord::plugin('Fresns')->sendCode($wordBody);

        if ($fresnsSendCodeResp->isErrorResponse()) {
            return $$fresnsSendCodeResp->getErrorResponse();
        }

        return $this->success();
    }

    public function register(Request $request)
    {
        $countryCode = $request->countryCode;
        $account = $request->account;
        if (empty($account)) {
            return $this->failure(34100);
        }

        switch ($request->accountType) {
            case 'email':
                $emailRegister = ConfigHelper::fresnsConfigByItemKey('account_email_register');
                if (! $emailRegister) {
                    return $this->failure(34202);
                }

                $accountModel = Account::where('email', $account)->first();

                $sendType = VerifyCode::TYPE_EMAIL;
                break;

            case 'phone':
                $phoneRegister = ConfigHelper::fresnsConfigByItemKey('account_phone_register');
                if (! $phoneRegister) {
                    return $this->failure(34203);
                }

                $phone = $countryCode.$account;
                $accountModel = Account::where('phone', $phone)->first();

                $sendType = VerifyCode::TYPE_SMS;
                break;

            default:
                return $this->failure(30002);
        }

        if ($accountModel) {
            $errorCode = match ($request->accountType) {
                'email' => 34206,
                'phone' => 34205,
            };

            return $this->failure($errorCode);
        }

        // verifyCode
        $verifyCode = $request->verifyCode;
        if (empty($verifyCode)) {
            return $this->failure(33202);
        }

        // birthday
        $birthday = $request->birthday;
        if (empty($birthday) || ! strtotime($birthday)) {
            return $this->failure(34113);
        }

        // password
        $password = $request->password;
        if (empty($password)) {
            return $this->failure(34111);
        }

        $validatePassword = ValidationUtility::password($password);

        if (! $validatePassword['length']) {
            return $this->failure(34105);
        }

        if (! $validatePassword['number']) {
            return $this->failure(34106);
        }

        if (! $validatePassword['lowercase']) {
            return $this->failure(34107);
        }

        if (! $validatePassword['uppercase']) {
            return $this->failure(34108);
        }

        if (! $validatePassword['symbols']) {
            return $this->failure(34109);
        }

        // nickname
        $nickname = $request->nickname;
        if (empty($nickname)) {
            return $this->failure(33202);
        }

        $nickname = Str::of($nickname)->trim();
        $validateNickname = ValidationUtility::nickname($nickname);

        if (! $validateNickname['formatString'] || ! $validateNickname['formatSpace']) {
            return $this->failure(35107);
        }

        if (! $validateNickname['minLength']) {
            return $this->failure(35109);
        }

        if (! $validateNickname['maxLength']) {
            return $this->failure(35108);
        }

        if (! $validateNickname['use']) {
            return $this->failure(35111);
        }

        if (! $validateNickname['banName']) {
            return $this->failure(35110);
        }

        // checkCode
        $wordBody = [
            'type' => $sendType,
            'account' => $account,
            'countryCode' => $request->countryCode,
            'verifyCode' => $verifyCode,
            'templateId' => VerifyCode::TEMPLATE_REGISTER_ACCOUNT,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        // create account
        $accountType = match ($request->accountType) {
            'email' => Account::CREATE_TYPE_EMAIL,
            'phone' => Account::CREATE_TYPE_PHONE,
        };
        $createAccountWordBody = [
            'type' => $accountType,
            'account' => $account,
            'countryCode' => $request->countryCode,
            'password' => $password,
            'birthday' => $birthday,
            'createUser' => true,
            'userInfo' => [
                'nickname' => $nickname,
            ],
        ];

        $fresnsCreateAccountResp = \FresnsCmdWord::plugin('Fresns')->createAccount($createAccountWordBody);

        if ($fresnsCreateAccountResp->isErrorResponse()) {
            return $fresnsCreateAccountResp->getErrorResponse();
        }

        // loginToken
        $loginToken = SignHelper::makeLoginToken($account);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_REGISTER,
            'platformId' => Cookie::get('fresns_account_center_platform_id'),
            'version' => Cookie::get('fresns_account_center_version'),
            'appId' => Cookie::get('fresns_account_center_app_id'),
            'langTag' => Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag(),
            'fskey' => 'Fresns',
            'actionName' => request()->path(),
            'actionDesc' => 'Create Account',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => null,
            'aid' => $fresnsCreateAccountResp->getData('aid'),
            'uid' => $fresnsCreateAccountResp->getData('uid'),
            'deviceInfo' => AppHelper::getDeviceInfo(),
            'deviceToken' => null,
            'loginToken' => $loginToken,
            'moreInfo' => null,
        ];

        // create session log
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        // callback ulid
        $callbackUlid = Cookie::get('fresns_callback_ulid');
        if ($callbackUlid && Str::of($callbackUlid)->isUlid()) {
            AppCallback::updateOrCreate([
                'ulid' => $callbackUlid,
            ], [
                'app_fskey' => 'Fresns',
                'type' => AppCallback::TYPE_TOKEN,
                'content' => [
                    'loginToken' => $loginToken,
                ],
                'is_used' => false,
            ]);
        }

        return $this->success([
            'loginToken' => $loginToken,
        ]);
    }

    public function login(Request $request)
    {
        $countryCode = $request->countryCode;
        $account = $request->account;
        if (empty($account)) {
            return $this->failure(34100);
        }

        $password = $request->password;
        $verifyCode = $request->verifyCode;
        if (empty($password) && empty($verifyCode)) {
            return $this->failure(34112);
        }

        switch ($request->accountType) {
            case 'email':
                $emailLogin = ConfigHelper::fresnsConfigByItemKey('account_email_login');
                if (! $emailLogin) {
                    return $this->failure(34207);
                }

                $accountModel = Account::withCount('users')->where('email', $account)->first();

                $verifyType = Account::VERIFY_TYPE_EMAIL;
                $verifyAccount = $accountModel?->email;
                $verifyCountryCode = null;
                break;

            case 'phone':
                $phoneLogin = ConfigHelper::fresnsConfigByItemKey('account_phone_login');
                if (! $phoneLogin) {
                    return $this->failure(34208);
                }

                $phone = $countryCode.$account;
                $accountModel = Account::withCount('users')->where('phone', $phone)->first();

                $verifyType = Account::VERIFY_TYPE_PHONE;
                $verifyAccount = $accountModel?->pure_phone;
                $verifyCountryCode = $accountModel?->country_code;
                break;

            default:
                return $this->failure(30002);
        }

        if (! $accountModel) {
            return $this->failure(34301);
        }

        // loginToken
        $loginToken = SignHelper::makeLoginToken($account);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_LOGIN,
            'platformId' => Cookie::get('fresns_account_center_platform_id'),
            'version' => Cookie::get('fresns_account_center_version'),
            'appId' => Cookie::get('fresns_account_center_app_id'),
            'langTag' => Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag(),
            'fskey' => 'Fresns',
            'actionName' => request()->path(),
            'actionDesc' => 'Account Login',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => null,
            'aid' => $accountModel->aid,
            'uid' => null,
            'deviceInfo' => AppHelper::getDeviceInfo(),
            'deviceToken' => null,
            'loginToken' => $loginToken,
            'moreInfo' => null,
        ];

        $wordBody = [
            'type' => $verifyType,
            'account' => $verifyAccount,
            'countryCode' => $verifyCountryCode,
            'password' => $password,
            'verifyCode' => $verifyCode,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifyAccount($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            $sessionLog['actionState'] = SessionLog::STATE_FAILURE;

            \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

            return $fresnsResp->getErrorResponse();
        }

        // account users
        $userCount = $accountModel->users_count;

        $user = $accountModel->users()->first();

        if ($userCount == 1 && ! $user->pin) {
            $sessionLog['uid'] = $user->uid;
            \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

            return $this->success([
                'loginToken' => $loginToken,
            ]);
        }

        // create session log
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        $userAuthInfoArr = [
            'aid' => $accountModel->aid,
            'loginToken' => $loginToken,
        ];

        $userAuthInfo = base64_encode(json_encode($userAuthInfoArr));

        Cookie::queue('fresns_account_center_user_auth', $userAuthInfo);

        return $this->success();
    }

    public function resetPassword(Request $request)
    {
        $account = $request->account;
        if (empty($account)) {
            return $this->failure(34100);
        }

        $verifyCode = $request->verifyCode;
        if (empty($verifyCode)) {
            return $this->failure(33202);
        }

        $newPassword = $request->newPassword;
        if (empty($newPassword)) {
            return $this->failure(34111);
        }

        $validatePassword = ValidationUtility::password($newPassword);

        if (! $validatePassword['length']) {
            return $this->failure(34105);
        }

        if (! $validatePassword['number']) {
            return $this->failure(34106);
        }

        if (! $validatePassword['lowercase']) {
            return $this->failure(34107);
        }

        if (! $validatePassword['uppercase']) {
            return $this->failure(34108);
        }

        if (! $validatePassword['symbols']) {
            return $this->failure(34109);
        }

        $accountType = $request->accountType;
        $countryCode = $request->countryCode;

        $sendType = match ($accountType) {
            'email' => VerifyCode::TYPE_EMAIL,
            'phone' => VerifyCode::TYPE_SMS,
            default => null,
        };

        $isEmail = filter_var($account, FILTER_VALIDATE_EMAIL);
        if ($isEmail) {
            $accountModel = Account::where('email', $account)->first();

            $sendAccount = $accountModel?->email;
        } else {
            $phone = $countryCode.$account;
            $accountModel = Account::where('phone', $phone)->first();

            $sendAccount = $accountModel?->pure_phone;
        }
        if (empty($accountModel)) {
            return $this->failure(34301);
        }

        $wordBody = [
            'type' => $sendType,
            'account' => $sendAccount,
            'countryCode' => $accountModel->country_code,
            'verifyCode' => $verifyCode,
            'templateId' => VerifyCode::TEMPLATE_RESET_LOGIN_PASSWORD,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        $dataPassword = Hash::make($newPassword);

        $accountModel->update([
            'password' => $dataPassword,
        ]);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_EDIT_PASSWORD,
            'platformId' => Cookie::get('fresns_account_center_platform_id'),
            'version' => Cookie::get('fresns_account_center_version'),
            'appId' => Cookie::get('fresns_account_center_app_id'),
            'langTag' => Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag(),
            'fskey' => 'Fresns',
            'actionName' => request()->path(),
            'actionDesc' => 'Account Reset Password',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => null,
            'aid' => $accountModel->aid,
            'uid' => null,
            'deviceInfo' => AppHelper::getDeviceInfo(),
            'deviceToken' => null,
            'loginToken' => null,
            'moreInfo' => null,
        ];

        // create session log
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        return $this->success();
    }

    public function userAuth(Request $request)
    {
        $userAuthInfo = Cookie::get('fresns_account_center_user_auth');

        if (empty($userAuthInfo)) {
            return $this->failure(30001);
        }

        try {
            $stringify = base64_decode($userAuthInfo, true);
            $userAuthInfoArr = json_decode($stringify, true);

            $aid = $userAuthInfoArr['aid'];
            $loginToken = $userAuthInfoArr['loginToken'];

            if (empty($aid) || empty($loginToken)) {
                Cookie::queue(Cookie::forget('fresns_account_center_user_auth'));

                return $this->failure(30001);
            }
        } catch (\Exception $e) {
            Cookie::queue(Cookie::forget('fresns_account_center_user_auth'));

            return $this->failure(30001);
        }

        $accountModel = Account::where('aid', $aid)->first();

        if (empty($accountModel)) {
            Cookie::queue(Cookie::forget('fresns_account_center_user_auth'));

            return $this->failure(34301);
        }

        $loginTokenInfo = SessionLog::where('account_id', $accountModel->id)->where('login_token', $loginToken)->first();

        if (! $loginTokenInfo) {
            Cookie::queue(Cookie::forget('fresns_account_center_user_auth'));

            return $this->failure(32206);
        }

        if ($loginTokenInfo->user_id) {
            Cookie::queue(Cookie::forget('fresns_account_center_user_auth'));

            return $this->failure(32204);
        }

        $checkTime = $loginTokenInfo->created_at->addMinutes(20);

        if ($checkTime->lt(now())) {
            Cookie::queue(Cookie::forget('fresns_account_center_user_auth'));

            return $this->failure(32203);
        }

        $uid = $request->uid;
        if (empty($uid)) {
            return $this->failure(35100);
        }

        $userModel = User::where('account_id', $accountModel->id)->where('uid', $uid)->first();
        if (empty($userModel)) {
            return $this->failure(35201);
        }

        // pin
        if ($userModel->pin) {
            $pin = $request->pin;

            if (empty($pin)) {
                return $this->failure(31604);
            }

            if (! Hash::check($pin, $userModel->pin)) {
                return $this->failure(35204);
            }
        }

        $loginTokenInfo->update([
            'user_id' => $userModel->id,
        ]);

        // login time
        $accountModel->update([
            'last_login_at' => now(),
        ]);

        $userModel->update([
            'last_login_at' => now(),
        ]);

        return $this->success([
            'loginToken' => $loginToken,
        ]);
    }

    public function sendVerifyCode(Request $request)
    {
        $dtoRequest = new SendVerifyCodeDTO($request->all());

        $templateId = $dtoRequest->templateId;
        $countryCode = $dtoRequest->countryCode;
        $accountInfo = $dtoRequest->account;

        $account = null;
        if ($templateId == 3 || $templateId == 4 || $templateId == 8) {
            $aid = Cookie::get('fresns_account_center_aid');
            $account = Account::where('aid', $aid)->first();

            if (empty($account)) {
                return $this->failure(31502);
            }

            if ($dtoRequest->type == 'email') {
                $accountInfo = $account->email;
            } else {
                $countryCode = $account->country_code;
                $accountInfo = $account->pure_phone;
            }
        }

        $sendType = match ($dtoRequest->type) {
            'email' => VerifyCode::TYPE_EMAIL,
            'sms' => VerifyCode::TYPE_SMS,
            default => null,
        };

        $langTag = Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag();

        $wordBody = [
            'type' => $sendType,
            'account' => $accountInfo,
            'countryCode' => $countryCode,
            'templateId' => $templateId,
            'langTag' => $langTag,
        ];

        $fresnsSendCodeResp = \FresnsCmdWord::plugin('Fresns')->sendCode($wordBody);

        if ($fresnsSendCodeResp->isErrorResponse()) {
            return $$fresnsSendCodeResp->getErrorResponse();
        }

        return $this->success();
    }

    public function checkVerifyCode(Request $request)
    {
        $aid = Cookie::get('fresns_account_center_aid');
        $account = Account::where('aid', $aid)->first();

        $type = $request->type;

        if ($type == 'email') {
            $accountType = VerifyCode::TYPE_EMAIL;
            $countryCode = null;
            $accountInfo = $account?->email;
        } else {
            $accountType = VerifyCode::TYPE_SMS;
            $countryCode = $account?->country_code;
            $accountInfo = $account?->pure_phone;
        }

        if (empty($account) || empty($accountInfo)) {
            return $this->failure(31502);
        }

        $verifyCode = $request->verifyCode;
        $templateId = $request->templateId;

        $wordBody = [
            'type' => $accountType,
            'account' => $accountInfo,
            'countryCode' => $countryCode,
            'verifyCode' => $verifyCode,
            'templateId' => $templateId,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        $cacheInfo = 'fresns_'.$type.'_'.$verifyCode;
        Cache::put($cacheInfo, $cacheInfo, now()->addMinutes(15));

        Cookie::queue('fresns_account_center_verify_code', $verifyCode);

        return $this->success();
    }

    public function update(Request $request)
    {
        $aid = Cookie::get('fresns_account_center_aid');
        $account = Account::where('aid', $aid)->first();

        if (empty($account)) {
            return $this->failure(31502);
        }

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_EDIT_DATA,
            'platformId' => Cookie::get('fresns_account_center_platform_id'),
            'version' => Cookie::get('fresns_account_center_version'),
            'appId' => Cookie::get('fresns_account_center_app_id'),
            'langTag' => Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag(),
            'fskey' => 'Fresns',
            'actionName' => request()->path(),
            'actionDesc' => 'Account Edit Data',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => null,
            'aid' => $aid,
            'uid' => Cookie::get('fresns_account_center_uid'),
            'deviceInfo' => AppHelper::getDeviceInfo(),
            'deviceToken' => null,
            'loginToken' => null,
            'moreInfo' => null,
        ];

        $formType = $request->formType;

        switch ($formType) {
            case 'birthday':
                $birthday = $request->birthday;

                if (! $birthday) {
                    return $this->failure(30001);
                }

                $account->update([
                    'birthday' => $birthday,
                ]);
                break;

            case 'email':
                $verifyCodeInfo = Cookie::get('fresns_account_center_verify_code');
                $cacheInfo = 'fresns_email_'.$verifyCodeInfo;
                if (! $account->email) {
                    $cacheInfo = 'fresns_sms_'.$verifyCodeInfo;
                }

                if ($account->email || $account->phone) {
                    $getCache = Cache::get($cacheInfo);

                    if (! $getCache) {
                        return $this->failure(30001);
                    }

                    Cache::forget($cacheInfo);
                }

                $newEmail = $request->newEmail;
                $newVerifyCode = $request->newVerifyCode;

                $wordBody = [
                    'type' => 1,
                    'account' => $newEmail,
                    'countryCode' => null,
                    'verifyCode' => $newVerifyCode,
                    'templateId' => VerifyCode::TEMPLATE_EDIT_PROFILE,
                ];

                $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($wordBody);

                if ($fresnsResp->isErrorResponse()) {
                    return $fresnsResp->getErrorResponse();
                }

                $account->update([
                    'email' => $newEmail,
                ]);
                break;

            case 'phone':
                $verifyCodeInfo = Cookie::get('fresns_account_center_verify_code');
                $cacheInfo = 'fresns_sms_'.$verifyCodeInfo;
                if (! $account->phone) {
                    $cacheInfo = 'fresns_email_'.$verifyCodeInfo;
                }

                if ($account->phone || $account->email) {
                    $getCache = Cache::get($cacheInfo);

                    if (! $getCache) {
                        return $this->failure(30001);
                    }

                    Cache::forget($cacheInfo);
                }

                $newCountryCode = $request->newCountryCode;
                $newPurePhone = $request->newPurePhone;
                $newVerifyCode = $request->newVerifyCode;

                $wordBody = [
                    'type' => 1,
                    'account' => $newPurePhone,
                    'countryCode' => $newCountryCode,
                    'verifyCode' => $newVerifyCode,
                    'templateId' => VerifyCode::TEMPLATE_EDIT_PROFILE,
                ];

                $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($wordBody);

                if ($fresnsResp->isErrorResponse()) {
                    return $fresnsResp->getErrorResponse();
                }

                $account->update([
                    'country_code' => $newCountryCode,
                    'pure_phone' => $newPurePhone,
                    'phone' => $newCountryCode.$newPurePhone,
                ]);
                break;

            case 'password':
                $codeType = $request->codeType;

                $verifyCode = match ($codeType) {
                    'email' => $request->emailVerifyCode,
                    'sms' => $request->smsVerifyCode,
                    default => null,
                };

                $currentPassword = $request->currentPassword;

                if (empty($currentPassword) && empty($verifyCode)) {
                    return $this->failure(34112);
                }

                if ($codeType == 'password') {
                    if (! Hash::check($currentPassword, $account->password)) {
                        return $this->failure(34304);
                    }
                } else {
                    $accountInfo = match ($codeType) {
                        'email' => $account->email,
                        'sms' => $account->pure_phone,
                        default => null,
                    };

                    $wordBody = [
                        'type' => 1,
                        'account' => $accountInfo,
                        'countryCode' => $account->country_code,
                        'verifyCode' => $verifyCode,
                        'templateId' => VerifyCode::TEMPLATE_EDIT_PROFILE,
                    ];

                    $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($wordBody);

                    if ($fresnsResp->isErrorResponse()) {
                        return $fresnsResp->getErrorResponse();
                    }
                }

                $newPassword = $request->newPassword;

                if (! $newPassword) {
                    return $this->failure(34111);
                }

                $validatePassword = ValidationUtility::password($newPassword);

                if (! $validatePassword['length']) {
                    return $this->failure(34105);
                }

                if (! $validatePassword['number']) {
                    return $this->failure(34106);
                }

                if (! $validatePassword['lowercase']) {
                    return $this->failure(34107);
                }

                if (! $validatePassword['uppercase']) {
                    return $this->failure(34108);
                }

                if (! $validatePassword['symbols']) {
                    return $this->failure(34109);
                }

                $account->update([
                    'password' => Hash::make($newPassword),
                ]);

                $sessionLog['type'] = SessionLog::TYPE_ACCOUNT_EDIT_PASSWORD;
                $sessionLog['actionDesc'] = 'Account Edit Password';
                break;

            case 'walletPassword':
                $codeType = $request->codeType;

                $verifyCode = match ($codeType) {
                    'email' => $request->emailVerifyCode,
                    'sms' => $request->smsVerifyCode,
                    default => null,
                };

                $currentWalletPassword = $request->currentWalletPassword;

                if (empty($currentWalletPassword) && empty($verifyCode)) {
                    return $this->failure(34112);
                }

                $wallet = AccountWallet::where('account_id', $account->id)->first();

                if (empty($wallet)) {
                    return $this->failure(34501);
                }

                if ($codeType == 'password') {
                    if (! Hash::check($currentWalletPassword, $wallet->password)) {
                        return $this->failure(34304);
                    }
                } else {
                    $accountInfo = match ($codeType) {
                        'email' => $account->email,
                        'sms' => $account->pure_phone,
                        default => null,
                    };

                    $wordBody = [
                        'type' => 1,
                        'account' => $accountInfo,
                        'countryCode' => $account->country_code,
                        'verifyCode' => $verifyCode,
                        'templateId' => VerifyCode::TEMPLATE_EDIT_PROFILE,
                    ];

                    $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($wordBody);

                    if ($fresnsResp->isErrorResponse()) {
                        return $fresnsResp->getErrorResponse();
                    }
                }

                $newWalletPassword = $request->newWalletPassword;

                if (! $newWalletPassword) {
                    return $this->failure(34111);
                }

                $wallet->update([
                    'password' => Hash::make($newWalletPassword),
                ]);

                $sessionLog['type'] = SessionLog::TYPE_WALLET_EDIT_PASSWORD;
                $sessionLog['actionDesc'] = 'Account Edit Wallet Password';
                break;
        }

        // create session log
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        CacheHelper::forgetFresnsAccount($account->aid);

        return $this->success();
    }

    // applyDelete
    public function applyDelete(Request $request)
    {
        $aid = Cookie::get('fresns_account_center_aid');
        $account = Account::where('aid', $aid)->first();

        if (empty($account)) {
            return $this->failure(31502);
        }

        $deleteType = ConfigHelper::fresnsConfigByItemKey('delete_account_type');

        if ($deleteType == 1) {
            return $this->failure(33100);
        }

        $codeType = $request->codeType;

        $verifyCode = match ($codeType) {
            'email' => $request->emailVerifyCode,
            'sms' => $request->smsVerifyCode,
            default => null,
        };

        if (empty($verifyCode)) {
            return $this->failure(33202);
        }

        $accountInfo = match ($codeType) {
            'email' => $account->email,
            'sms' => $account->pure_phone,
            default => null,
        };

        $wordBody = [
            'type' => 1,
            'account' => $accountInfo,
            'countryCode' => $account->country_code,
            'verifyCode' => $verifyCode,
            'templateId' => VerifyCode::TEMPLATE_DELETE_ACCOUNT,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkCode($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        $todoDay = ConfigHelper::fresnsConfigByItemKey('delete_account_todo');

        $account->update([
            'wait_delete' => true,
            'wait_delete_at' => now()->addDays($todoDay),
        ]);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_DELETE,
            'platformId' => Cookie::get('fresns_account_center_platform_id'),
            'version' => Cookie::get('fresns_account_center_version'),
            'appId' => Cookie::get('fresns_account_center_app_id'),
            'langTag' => Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag(),
            'fskey' => 'Fresns',
            'actionName' => request()->path(),
            'actionDesc' => 'Apply Delete Account',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => null,
            'aid' => $aid,
            'uid' => Cookie::get('fresns_account_center_uid'),
            'deviceInfo' => AppHelper::getDeviceInfo(),
            'deviceToken' => null,
            'loginToken' => null,
            'moreInfo' => null,
        ];
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        CacheHelper::forgetFresnsAccount($account->aid);

        return $this->success();
    }

    // revokeDelete
    public function revokeDelete()
    {
        $aid = Cookie::get('fresns_account_center_aid');
        $account = Account::where('aid', $aid)->first();

        if (empty($account)) {
            return $this->failure(31502);
        }

        $account->update([
            'wait_delete' => false,
            'wait_delete_at' => null,
        ]);

        // session log
        $sessionLog = [
            'type' => SessionLog::TYPE_ACCOUNT_DELETE,
            'platformId' => Cookie::get('fresns_account_center_platform_id'),
            'version' => Cookie::get('fresns_account_center_version'),
            'appId' => Cookie::get('fresns_account_center_app_id'),
            'langTag' => Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag(),
            'fskey' => 'Fresns',
            'actionName' => request()->path(),
            'actionDesc' => 'Revoke Delete Account',
            'actionState' => SessionLog::STATE_SUCCESS,
            'actionId' => null,
            'aid' => $aid,
            'uid' => Cookie::get('fresns_account_center_uid'),
            'deviceInfo' => AppHelper::getDeviceInfo(),
            'deviceToken' => null,
            'loginToken' => null,
            'moreInfo' => null,
        ];
        \FresnsCmdWord::plugin('Fresns')->createSessionLog($sessionLog);

        CacheHelper::forgetFresnsAccount($account->aid);

        return $this->success();
    }
}
