<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basic;

use App\Fresns\Words\Basic\DTO\CheckCodeDTO;
use App\Fresns\Words\Basic\DTO\CheckHeadersDTO;
use App\Fresns\Words\Basic\DTO\CreateSessionLogDTO;
use App\Fresns\Words\Basic\DTO\DeviceInfoDTO;
use App\Fresns\Words\Basic\DTO\GetCallbackContentDTO;
use App\Fresns\Words\Basic\DTO\IpInfoDTO;
use App\Fresns\Words\Basic\DTO\SendCodeDTO;
use App\Fresns\Words\Basic\DTO\UpdateOrCreateCallbackContentDTO;
use App\Fresns\Words\Basic\DTO\VerifyAccessTokenDTO;
use App\Fresns\Words\Basic\DTO\VerifySignDTO;
use App\Helpers\AppHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\SignHelper;
use App\Models\App;
use App\Models\SessionKey;
use App\Models\SessionLog;
use App\Models\TempCallbackContent;
use App\Models\TempVerifyCode;
use App\Utilities\ConfigUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;

class Basic
{
    use CmdWordResponseTrait;

    public function checkHeaders()
    {
        $headers = [
            'appId' => \request()->header('X-Fresns-App-Id'),
            'platformId' => \request()->header('X-Fresns-Client-Platform-Id'),
            'version' => \request()->header('X-Fresns-Client-Version'),
            'deviceInfo' => \request()->header('X-Fresns-Client-Device-Info'),
            'timezone' => \request()->header('X-Fresns-Client-Timezone'),
            'langTag' => \request()->header('X-Fresns-Client-Lang-Tag'),
            'contentFormat' => \request()->header('X-Fresns-Client-Content-Format'),
            'aid' => \request()->header('X-Fresns-Aid'),
            'aidToken' => \request()->header('X-Fresns-Aid-Token'),
            'uid' => \request()->header('X-Fresns-Uid'),
            'uidToken' => \request()->header('X-Fresns-Uid-Token'),
            'signature' => \request()->header('X-Fresns-Signature'),
            'timestamp' => \request()->header('X-Fresns-Signature-Timestamp'),
        ];

        // check header
        $dtoWordBody = new CheckHeadersDTO($headers);

        // check sign
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifySign($headers);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        // device info
        try {
            $stringify = base64_decode($dtoWordBody->deviceInfo, true);
            $deviceInfoStringify = preg_replace('/[\x00-\x1F\x80-\xFF]/', ' ', $stringify); // sanitize JSON String
            $deviceInfo = json_decode($deviceInfoStringify, true);

            if (empty($deviceInfo)) {
                $deviceInfo = [];
            }
        } catch (\Exception $e) {
            $deviceInfo = [];
            info('device info error', [$dtoWordBody->deviceInfo]);
        }

        // check deviceInfo
        new DeviceInfoDTO($deviceInfo);

        $headers['deviceInfo'] = $deviceInfo;

        return $this->success($headers);
    }

