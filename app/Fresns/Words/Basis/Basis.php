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
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;

class Basis
{
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
        $urlSign = urldecode(base64_decode($dtoWordBody->urlSign));
        $urlSign = json_decode($urlSign, true);

        if (empty($urlSign->aid)) {
            return [
                'code' => 31501,
                'message' => ConfigUtility::getCodeMessage(31501, 'Fresns', $langTag),
                'data' => [],
            ];
        }

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->verifySign($urlSign);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getOrigin();
        }

        return [
            'code' => 0,
            'message' => 'success',
            'data' => $urlSign,
        ];
    }

    /**
     * @param $wordBody
     * @return array|string
     *
     * @throws \Throwable
     */
    public function verifySign($wordBody)
    {
        $dtoWordBody = new VerifySignDTO($wordBody);
        $appId = $dtoWordBody->appId;
        $langTag = \request()->header('langTag', config('app.locale'));

        if (isset($dtoWordBody->aid)) {
            $verifySessionTokenArr = array_filter([
                'platform'=>$dtoWordBody->platform,
                'aid'=>$dtoWordBody->aid,
                'uid'=>$dtoWordBody->uid ?? 0,
                'token'=>$dtoWordBody->token,
            ]);
            \FresnsCmdWord::plugin()->verifySessionToken($verifySessionTokenArr);
        }

        $includeEmptyCheckArr = [
            'platform' => $dtoWordBody->platform,
            'version' => $dtoWordBody->version,
            'appId' => $dtoWordBody->appId,
            'timestamp' => $dtoWordBody->timestamp,
            'sign' => $dtoWordBody->sign,
            'aid' => $dtoWordBody->aid ?? '',
            'uid' => $dtoWordBody->uid ?? '',
            'token' => $dtoWordBody->token ?? '',
        ];

        $withoutEmptyCheckArr = array_filter($includeEmptyCheckArr);

        // Header Signature Expiration Date
        $min = 5; //Expiration time limit (unit: minutes)

        //Determine the timestamp type
        $timestampNum = strlen($dtoWordBody->timestamp);
        if ($timestampNum == 10) {
            $now = time();
            $expiredMin = $min * 60;
        } else {
            $now = intval(microtime(true) * 1000);
            $expiredMin = $min * 60 * 1000;
        }
        if ($now - $dtoWordBody->timestamp > $expiredMin) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_PARAM_ERROR)::throw();
        }

        $appSecret = SessionKey::where('app_id', $appId)->value('app_secret');
        if (empty($appSecret)) {
            return [
                'code' => 31301,
                'message' => ConfigUtility::getCodeMessage(31301, 'Fresns', $langTag),
                'data' => [
                    'appId'=>$dtoWordBody->appId,
                ],
            ];
        }

        $checkArr = SignHelper::checkSign($withoutEmptyCheckArr, $appSecret);
        if ($checkArr !== true) {
            return [
                'code' => 31302,
                'message' => ConfigUtility::getCodeMessage(31302, 'Fresns', $langTag), ,
                'data' => [
                    'sign'=>$checkArr,
                ],
            ];
        }

        return [
            'code' => 0,
            'message' => 'success',
            'data' => [],
        ];
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
            'plugin_unikey' => $dtoWordBody->pluginUnikey ?? 'Fresns',
            'platform_id' => $dtoWordBody->platform,
            'version' => $dtoWordBody->version,
            'lang_tag' => $dtoWordBody->langTag ?? null,
            'account_id' => $dtoWordBody->accountId ?? null,
            'user_id' => $dtoWordBody->userId ?? null,
            'object_type' => $dtoWordBody->objectType,
            'object_name' => $dtoWordBody->objectName,
            'object_action' => $dtoWordBody->objectAction,
            'object_result' => $dtoWordBody->objectResult,
            'object_order_id' => $dtoWordBody->objectOrderId ?? null,
            'device_info' => $dtoWordBody->deviceInfo ?? null,
            'device_token' => $dtoWordBody->deviceToken ?? null,
            'more_json' => $dtoWordBody->moreJson ?? null,
        ];

        SessionLog::insert($input);

        return [
            'code' => 0,
            'message' => 'success',
            'data' => [],
        ];
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
        if (empty($pluginUniKey)) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::PLUGIN_CONFIG_ERROR)::throw();
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
        $term = [
            'type' => $dtoWordBody->type,
            'account' => $dtoWordBody->account,
            'code' => $dtoWordBody->type == 1 ? $dtoWordBody->verifyCode : $dtoWordBody->countryCode.$dtoWordBody->account,
            'is_enable' => 1,
        ];
        $verifyInfo = VerifyCode::where($term)->where('expired_at', '>', date('Y-m-d H:i:s'))->first();
        if ($verifyInfo) {
            VerifyCode::where('id', $verifyInfo['id'])->update(['is_enable' => 0]);

            return [
                'code' => 0,
                'message' => 'success',
                'data' => [],
            ];
        } else {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_DATA_ERROR)::throw();
        }
    }
}
