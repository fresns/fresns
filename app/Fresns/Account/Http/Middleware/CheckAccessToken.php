<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Account\Http\Middleware;

use App\Helpers\ConfigHelper;
use App\Utilities\ConfigUtility;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\View;

class CheckAccessToken
{
    public function handle(Request $request, Closure $next)
    {
        $appId = Cookie::get('fresns_account_center_app_id');
        $platformId = Cookie::get('fresns_account_center_platform_id');
        $version = Cookie::get('fresns_account_center_version');
        $deviceInfo = Cookie::get('fresns_account_center_device_info');

        $timezone = Cookie::get('fresns_account_center_timezone');
        $langTag = Cookie::get('fresns_account_center_lang_tag') ?? ConfigHelper::fresnsConfigDefaultLangTag();

        $aid = Cookie::get('fresns_account_center_aid');
        $aidToken = Cookie::get('fresns_account_center_aid_token');
        $uid = Cookie::get('fresns_account_center_uid');
        $uidToken = Cookie::get('fresns_account_center_uid_token');

        $siteIcon = ConfigHelper::fresnsConfigFileUrlByItemKey('site_icon');
        $siteLogo = ConfigHelper::fresnsConfigFileUrlByItemKey('site_logo');
        View::share('siteIcon', $siteIcon);
        View::share('siteLogo', $siteLogo);

        $siteName = ConfigHelper::fresnsConfigByItemKey('site_name', $langTag);
        $fsLang = ConfigHelper::fresnsConfigLanguagePack($langTag);
        $accountCenterCaptcha = ConfigHelper::fresnsConfigByItemKey('account_center_captcha');
        $accountEmptyError = ConfigUtility::getCodeMessage(34100, 'Fresns', $langTag);
        $verifyCodeEmptyError = ConfigUtility::getCodeMessage(33202, 'Fresns', $langTag);

        $fsConfig = ConfigHelper::fresnsConfigByItemKeys([
            'send_sms_default_code',
            'send_sms_supported_codes',
        ]);

        View::share('siteName', $siteName);
        View::share('fsLang', $fsLang);
        View::share('langTag', $langTag);
        View::share('accountCenterCaptcha', $accountCenterCaptcha);
        View::share('accountEmptyError', $accountEmptyError);
        View::share('verifyCodeEmptyError', $verifyCodeEmptyError);
        View::share('smsDefaultCode', $fsConfig['send_sms_default_code']);
        View::share('smsSupportedCodes', $fsConfig['send_sms_supported_codes']);

        $accountCenterCaptcha = ConfigHelper::fresnsConfigByItemKey('account_center_captcha');
        $captcha = [
            'type' => $accountCenterCaptcha['type'] ?? null,
            'siteKey' => $accountCenterCaptcha['siteKey'] ?? null,
            'secretKey' => $accountCenterCaptcha['secretKey'] ?? null,
        ];
        View::share('captcha', $captcha);

        // Verify Access Token
        if ($request->accessToken) {
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifyAccessToken([
                'accessToken' => $request->accessToken,
            ]);

            if ($fresnsResp->isErrorResponse()) {
                $code = $fresnsResp->getCode();
                $message = $fresnsResp->getMessage();

                return response()->view('FsAccountView::commons.tips', compact('code', 'message'), 403);
            }

            $appId = $fresnsResp->getData('appId');
            $platformId = $fresnsResp->getData('platformId');
            $version = $fresnsResp->getData('version');
            $deviceInfo = base64_encode(json_encode($fresnsResp->getData('deviceInfo')));

            $timezone = $fresnsResp->getData('timezone');
            $langTag = $fresnsResp->getData('langTag');

            $aid = $fresnsResp->getData('aid');
            $aidToken = $fresnsResp->getData('aidToken');
            $uid = $fresnsResp->getData('uid');
            $uidToken = $fresnsResp->getData('uidToken');

            Cookie::queue('fresns_account_center_app_id', $appId);
            Cookie::queue('fresns_account_center_platform_id', $platformId);
            Cookie::queue('fresns_account_center_version', $version);
            Cookie::queue('fresns_account_center_device_info', $deviceInfo);

            Cookie::queue('fresns_account_center_timezone', $timezone);
            Cookie::queue('fresns_account_center_lang_tag', $langTag);

            Cookie::queue('fresns_account_center_aid', $aid);
            Cookie::queue('fresns_account_center_aid_token', $aidToken);
            Cookie::queue('fresns_account_center_uid', $uid);
            Cookie::queue('fresns_account_center_uid_token', $uidToken);
        }

        $request->attributes->add([
            'fresns_account_center_app_id' => $appId,
            'fresns_account_center_platform_id' => $platformId,
            'fresns_account_center_version' => $version,
            'fresns_account_center_device_info' => $deviceInfo,
            'fresns_account_center_timezone' => $timezone,
            'fresns_account_center_lang_tag' => $langTag,
            'fresns_account_center_aid' => $aid,
            'fresns_account_center_aid_token' => $aidToken,
            'fresns_account_center_uid' => $uid,
            'fresns_account_center_uid_token' => $uidToken,
        ]);

        return $next($request);
    }
}
