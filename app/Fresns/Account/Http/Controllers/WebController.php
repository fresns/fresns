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
use Browser;
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
        $accountConnects = $account->getAccountConnects($langTag);

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
        $serviceUrl = [
            'users' => PluginHelper::fresnsPluginUrlByFskey($fsConfig['account_users_service']),
            'kyc' => PluginHelper::fresnsPluginUrlByFskey($fsConfig['account_kyc_service']),
        ];

        // handle date
        $timezone = $request->attributes->get('fresns_account_center_timezone');
        $accountData['waitDeleteDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['waitDeleteDateTime'], $timezone, $langTag);

        $redirectURL = $request->redirectURL ?? Cookie::get('fresns_account_center_callback_redirect_url');
        if ($redirectURL && $redirectURL != '{redirectUrl}') {
            $redirectURL = urlencode($redirectURL);
        }

        return view('FsAccountView::index', compact('account', 'accountPassport', 'accountData', 'accountWallet', 'accountConnects', 'fsConfig', 'serviceUrl', 'redirectURL'));
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
        $platformId = $request->attributes->get('fresns_account_center_platform_id');
        $version = $request->attributes->get('fresns_account_center_version');

        if (empty($appId) || empty($platformId) || empty($version)) {
            $code = 30001;
            $message = ConfigUtility::getCodeMessage(30001, 'Fresns', $langTag).' (accessToken)';

            return response()->view('FsAccountView::commons.tips', compact('code', 'message'), 403);
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

        $userAgent = Browser::userAgent();
        $userAgent = Str::of($userAgent)->lower()->toString();

        $miniBrowser = Str::contains($userAgent, 'miniprogram');

        $connectServices = [];
        if (! $miniBrowser) {
            $connectServices = ConfigHelper::fresnsConfigPluginsByItemKey('account_connect_services', $langTag);
        }

        $emailConfig = $fsConfig['account_email_register'];
        $phoneConfig = $fsConfig['account_phone_register'];

        return view('FsAccountView::register', compact('fsConfig', 'connectServices', 'emailConfig', 'phoneConfig'));
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
        $platformId = $request->attributes->get('fresns_account_center_platform_id');
        $version = $request->attributes->get('fresns_account_center_version');

        if (empty($appId) || empty($platformId) || empty($version)) {
            $code = 30001;
            $message = ConfigUtility::getCodeMessage(30001, 'Fresns', $langTag).' (accessToken)';

            return response()->view('FsAccountView::commons.tips', compact('code', 'message'), 403);
        }

        $fsConfig = ConfigHelper::fresnsConfigByItemKeys([
            'site_url',
            'account_email_login',
            'account_phone_login',
            'account_login_or_register',
            'account_login_with_code',
            'account_register_status',
        ]);

        $userAgent = Browser::userAgent();
        $userAgent = Str::of($userAgent)->lower()->toString();

        $miniBrowser = Str::contains($userAgent, 'miniprogram');

        $connectServices = [];
        if (! $miniBrowser) {
            $connectServices = ConfigHelper::fresnsConfigPluginsByItemKey('account_connect_services', $langTag);
        }

        $emailConfig = $fsConfig['account_email_login'];
        $phoneConfig = $fsConfig['account_phone_login'];

        return view('FsAccountView::login', compact('fsConfig', 'connectServices', 'emailConfig', 'phoneConfig'));
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
        $platformId = $request->attributes->get('fresns_account_center_platform_id');
        $version = $request->attributes->get('fresns_account_center_version');

        if (empty($appId) || empty($platformId) || empty($version)) {
            $langTag = $request->attributes->get('fresns_account_center_lang_tag');

            $code = 30001;
            $message = ConfigUtility::getCodeMessage(30001, 'Fresns', $langTag).' (accessToken)';

            return response()->view('FsAccountView::commons.tips', compact('code', 'message'), 403);
        }

        return view('FsAccountView::reset-password', compact('fsConfig'));
    }

    public function userAuth(Request $request)
    {
        $registerService = ConfigHelper::fresnsConfigByItemKey('account_register_service');
        $loginService = ConfigHelper::fresnsConfigByItemKey('account_login_service');

        if ($registerService && $loginService) {
            return Response::view('404', [], 404);
        }

        $appId = Cookie::get('fresns_account_center_app_id');
        $platformId = Cookie::get('fresns_account_center_platform_id');
        $version = Cookie::get('fresns_account_center_version');

        $loginToken = $request->loginToken;

        $usersServiceFskey = ConfigHelper::fresnsConfigByItemKey('account_users_service');
        $usersServiceUrl = PluginHelper::fresnsPluginUrlByFskey($usersServiceFskey);

        $accountDetail = [];

        $loginType = 'callback';
        $redirectURL = Cookie::get('fresns_account_center_callback_redirect_url');

        // redirect url
        if ($loginToken && $loginToken != '{loginToken}') {
            $wordBody = [
                'appId' => $appId,
                'platformId' => $platformId,
                'version' => $version,
                'loginToken' => $loginToken,
            ];

            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->checkLoginToken($wordBody);

            Cookie::queue('fresns_account_center_login_token', $loginToken);

            if ($fresnsResp->isSuccessResponse()) {
                $redirectURL = Str::replace('{loginToken}', $loginToken, $redirectURL);

                return view('FsAccountView::user-auth', compact('usersServiceUrl', 'accountDetail', 'loginType', 'loginToken', 'redirectURL'));
            }
        }

        $langTag = Cookie::get('fresns_account_center_lang_tag');

        $loginToken = Cookie::get('fresns_account_center_login_token') ?? $request->loginToken;

        if (empty($loginToken) || $loginToken == '{loginToken}') {
            return redirect()->to(route('account-center.login'));
        }

        $accountModel = PrimaryHelper::fresnsModelAccountByLoginToken($appId, $platformId, $version, $loginToken);

        if (empty($accountModel)) {
            $code = 34301;
            $message = ConfigUtility::getCodeMessage(34301, 'Fresns', $langTag);

            return response()->view('FsAccountView::commons.tips', compact('code', 'message'), 403);
        }

        $accountDetail = DetailUtility::accountDetail($accountModel, $langTag);

        $loginType = 'userAuth';
        $redirectURL = Str::replace('{loginToken}', $loginToken, $redirectURL);

        return view('FsAccountView::user-auth', compact('usersServiceUrl', 'accountDetail', 'loginType', 'loginToken', 'redirectURL'));
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
            $postMessageKey = Cookie::get('fresns_post_message_key');
        }

        $redirectURL = $request->redirectURL ?? Cookie::get('fresns_account_center_callback_redirect_url');
        if ($redirectURL && $redirectURL != '{redirectUrl}') {
            $redirectURL = urlencode($redirectURL);
        }

        $pluginUrl = Str::replace('{accessToken}', $accessToken, $url);
        $pluginUrl = Str::replace('{postMessageKey}', $postMessageKey, $pluginUrl);
        $pluginUrl = Str::replace('{redirectUrl}', $redirectURL, $pluginUrl);

        return $pluginUrl;
    }
}
