<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic;

use App\Fresns\Words\Basic\DTO\CheckCodeDTO;
use App\Fresns\Words\Basic\DTO\DeviceInfoDTO;
use App\Fresns\Words\Basic\DTO\IpInfoDTO;
use App\Fresns\Words\Basic\DTO\SendCodeDTO;
use App\Fresns\Words\Basic\DTO\UploadSessionLogDTO;
use App\Fresns\Words\Basic\DTO\VerifySignDTO;
use App\Fresns\Words\Basic\DTO\VerifyUrlAuthorizationDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\SignHelper;
use App\Models\SessionKey;
use App\Models\SessionLog;
use App\Models\VerifyCode;
use App\Utilities\ConfigUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;

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
        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());

        $keyInfo = PrimaryHelper::fresnsModelByFsid('key', $dtoWordBody->appId);
        $keyType = $dtoWordBody->verifyType ?? SessionKey::TYPE_CORE;
        $keyFskey = $dtoWordBody->verifyFskey;

        if (empty($keyInfo) || ! $keyInfo->is_enabled) {
            return $this->failure(
                31301,
                ConfigUtility::getCodeMessage(31301, 'Fresns', $langTag),
            );
        }

        if ($keyInfo->type != $keyType) {
            return $this->failure(
                31304,
                ConfigUtility::getCodeMessage(31304, 'Fresns', $langTag),
            );
        }

        if ($keyType == SessionKey::TYPE_PLUGIN && $keyInfo->plugin_fskey != $keyFskey) {
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
            'appId' => $dtoWordBody->appId,
            'platformId' => $dtoWordBody->platformId,
            'version' => $dtoWordBody->version,
            'aid' => $dtoWordBody->aid ?? null,
            'aidToken' => $dtoWordBody->aidToken ?? null,
            'uid' => $dtoWordBody->uid ?? null,
            'uidToken' => $dtoWordBody->uidToken ?? null,
            'signature' => $dtoWordBody->signature,
            'timestamp' => $dtoWordBody->timestamp,
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
            $verifyAccountToken = \FresnsCmdWord::plugin()->verifyAccountToken($includeEmptyCheckArr);

            if ($verifyAccountToken->isErrorResponse()) {
                return $verifyAccountToken->errorResponse();
            }
        }

        if ($dtoWordBody->uid) {
            $verifyUserToken = \FresnsCmdWord::plugin()->verifyUserToken($includeEmptyCheckArr);

            if ($verifyUserToken->isErrorResponse()) {
                return $verifyUserToken->errorResponse();
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
    public function verifyUrlAuthorization($wordBody)
    {
        $dtoWordBody = new VerifyUrlAuthorizationDTO($wordBody);
        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());

        try {
            $urlAuthorizationData = urldecode(base64_decode($dtoWordBody->urlAuthorization));
            $authorizationJson = json_decode($urlAuthorizationData, true) ?? [];

            if (empty($authorizationJson)) {
                return $this->failure(
                    30002,
                    ConfigUtility::getCodeMessage(30002, 'Fresns', $langTag)
                );
            }
        } catch (\Exception $e) {
            return $this->failure(
                31000,
                ConfigUtility::getCodeMessage(31000, 'Fresns', $langTag)
            );
        }

        $headers = [
            'appId' => $authorizationJson['X-Fresns-App-Id'] ?? null,
            'platformId' => $authorizationJson['X-Fresns-Client-Platform-Id'] ?? null,
            'version' => $authorizationJson['X-Fresns-Client-Version'] ?? null,
            'deviceInfo' => json_decode($authorizationJson['X-Fresns-Client-Device-Info'], true),
            'langTag' => $authorizationJson['X-Fresns-Client-Lang-Tag'] ?? ConfigHelper::fresnsConfigDefaultLangTag(),
            'timezone' => $authorizationJson['X-Fresns-Client-Timezone'] ?? null,
            'contentFormat' => $authorizationJson['X-Fresns-Client-Content-Format'] ?? null,
            'aid' => $authorizationJson['X-Fresns-Aid'] ?? null,
            'aidToken' => $authorizationJson['X-Fresns-Aid-Token'] ?? null,
            'uid' => $authorizationJson['X-Fresns-Uid'] ?? null,
            'uidToken' => $authorizationJson['X-Fresns-Uid-Token'] ?? null,
            'signature' => $authorizationJson['X-Fresns-Signature'] ?? null,
            'timestamp' => $authorizationJson['X-Fresns-Signature-Timestamp'] ?? null,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifySign($headers);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getOrigin();
        }

        if ($dtoWordBody->accountLogin) {
            if (empty($headers['aid']) || empty($headers['aidToken'])) {
                return $this->failure(
                    31501,
                    ConfigUtility::getCodeMessage(31501, 'Fresns', $langTag)
                );
            }
        }

        if ($dtoWordBody->userLogin) {
            if (empty($headers['uid']) || empty($headers['uidToken'])) {
                return $this->failure(
                    31601,
                    ConfigUtility::getCodeMessage(31601, 'Fresns', $langTag)
                );
            }
        }

        return $this->success($headers);
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

        new DeviceInfoDTO($dtoWordBody->deviceInfo);

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
            'plugin_fskey' => $dtoWordBody->fskey ?? 'Fresns',
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
            $pluginFskey = ConfigHelper::fresnsConfigByItemKey('send_email_service');
        } else {
            $pluginFskey = ConfigHelper::fresnsConfigByItemKey('send_sms_service');
        }

        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());
        if (empty($pluginFskey)) {
            return $this->failure(
                32100,
                ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
            );
        }

        $fresnsResp = \FresnsCmdWord::plugin($pluginFskey)->sendCode($wordBody);

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
        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());

        if ($dtoWordBody->type == 1) {
            $account = $dtoWordBody->account;
        } else {
            $account = $dtoWordBody->countryCode.$dtoWordBody->account;
        }

        $verifyInfo = VerifyCode::where('template_id', $dtoWordBody->templateId)
            ->where('type', $dtoWordBody->type)
            ->where('account', $account)
            ->where('code', $dtoWordBody->verifyCode)
            ->where('expired_at', '>', now())
            ->where('is_enabled', true)
            ->first();

        if (! $verifyInfo) {
            return $this->failure(
                33203,
                ConfigUtility::getCodeMessage(33203, 'Fresns', $langTag),
            );
        }

        $verifyInfo->update([
            'is_enabled' => false,
        ]);

        return $this->success();
    }

    public function ipInfo($wordBody)
    {
        $dtoWordBody = new IpInfoDTO($wordBody);

        $ip = $dtoWordBody->ip;

        if (strpos($ip, ':') !== false) {
            $ipv4 = null;
            $ipv6 = $ip;
        } else {
            $ipv4 = $ip;
            $ipv6 = null;
        }

        $ipInfo = [
            'networkIpv4' => $ipv4,
            'networkIpv6' => $ipv6,
            'networkPort' => $_SERVER['REMOTE_PORT'],
            'networkTimezone' => null,
            'networkOffset' => null,
            'networkCurrency' => null,
            'networkIsp' => null,
            'networkOrg' => null,
            'networkAs' => null,
            'networkAsName' => null,
            'networkMobile' => false,
            'networkProxy' => false,
            'networkHosting' => false,
            'mapId' => 1,
            'latitude' => null,
            'longitude' => null,
            'scale' => null,
            'continent' => null,
            'continentCode' => null,
            'country' => null,
            'countryCode' => null,
            'region' => null,
            'regionCode' => null,
            'city' => null,
            'district' => null,
            'zip' => null,
        ];

        $pluginFskey = ConfigHelper::fresnsConfigByItemKey('ip_service');

        if ($pluginFskey) {
            $fresnsResp = \FresnsCmdWord::plugin($pluginFskey)->ipInfo($wordBody);

            return $fresnsResp->getOrigin();
        }

        return $this->success($ipInfo);
    }
}
