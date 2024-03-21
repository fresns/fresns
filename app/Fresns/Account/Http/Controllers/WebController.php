<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Account\Http\Controllers;

use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\SignHelper;
use App\Helpers\StrHelper;
use App\Models\Account;
use App\Utilities\ConfigUtility;
use App\Utilities\DetailUtility;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class WebController extends Controller
{
    public function index(Request $request)
    {
        $service = ConfigHelper::fresnsConfigByItemKey('account_center_service');

        if ($service) {
            return Response::view('404', [], 404);
        }

        $platformId = $request->attributes->get('fresns_account_center_platform_id');
        $aid = $request->attributes->get('fresns_account_center_aid');
        $aidToken = $request->attributes->get('fresns_account_center_aid_token');

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifyAccountToken([
            'platformId' => $platformId,
            'aid' => $aid,
            'aidToken' => $aidToken,
        ]);

        if ($fresnsResp->isErrorResponse()) {
            $loginService = ConfigHelper::fresnsConfigByItemKey('account_login_service');
            $pluginUrl = self::getPluginUrl($request, $loginService);

            if ($loginService && $pluginUrl) {
                return redirect()->to($pluginUrl);
            }

            return redirect()->to(route('account-center.login'));
        }

        $account = Account::with(['wallet', 'connects', 'users'])->where('aid', $aid)->first();

        $langTag = $request->attributes->get('fresns_account_center_lang_tag');

        $accountPassport = [
            'countryCode' => (int) $account->country_code,
            'purePhone' => $account->pure_phone ? StrHelper::maskNumber($account->pure_phone) : null,
            'phone' => $account->phone ? StrHelper::maskNumber($account->phone) : null,
            'email' => $account->email ? StrHelper::maskEmail($account->email) : null,
        ];

        $accountData = $account->getAccountInfo($langTag);
        $accountWallet = $account->getAccountWallet($langTag);
        $accountConnects = $account->getAccountConnects();

        $fsConfig = ConfigHelper::fresnsConfigByItemKeys([
            'wallet_status',
            'channel_me_wallet_name',
            'password_length',
            'password_strength',
            'account_users_service',
            'user_name',
            'account_kyc_service',
            'delete_account_type',
            'account_delete_policy',
        ]);

        // handle date
        $timezone = $request->attributes->get('fresns_account_center_timezone');
        $accountData['waitDeleteDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['waitDeleteDateTime'], $timezone, $langTag);

        return view('FsAccountView::index', compact('account', 'accountPassport', 'accountData', 'accountWallet', 'accountConnects', 'fsConfig'));
    }

    public function register(Request $request)
    {
        $service = ConfigHelper::fresnsConfigByItemKey('account_register_service');

        if ($service) {
            return Response::view('404', [], 404);
        }

        $registerStatus = ConfigHelper::fresnsConfigByItemKey('account_register_status');

        if (! $registerStatus) {
            return Response::view('404', [], 404);
        }

        $platformId = $request->attributes->get('fresns_account_center_platform_id');
        $aid = $request->attributes->get('fresns_account_center_aid');
        $aidToken = $request->attributes->get('fresns_account_center_aid_token');

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifyAccountToken([
            'platformId' => $platformId,
            'aid' => $aid,
            'aidToken' => $aidToken,
        ]);

        if ($fresnsResp->isSuccessResponse()) {
            $accountCenterService = ConfigHelper::fresnsConfigByItemKey('account_center_service');
            $pluginUrl = self::getPluginUrl($request, $accountCenterService);

            if ($accountCenterService && $pluginUrl) {
                return redirect()->to($pluginUrl);
            }

            return redirect()->to(route('account-center.index'));
        }

        $langTag = $request->attributes->get('fresns_account_center_lang_tag');

        $appId = $request->attributes->get('fresns_account_center_app_id');
        $version = $request->attributes->get('fresns_account_center_version');
        $platformId = $request->attributes->get('fresns_account_center_platform_id');

        if (empty($appId) || empty($version) || empty($platformId)) {
            $code = 30001;
            $message = ConfigUtility::getCodeMessage(30001, 'Fresns', $langTag).' (accessToken)';

            return response()->view('FsAccountView::tips', compact('code', 'message'), 403);
        }

        $fsConfig = ConfigHelper::fresnsConfigByItemKeys([
            'account_email_register',
            'account_phone_register',
            'password_strength',
            'password_length',
            'nickname_min',
            'nickname_max',
            'user_nickname_name',
            'account_terms_status',
            'account_privacy_status',
            'account_cookie_status',
            'account_terms_policy',
            'account_privacy_policy',
            'account_cookie_policy',
        ]);

        $connectServices = ConfigHelper::fresnsConfigPluginsByItemKey('account_connect_services', $langTag);

        return view('FsAccountView::register', compact('fsConfig', 'connectServices'));
    }

    public function login(Request $request)
    {
        $service = ConfigHelper::fresnsConfigByItemKey('account_login_service');

        if ($service) {
            return Response::view('404', [], 404);
        }

        $platformId = $request->attributes->get('fresns_account_center_platform_id');
        $aid = $request->attributes->get('fresns_account_center_aid');
        $aidToken = $request->attributes->get('fresns_account_center_aid_token');

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifyAccountToken([
            'platformId' => $platformId,
            'aid' => $aid,
            'aidToken' => $aidToken,
        ]);

        if ($fresnsResp->isSuccessResponse()) {
            $accountCenterService = ConfigHelper::fresnsConfigByItemKey('account_center_service');
            $pluginUrl = self::getPluginUrl($request, $accountCenterService);

            if ($accountCenterService && $pluginUrl) {
                return redirect()->to($pluginUrl);
            }

            return redirect()->to(route('account-center.index'));
        }

        $langTag = $request->attributes->get('fresns_account_center_lang_tag');

        $appId = $request->attributes->get('fresns_account_center_app_id');
        $version = $request->attributes->get('fresns_account_center_version');
        $platformId = $request->attributes->get('fresns_account_center_platform_id');

        if (empty($appId) || empty($version) || empty($platformId)) {
            $code = 30001;
            $message = ConfigUtility::getCodeMessage(30001, 'Fresns', $langTag).' (accessToken)';

            return response()->view('FsAccountView::tips', compact('code', 'message'), 403);
        }

        $fsConfig = ConfigHelper::fresnsConfigByItemKeys([
            'account_email_login',
            'account_phone_login',
            'account_login_or_register',
            'account_login_with_code',
            'account_register_status',
        ]);

        $connectServices = ConfigHelper::fresnsConfigPluginsByItemKey('account_connect_services', $langTag);

        return view('FsAccountView::login', compact('fsConfig', 'connectServices'));
    }

    public function resetPassword(Request $request)
    {
        $service = ConfigHelper::fresnsConfigByItemKey('account_login_service');

        if ($service) {
            return Response::view('404', [], 404);
        }

        $platformId = $request->attributes->get('fresns_account_center_platform_id');
        $aid = $request->attributes->get('fresns_account_center_aid');
        $aidToken = $request->attributes->get('fresns_account_center_aid_token');

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifyAccountToken([
            'platformId' => $platformId,
            'aid' => $aid,
            'aidToken' => $aidToken,
        ]);

        if ($fresnsResp->isSuccessResponse()) {
            $accountCenterService = ConfigHelper::fresnsConfigByItemKey('account_center_service');
            $pluginUrl = self::getPluginUrl($request, $accountCenterService);

            if ($accountCenterService && $pluginUrl) {
                return redirect()->to($pluginUrl);
            }

            return redirect()->to(route('account-center.index'));
        }

        $fsConfig = ConfigHelper::fresnsConfigByItemKeys([
            'send_email_service',
            'send_sms_service',
            'password_strength',
            'password_length',
            'account_register_status',
        ]);

        $appId = $request->attributes->get('fresns_account_center_app_id');
        $version = $request->attributes->get('fresns_account_center_version');
        $platformId = $request->attributes->get('fresns_account_center_platform_id');

        if (empty($appId) || empty($version) || empty($platformId)) {
            $langTag = $request->attributes->get('fresns_account_center_lang_tag');

            $code = 30001;
            $message = ConfigUtility::getCodeMessage(30001, 'Fresns', $langTag).' (accessToken)';

            return response()->view('FsAccountView::tips', compact('code', 'message'), 403);
        }

        return view('FsAccountView::reset-password', compact('fsConfig'));
    }

    public function userAuth(Request $request)
    {
        $service = ConfigHelper::fresnsConfigByItemKey('account_login_service');

        if ($service) {
            return Response::view('404', [], 404);
        }

        $langTag = Cookie::get('fresns_account_center_lang_tag');
        $code = 30001;
        $message = ConfigUtility::getCodeMessage(30001, 'Fresns', $langTag).' (userAuthInfo)';

        $userAuthInfo = Cookie::get('fresns_account_center_user_auth');

        if (empty($userAuthInfo)) {
            return redirect()->to(route('account-center.login'));
        }

        try {
            $stringify = base64_decode($userAuthInfo, true);
            $userAuthInfoArr = json_decode($stringify, true);

            $aid = $userAuthInfoArr['aid'];
            $loginToken = $userAuthInfoArr['loginToken'];

            if (empty($aid) || empty($loginToken)) {
                return response()->view('FsAccountView::tips', compact('code', 'message'), 403);
            }
        } catch (\Exception $e) {
            return response()->view('FsAccountView::tips', compact('code', 'message'), 403);
        }

        $accountModel = Account::where('aid', $aid)->first();

        if (empty($accountModel)) {
            $code = 34301;
            $message = ConfigUtility::getCodeMessage(34301, 'Fresns', $langTag);

            return response()->view('FsAccountView::tips', compact('code', 'message'), 403);
        }

        $accountDetail = DetailUtility::accountDetail($accountModel, $langTag);

        $usersService = ConfigHelper::fresnsConfigByItemKey('account_users_service');

        return view('FsAccountView::user-auth', compact('accountDetail', 'usersService'));
    }

    // get plugin url
    private static function getPluginUrl(Request $request, ?string $fskey = null): ?string
    {
        if (empty($fskey)) {
            return null;
        }

        $url = PluginHelper::fresnsPluginUrlByFskey($fskey);

        $accessToken = $request->accessToken;
        if (empty($accessToken)) {
            $appId = Cookie::get('fresns_account_center_app_id');

            $keyInfo = PrimaryHelper::fresnsModelByFsid('key', $appId);

            if ($keyInfo) {
                $headers = [
                    'X-Fresns-App-Id' => Cookie::get('fresns_account_center_app_id'),
                    'X-Fresns-Client-Platform-Id' => Cookie::get('fresns_account_center_platform_id'),
                    'X-Fresns-Client-Version' => Cookie::get('fresns_account_center_version'),
                    'X-Fresns-Client-Device-Info' => Cookie::get('fresns_account_center_device_info'),
                    'X-Fresns-Client-Timezone' => Cookie::get('fresns_timezone'),
                    'X-Fresns-Client-Lang-Tag' => Cookie::get('fresns_account_center_device_info'),
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
            }
        }

        $postMessageKey = $request->callbackKey;
        if (empty($postMessageKey)) {
            $postMessageKey = Cookie::get('fresns_callback_key');
        }

        $callbackUlid = $request->callbackUlid;
        if (empty($callbackUlid)) {
            $callbackUlid = Cookie::get('fresns_callback_ulid');
        }

        $redirectURL = $request->redirectURL;
        if (empty($redirectURL)) {
            $redirectURL = Cookie::get('fresns_redirect_url');
        }

        $pluginUrl = Str::replace('{accessToken}', $accessToken, $url);
        $pluginUrl = Str::replace('{postMessageKey}', $postMessageKey, $pluginUrl);
        $pluginUrl = Str::replace('{callbackUlid}', $callbackUlid, $pluginUrl);
        $pluginUrl = Str::replace('{redirectUrl}', $redirectURL, $pluginUrl);

        return $pluginUrl;
    }
}
