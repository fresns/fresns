<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Account\Http\Controllers;

use App\Fresns\Account\Http\DTO\SendVerifyCodeDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\SignHelper;
use App\Fresns\Api\Traits\ApiResponseTrait;
use App\Helpers\AppHelper;
use App\Helpers\CacheHelper;
use App\Models\Account;
use App\Models\AccountWallet;
use App\Models\SessionLog;
use App\Utilities\ValidationUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;

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

    public function sendVerifyCode(Request $request)
    {
        $dtoRequest = new SendVerifyCodeDTO($request->all());

        $templateId = $dtoRequest->templateId;
        $countryCode = $dtoRequest->countryCode;
        $accountInfo = $dtoRequest->account;

        $account = null;
        if ($templateId == 3 || $templateId == 4 || $templateId == 8) {
            $platformId = Cookie::get('fresns_account_center_platform_id');
            $aid = Cookie::get('fresns_account_center_aid');
            $aidToken = Cookie::get('fresns_account_center_aid_token');

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifyAccountToken([
                'platformId' => $platformId,
                'aid' => $aid,
                'aidToken' => $aidToken,
            ]);

            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->getErrorResponse();
            }

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

        if ($dtoRequest->type == 'email') {
            $accountType = 1;
        } else {
            $accountType = 2;
        }

        $langTag = Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag();

        $wordBody = [
            'type' => $accountType,
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
            $accountType = 1;
            $countryCode = null;
            $accountInfo = $account?->email;
        } else {
            $accountType = 2;
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
                    'templateId' => 3,
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
                    'templateId' => 3,
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
                        'templateId' => 3,
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
                        'templateId' => 3,
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
            'templateId' => 8,
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
