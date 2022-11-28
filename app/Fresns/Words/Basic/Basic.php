<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic;

use App\Fresns\Words\Basic\DTO\CheckCodeDTO;
use App\Fresns\Words\Basic\DTO\IpInfoDTO;
use App\Fresns\Words\Basic\DTO\SendCodeDTO;
use App\Fresns\Words\Basic\DTO\UploadSessionLogDTO;
use App\Fresns\Words\Basic\DTO\VerifySignDTO;
use App\Fresns\Words\Basic\DTO\VerifyUrlSignDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\SignHelper;
use App\Models\SessionKey;
use App\Models\SessionLog;
use App\Models\VerifyCode;
use App\Utilities\ConfigUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Facades\Cache;

class Basic
{
    use CmdWordResponseTrait;

    /**
     * @param $wordBody
     * @return array|string
     *
     * @throws \Throwable
     */
    public function verifySign($wordBody)
    {
        $dtoWordBody = new VerifySignDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $appId = $dtoWordBody->appId;

        $cacheKey = "fresns_api_key_{$appId}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $keyInfo = Cache::remember($cacheKey, $cacheTime, function () use ($appId) {
            return SessionKey::where('app_id', $appId)->isEnable()->first();
        });

        if (empty($keyInfo)) {
            return $this->failure(
                31301,
                ConfigUtility::getCodeMessage(31301, 'Fresns', $langTag),
            );
        }

        if ($keyInfo->type != 1) {
            return $this->failure(
                31304,
                ConfigUtility::getCodeMessage(31304, 'Fresns', $langTag),
            );
        }

        if ($keyInfo->platform_id != $dtoWordBody->platformId) {
            return $this->failure(
                31102,
                ConfigUtility::getCodeMessage(31102, 'Fresns', $langTag),
            );
        }

        $timestampNum = strlen($dtoWordBody->timestamp);
        $duration = 5; //Expiration time limit (unit: minutes)

        if ($timestampNum == 10) {
            $now = time();
            $expiredDuration = $duration * 60;
        } else {
            $now = intval(microtime(true) * 1000);
            $expiredDuration = $duration * 60 * 1000;
        }

        if ($now - $dtoWordBody->timestamp > $expiredDuration) {
            return $this->failure(
                31303,
                ConfigUtility::getCodeMessage(31303, 'Fresns', $langTag),
            );
        }

        $includeEmptyCheckArr = [
            'platformId' => $dtoWordBody->platformId,
            'version' => $dtoWordBody->version,
            'appId' => $dtoWordBody->appId,
            'timestamp' => $dtoWordBody->timestamp,
            'sign' => $dtoWordBody->sign,
            'aid' => $dtoWordBody->aid ?? null,
            'uid' => $dtoWordBody->uid ?? null,
            'token' => $dtoWordBody->token ?? null,
        ];

        $withoutEmptyCheckArr = array_filter($includeEmptyCheckArr);

        $checkSign = SignHelper::checkSign($withoutEmptyCheckArr, $keyInfo->app_secret);

        if (! $checkSign) {
            return $this->failure(
                31302,
                ConfigUtility::getCodeMessage(31302, 'Fresns', $langTag),
            );
        }

        if ($dtoWordBody->aid) {
            $verifySessionTokenArr = array_filter([
                'platformId' => $dtoWordBody->platformId,
                'aid' => $dtoWordBody->aid,
                'uid' => $dtoWordBody->uid ?? null,
                'token' => $dtoWordBody->token,
            ]);
            $verifySessionToken = \FresnsCmdWord::plugin()->verifySessionToken($verifySessionTokenArr);

            if ($verifySessionToken->isErrorResponse()) {
                return $verifySessionToken->errorResponse();
            }
        }

        return $this->success();
    }

    /**
     * @param $wordBody
     * @return mixed
     *
     * @throws \Throwable
     */
    public function verifyUrlSign($wordBody)
    {
        $dtoWordBody = new VerifyUrlSignDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $urlSignData = urldecode(base64_decode($dtoWordBody->urlSign));
        $urlSignJson = json_decode($urlSignData, true) ?? [];

        if (empty($urlSignJson['aid'])) {
            return $this->failure(
                31501,
                ConfigUtility::getCodeMessage(31501, 'Fresns', $langTag)
            );
        }

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifySign($urlSignJson);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getOrigin();
        }

        return $this->success($urlSignJson);
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function uploadSessionLog($wordBody)
    {
        $dtoWordBody = new UploadSessionLogDTO($wordBody);

        $accountId = null;
        if (isset($dtoWordBody->aid)) {
            $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        }

        $userId = null;
        if (isset($dtoWordBody->uid)) {
            $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);
        }

        $input = [
            'type' => $dtoWordBody->type,
            'plugin_unikey' => $dtoWordBody->pluginUnikey ?? 'Fresns',
            'platform_id' => $dtoWordBody->platformId,
            'version' => $dtoWordBody->version,
            'app_id' => $dtoWordBody->appId ?? null,
            'lang_tag' => $dtoWordBody->langTag ?? null,
            'account_id' => $accountId,
            'user_id' => $userId,
            'object_name' => $dtoWordBody->objectName,
            'object_action' => $dtoWordBody->objectAction,
            'object_result' => $dtoWordBody->objectResult,
            'object_order_id' => $dtoWordBody->objectOrderId ?? null,
            'device_info' => $dtoWordBody->deviceInfo ?? null,
            'device_token' => $dtoWordBody->deviceToken ?? null,
            'more_json' => $dtoWordBody->moreJson ?? null,
        ];

        SessionLog::create($input);

        return $this->success();
    }

    /**
     * @param $wordBody
     * @return mixed
     *
     * @throws \Throwable
     */
    public function sendCode($wordBody)
    {
        $dtoWordBody = new SendCodeDTO($wordBody);
        if ($dtoWordBody->type == 1) {
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('send_email_service');
        } else {
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('send_sms_service');
        }

        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());
        if (empty($pluginUniKey)) {
            return $this->failure(
                32100,
                ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
            );
        }

        $fresnsResp = \FresnsCmdWord::plugin($pluginUniKey)->sendCode($wordBody);

        return $fresnsResp->getOrigin();
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function checkCode($wordBody)
    {
        $dtoWordBody = new CheckCodeDTO($wordBody);
        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        if ($dtoWordBody->type == 1) {
            $account = $dtoWordBody->account;
        } else {
            $account = $dtoWordBody->countryCode.$dtoWordBody->account;
        }

        $verifyInfo = VerifyCode::where('template_id', $dtoWordBody->templateId)
            ->where('type', $dtoWordBody->type)
            ->where('account', $account)
            ->where('code', $dtoWordBody->verifyCode)
            ->where('expired_at', '>', date('Y-m-d H:i:s'))
            ->where('is_enable', 1)
            ->first();

        if (! $verifyInfo) {
            return $this->failure(
                33203,
                ConfigUtility::getCodeMessage(33203, 'Fresns', $langTag),
            );
        }

        $verifyInfo->update([
            'is_enable' => 0,
        ]);

        return $this->success();
    }

    public function ipInfo($wordBody)
    {
        $dtoWordBody = new IpInfoDTO($wordBody);

        $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('ip_service');

        $fresnsResp = \FresnsCmdWord::plugin($pluginUniKey)->ipInfo($wordBody);

        return $fresnsResp->getOrigin();
    }
}