    public function verifySign($wordBody)
    {
        $dtoWordBody = new VerifySignDTO($wordBody);

        $keyInfo = PrimaryHelper::fresnsModelByFsid('key', $dtoWordBody->appId);
        $keyType = $dtoWordBody->verifyType ?? SessionKey::TYPE_CORE;
        $keyFskey = $dtoWordBody->verifyFskey;

        if (empty($keyInfo) || ! $keyInfo->is_enabled) {
            return $this->failure(31301, ConfigUtility::getCodeMessage(31301));
        }

        if ($keyInfo->type != $keyType) {
            return $this->failure(31304, ConfigUtility::getCodeMessage(31304));
        }

        if ($keyType == SessionKey::TYPE_APP && $keyInfo->app_fskey != $keyFskey) {
            return $this->failure(31304, ConfigUtility::getCodeMessage(31304));
        }

        if ($keyInfo->platform_id != $dtoWordBody->platformId) {
            return $this->failure(31102, ConfigUtility::getCodeMessage(31102));
        }

        $timestamp = (int) $dtoWordBody->timestamp;

        if (strlen($timestamp) == 13) {
            $timestamp /= 1000;
            $timestamp = intval($timestamp);
        }

        $diff = time() - $timestamp;

        if ($diff > 600) {
            return $this->failure(31303, ConfigUtility::getCodeMessage(31303));
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

        $checkSign = SignHelper::checkSign($withoutEmptyCheckArr, $keyInfo->app_key);

        if (! $checkSign) {
            return $this->failure(31302, ConfigUtility::getCodeMessage(31302));
        }

        if ($dtoWordBody->uid) {
            $verifyUserToken = \FresnsCmdWord::plugin()->verifyUserToken($includeEmptyCheckArr);

            if ($verifyUserToken->isErrorResponse()) {
                return $verifyUserToken->getErrorResponse();
            }

            return $this->success();
        }

        if ($dtoWordBody->aid) {
            $verifyAccountToken = \FresnsCmdWord::plugin()->verifyAccountToken($includeEmptyCheckArr);

            if ($verifyAccountToken->isErrorResponse()) {
                return $verifyAccountToken->getErrorResponse();
            }
        }

        return $this->success();
    }

    public function verifyAccessToken($wordBody)
    {
        $dtoWordBody = new VerifyAccessTokenDTO($wordBody);

        try {
            $accessTokenData = base64_decode(urldecode($dtoWordBody->accessToken));
            $accessTokenJson = json_decode($accessTokenData, true) ?? [];

            if (empty($accessTokenJson)) {
                return $this->failure(30002, ConfigUtility::getCodeMessage(30002));
            }

            if (! is_array($accessTokenJson)) {
                return $this->failure(30004, ConfigUtility::getCodeMessage(30004));
            }
        } catch (\Exception $e) {
            return $this->failure(31000, ConfigUtility::getCodeMessage(31000));
        }

        $langTag = $accessTokenJson['X-Fresns-Client-Lang-Tag'] ?? AppHelper::getLangTag();

        // check deviceInfo
        try {
            $stringify = base64_decode($accessTokenJson['X-Fresns-Client-Device-Info'], true);
            $deviceInfoStringify = preg_replace('/[\x00-\x1F\x80-\xFF]/', ' ', $stringify); // sanitize JSON String
            $deviceInfo = json_decode($deviceInfoStringify, true);

            if (empty($deviceInfo)) {
                $deviceInfo = [];
            }
        } catch (\Exception $e) {
            $deviceInfo = [];
            info('access device info error', [$accessTokenJson['X-Fresns-Client-Device-Info']]);
        }

        new DeviceInfoDTO($deviceInfo);

        // check headers
        $headers = [
            'appId' => $accessTokenJson['X-Fresns-App-Id'] ?? null,
            'platformId' => $accessTokenJson['X-Fresns-Client-Platform-Id'] ?? null,
            'version' => $accessTokenJson['X-Fresns-Client-Version'] ?? null,
            'deviceInfo' => $deviceInfo,
            'langTag' => $langTag,
            'timezone' => $accessTokenJson['X-Fresns-Client-Timezone'] ?? null,
            'contentFormat' => $accessTokenJson['X-Fresns-Client-Content-Format'] ?? null,
            'aid' => $accessTokenJson['X-Fresns-Aid'] ?? null,
            'aidToken' => $accessTokenJson['X-Fresns-Aid-Token'] ?? null,
            'uid' => $accessTokenJson['X-Fresns-Uid'] ?? null,
            'uidToken' => $accessTokenJson['X-Fresns-Uid-Token'] ?? null,
            'signature' => $accessTokenJson['X-Fresns-Signature'] ?? null,
            'timestamp' => $accessTokenJson['X-Fresns-Signature-Timestamp'] ?? null,
        ];

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifySign($headers);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        if ($dtoWordBody->accountLogin) {
            if (empty($headers['aid']) || empty($headers['aidToken'])) {
                return $this->failure(31501, ConfigUtility::getCodeMessage(31501, 'Fresns', $langTag));
            }
        }

        if ($dtoWordBody->userLogin) {
            if (empty($headers['uid']) || empty($headers['uidToken'])) {
                return $this->failure(31601, ConfigUtility::getCodeMessage(31601, 'Fresns', $langTag));
            }
        }

        return $this->success($headers);
    }

    public function createSessionLog($wordBody)
    {
        $dtoWordBody = new CreateSessionLogDTO($wordBody);

        new DeviceInfoDTO($dtoWordBody->deviceInfo);

        $accountId = null;
        if (isset($dtoWordBody->aid)) {
            $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);
        }

        $userId = null;
        if (isset($dtoWordBody->uid)) {
            $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        }

        $input = [
            'type' => $dtoWordBody->type,
            'app_fskey' => $dtoWordBody->fskey ?? 'Fresns',
            'platform_id' => $dtoWordBody->platformId,
            'version' => $dtoWordBody->version,
            'app_id' => $dtoWordBody->appId ?? null,
            'lang_tag' => $dtoWordBody->langTag ?? null,
            'account_id' => $accountId,
            'user_id' => $userId,
            'action_name' => $dtoWordBody->actionName,
            'action_desc' => $dtoWordBody->actionDesc,
            'action_state' => $dtoWordBody->actionState,
            'action_id' => $dtoWordBody->actionId ?? null,
            'device_info' => $dtoWordBody->deviceInfo ?? null,
            'device_token' => $dtoWordBody->deviceToken ?? null,
            'login_token' => $dtoWordBody->loginToken ?? null,
            'more_info' => $dtoWordBody->moreInfo ?? null,
        ];

        SessionLog::create($input);

        return $this->success();
    }

