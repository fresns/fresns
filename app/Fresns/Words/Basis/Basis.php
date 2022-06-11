<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basis;

use App\Fresns\Words\Basis\DTO\CheckCodeDTO;
use App\Fresns\Words\Basis\DTO\SendCodeDTO;
use App\Fresns\Words\Basis\DTO\UploadSessionLogDTO;
use App\Fresns\Words\Basis\DTO\VerifySignDTO;
use App\Fresns\Words\Basis\DTO\VerifyUrlSignDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\SignHelper;
use App\Models\Account;
use App\Models\SessionKey;
use App\Models\SessionLog;
use App\Models\User;
use App\Models\VerifyCode;
use App\Utilities\ConfigUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;

class Basis
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
        $langTag = \request()->header('langTag', config('app.locale'));

        $keyInfo = SessionKey::where('app_id', $dtoWordBody->appId)->isEnable()->first();

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

        if ($checkSign !== true) {
            return $this->failure(
                31302,
                ConfigUtility::getCodeMessage(31302, 'Fresns', $langTag),
            );
        }

        if (isset($dtoWordBody->aid)) {
            $verifySessionTokenArr = array_filter([
                'platformId' => $dtoWordBody->platformId,
                'aid' => $dtoWordBody->aid,
                'uid' => $dtoWordBody->uid ?? null,
                'token' => $dtoWordBody->token,
            ]);
            \FresnsCmdWord::plugin()->verifySessionToken($verifySessionTokenArr);
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
        $langTag = \request()->header('langTag', config('app.locale'));

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
        if (isset($dtoWordBody->aid)) {
            $accountId = Account::where('aid', '=', $dtoWordBody->aid)->value('id');
            $dtoWordBody->accountId = $accountId;
        }

        if (isset($dtoWordBody->uid)) {
            $userId = User::where('uid', '=', $dtoWordBody->uid)->value('id');
            $dtoWordBody->userId = $userId;
        }

        $input = [
            'type' => $dtoWordBody->type,
            'plugin_unikey' => $dtoWordBody->pluginUnikey ?? 'Fresns',
            'platform_id' => $dtoWordBody->platformId,
            'version' => $dtoWordBody->version,
            'lang_tag' => $dtoWordBody->langTag ?? null,
            'account_id' => $dtoWordBody->accountId ?? null,
            'user_id' => $dtoWordBody->userId ?? null,
            'object_name' => $dtoWordBody->objectName,
            'object_action' => $dtoWordBody->objectAction,
            'object_result' => $dtoWordBody->objectResult,
            'object_order_id' => $dtoWordBody->objectOrderId ?? null,
            'device_info' => $dtoWordBody->deviceInfo ?? null,
            'device_token' => $dtoWordBody->deviceToken ?? null,
            'more_json' => $dtoWordBody->moreJson ?? null,
        ];

        SessionLog::insert($input);

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

        $langTag = \request()->header('langTag', config('app.locale'));
        if (empty($pluginUniKey)) {
            return $this->failure(
                32100,
                ConfigUtility::getCodeMessage(32100, 'Fresns', $langTag),
            );
        }

        return \FresnsCmdWord::plugin($pluginUniKey)->sendCode($wordBody);
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
        $langTag = \request()->header('langTag', config('app.locale'));

        if ($dtoWordBody->type == 1) {
            $account = $dtoWordBody->account;
        } else {
            $account = $dtoWordBody->countryCode.$dtoWordBody->account;
        }

        $term = [
            'type' => $dtoWordBody->type,
            'account' => $account,
            'code' => $dtoWordBody->verifyCode,
            'is_enable' => 1,
        ];
        $verifyInfo = VerifyCode::where($term)->where('expired_at', '>', date('Y-m-d H:i:s'))->first();

        if ($verifyInfo) {
            VerifyCode::where('id', $verifyInfo->id)->update([
                'is_enable' => 0,
            ]);

            return $this->success();
        } else {
            return $this->failure(
                33104,
                ConfigUtility::getCodeMessage(33104, 'Fresns', $langTag),
            );
        }
    }
}
