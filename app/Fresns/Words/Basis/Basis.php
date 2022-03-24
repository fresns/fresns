<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Basis;

use App\Fresns\Words\Basis\DTO\CheckCodeDTO;
use App\Fresns\Words\Basis\DTO\DecodeSignDTO;
use App\Fresns\Words\Basis\DTO\SendCodeDTO;
use App\Fresns\Words\Basis\DTO\UploadSessionLogDTO;
use App\Fresns\Words\Basis\DTO\VerifySignDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\SignHelper;
use App\Models\Account;
use App\Models\SessionKey;
use App\Models\SessionLog;
use App\Models\User;
use App\Models\VerifyCode;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;

class Basis
{
    /**
     * @param  DecodeSignDTO  $urlSign
     * @return mixed
     */
    public function decodeSign(DecodeSignDTO $urlSign)
    {
        $decodeSign = url_decode(base64_decode($urlSign['decodeSignDTO']));

        return $decodeSign;
    }

    public function verifySign(VerifySignDTO $wordBody)
    {
        $checkTokenParam = SignHelper::checkTokenParam($wordBody->token, $wordBody->aid, $wordBody->uid);
        if (! $checkTokenParam) {
            return 'verify not passed';
        }

        $includeEmptyCheckArr = [
            'platform' => $wordBody->platform,
            'version' => $wordBody->version,
            'appId' => $wordBody->appId,
            'timestamp' => $wordBody->timestamp,
            'aid' => $wordBody->aid,
            'uid' => $wordBody->uid,
            'token' => $wordBody->token,
        ];

        $withoutEmptycheckArr = array_filter($includeEmptyCheckArr);

        // Header Signature Expiration Date
        $min = 5; //Expiration time limit (unit: minutes)
        //Determine the timestamp type
        $timestampNum = strlen($wordBody->timestamp);
        if ($timestampNum == 10) {
            $now = time();
            $expiredMin = $min * 60;
        } else {
            $now = intval(microtime(true) * 1000);
            $expiredMin = $min * 60 * 1000;
        }
        if ($now - $wordBody->timestamp > $expiredMin) {
            return 'wrong timestamp';
        }
        $signKey = SessionKey::where('app_id', $wordBody->appId)->first()->app_secret;
        $emptyCheckArr = SignHelper::checkSign($includeEmptyCheckArr, $signKey);
        $checkArr = SignHelper::checkSign($withoutEmptycheckArr, $signKey);
        if ($checkArr !== true || $emptyCheckArr != true) {
            return 'wrong key';
        }

        return 'success';
    }

    public function uploadSessionLog(UploadSessionLogDTO $wordBody)
    {
        if (isset($wordBody->aid)) {
            $accountId = Account::where('aid', '=', $wordBody->aid)->value('id');
            $wordBody->accountId = $accountId;
        }

        if (isset($wordBody->uid)) {
            $userId = User::where('uid', '=', $wordBody->uid)->value('id');
            $wordBody->userId = $userId;
        }

        $input = [
            'plugin_unikey' => $wordBody->pluginUnikey ?? 'Fresns',
            'platform_id' => $wordBody->platform,
            'version' => $wordBody->version,
            'lang_tag' => $wordBody->langTag ?? null,
            'account_id' => $wordBody->accountId ?? null,
            'user_id' => $wordBody->userId ?? null,
            'object_type' => $wordBody->objectType,
            'object_name' => $wordBody->objectName,
            'object_action' => $wordBody->objectAction,
            'object_result' => $wordBody->objectResult,
            'object_order_id' => $wordBody->objectOrderId ?? null,
            'device_info' => $wordBody->deviceInfo ?? null,
            'device_token' => $wordBody->deviceToken ?? null,
            'more_json' => $wordBody->moreJson ?? null,
        ];

        SessionLog::insert($input);

        return 'success';
    }

    public function sendCode($wordBody)
    {
        $dtoWordBody = new SendCodeDTO($wordBody);
        if ($dtoWordBody->type == 1) {
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('send_email_service');
        } else {
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('send_sms_service');
        }
        if (empty($pluginUniKey)) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::ERROR_CODE_20004)::throw();
        }

        return \FresnsCmdWord::plugin($pluginUniKey)->sendCode($wordBody);
    }

    public function checkCode(CheckCodeDTO $wordBody)
    {
        $term = [
            'type' => $wordBody->type,
            'account' => $wordBody->account,
            'code' => $wordBody->type == 1 ? $wordBody->verifyCode : $wordBody->countryCode.$wordBody->account,
            'is_enable' => 1,
        ];
        $verifyInfo = VerifyCode::where($term)->where('expired_at', '>', date('Y-m-d H:i:s'))->first();
        if ($verifyInfo) {
            VerifyCode::where('id', $verifyInfo['id'])->update(['is_enable' => 0]);

            return ['message'=>'success', 'code'=>200, 'data'=>[]];
        } else {
            return ['message'=>'error', 'code'=>200, 'data'=>[]];
        }
    }
}