    public function sendCode($wordBody)
    {
        $dtoWordBody = new SendCodeDTO($wordBody);
        if ($dtoWordBody->type == 1) {
            $pluginFskey = ConfigHelper::fresnsConfigByItemKey('send_email_service');
        } else {
            $pluginFskey = ConfigHelper::fresnsConfigByItemKey('send_sms_service');
        }

        if (empty($pluginFskey)) {
            return $this->failure(32100, ConfigUtility::getCodeMessage(32100));
        }

        $fresnsResp = \FresnsCmdWord::plugin($pluginFskey)->sendCode($wordBody);

        return $fresnsResp->getOrigin();
    }

    public function checkCode($wordBody)
    {
        $dtoWordBody = new CheckCodeDTO($wordBody);

        if ($dtoWordBody->type == TempVerifyCode::TYPE_EMAIL) {
            $account = $dtoWordBody->account;
        } else {
            $account = $dtoWordBody->countryCode.$dtoWordBody->account;
        }

        $verifyInfo = TempVerifyCode::where('template_id', $dtoWordBody->templateId)
            ->where('type', $dtoWordBody->type)
            ->where('account', $account)
            ->where('code', $dtoWordBody->verifyCode)
            ->where('expired_at', '>', now())
            ->where('is_enabled', true)
            ->first();

        if (! $verifyInfo) {
            return $this->failure(33203, ConfigUtility::getCodeMessage(33203));
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

    public function updateOrCreateCallbackContent($wordBody)
    {
        $dtoWordBody = new UpdateOrCreateCallbackContentDTO($wordBody);

        // plugin
        $plugin = App::where('fskey', $dtoWordBody->fskey)->first();

        if (empty($plugin)) {
            return $this->failure(32101, ConfigUtility::getCodeMessage(32101));
        }

        if (! $plugin->is_enabled) {
            return $this->failure(32102, ConfigUtility::getCodeMessage(32102));
        }

        // callback
        TempCallbackContent::updateOrCreate([
            'app_fskey' => $dtoWordBody->fskey,
            'ulid' => $dtoWordBody->ulid,
        ], [
            'type' => $dtoWordBody->type ?? TempCallbackContent::TYPE_UNKNOWN,
            'content' => $dtoWordBody->content ?? [],
            'retention_days' => $dtoWordBody->retentionDays ?? 1,
            'is_enabled' => true,
        ]);

        return $this->success($dtoWordBody->content);
    }

    public function getCallbackContent($wordBody)
    {
        $dtoWordBody = new GetCallbackContentDTO($wordBody);

        // plugin
        $plugin = App::where('fskey', $dtoWordBody->fskey)->first();

        if (empty($plugin)) {
            return $this->failure(32101, ConfigUtility::getCodeMessage(32101));
        }

        if (! $plugin->is_enabled) {
            return $this->failure(32102, ConfigUtility::getCodeMessage(32102));
        }

        // callback
        $callbackData = TempCallbackContent::where('app_fskey', $dtoWordBody->fskey)->where('ulid', $dtoWordBody->ulid)->first();

        if (empty($callbackData)) {
            return $this->failure(32303, ConfigUtility::getCodeMessage(32303));
        }

        if (! $callbackData->is_enabled) {
            return $this->failure(32204, ConfigUtility::getCodeMessage(32204));
        }

        if (empty($callbackData->content)) {
            return $this->failure(32206, ConfigUtility::getCodeMessage(32206));
        }

        if ($dtoWordBody->timeout) {
            $checkTime = $callbackData->created_at->addMinutes($dtoWordBody->timeout);

            if ($checkTime->lt(now())) {
                return $this->failure(32203, ConfigUtility::getCodeMessage(32203));
            }
        }

        if ($dtoWordBody->markAsUsed) {
            $callbackData->update([
                'is_enabled' => false,
            ]);
        }

        return $this->success($callbackData->content);
    }
}
