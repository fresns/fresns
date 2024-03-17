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
use App\Helpers\StrHelper;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Illuminate\Routing\Controller;

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

    public function signUp(Request $request)
    {
        $service = ConfigHelper::fresnsConfigByItemKey('account_register_service');

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

        return view('FsAccountView::register');
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

        return view('FsAccountView::login');
    }

    // get plugin url
    private static function getPluginUrl(Request $request, ?string $fskey = null): ?string
    {
        if (empty($fskey)) {
            return null;
        }

        $url = PluginHelper::fresnsPluginUrlByFskey($fskey);

        $accessToken = $request->accessToken;
        $postMessageKey = $request->callbackKey;
        $redirectURL = $request->redirectURL;

        $pluginUrl = Str::replace('{accessToken}', $accessToken, $url);
        $pluginUrl = Str::replace('{postMessageKey}', $postMessageKey, $pluginUrl);
        $pluginUrl = Str::replace('{redirectUrl}', $redirectURL, $pluginUrl);

        return $pluginUrl;
    }
}
