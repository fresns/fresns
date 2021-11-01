<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Info;

use App\Base\Checkers\BaseChecker;
use App\Http\Center\Common\ErrorCodeService;
use App\Http\FresnsApi\Base\FresnsBaseChecker;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsDb\FresnsPluginCallbacks\FresnsPluginCallbacks;
use App\Http\FresnsDb\FresnsUsers\FresnsUsers;

class FsChecker extends FresnsBaseChecker
{
    // Check Verify Code
    public static function checkVerifyCode($type, $useType, $account)
    {
        // Sending Message Settings Plugin
        if ($type == 1) {
            $pluginUniKey = ApiConfigHelper::getConfigByItemKey('send_email_service');
        } else {
            $pluginUniKey = ApiConfigHelper::getConfigByItemKey('send_sms_service');
        }
        if (empty($pluginUniKey)) {
            return self::checkInfo(ErrorCodeService::PLUGINS_CONFIG_ERROR);
        }
        $countryCode = request()->input('countryCode');
        $templateId = request()->input('templateId');
        $templateBlade = ApiConfigHelper::getConfigByItemKey('verifycode_template'.$templateId);
        if (! $templateBlade) {
            return self::checkInfo(ErrorCodeService::CODE_TEMPLATE_ERROR);
        }
        $templateData = json_decode($templateBlade, true);
        $emailArr = [];
        $phoneArr = [];
        foreach ($templateData as $t) {
            if ($t['type'] == 'email') {
                $emailArr = $t;
            }
            if ($t['type'] == 'sms') {
                $phoneArr = $t;
            }
        }
        if ($type == 1) {
            if (! $emailArr) {
                return self::checkInfo(ErrorCodeService::CODE_TEMPLATE_ERROR);
            }
            if (! $emailArr['isEnable']) {
                return self::checkInfo(ErrorCodeService::CODE_TEMPLATE_ERROR);
            }
        } else {
            if (! $phoneArr) {
                return self::checkInfo(ErrorCodeService::CODE_TEMPLATE_ERROR);
            }
            if (! $phoneArr['isEnable']) {
                return self::checkInfo(ErrorCodeService::CODE_TEMPLATE_ERROR);
            }
        }

        /*
         * Code Usage
         * https://fresns.org/api/info/sendVerifyCode.html
         */
        switch ($useType) {
            // useType=1
            case 1:
                if ($type == 1) {
                    $result = self::RuleEmail($account);
                    if (! $result) {
                        return self::checkInfo(ErrorCodeService::EMAIL_REGEX_ERROR);
                    }
                    $count = FresnsUsers::where('email', $account)->count();
                    if ($count > 0) {
                        return self::checkInfo(ErrorCodeService::EMAIL_ERROR);
                    }
                } else {
                    $count = FresnsUsers::where('pure_phone', $account)->count();
                    if ($count > 0) {
                        return self::checkInfo(ErrorCodeService::PHONE_ERROR);
                    }
                }
                break;
            // useType=2
            case 2:
                if ($type == 1) {
                    $result = self::RuleEmail($account);
                    if (! $result) {
                        return self::checkInfo(ErrorCodeService::EMAIL_REGEX_ERROR);
                    }
                    $count = FresnsUsers::where('email', $account)->count();
                    if ($count == 0) {
                        return self::checkInfo(ErrorCodeService::EMAIL_EXIST_ERROR);
                    }
                } else {
                    $count = FresnsUsers::where('pure_phone', $account)->count();
                    if ($count == 0) {
                        return self::checkInfo(ErrorCodeService::PHONE_EXIST_ERROR);
                    }
                }
                break;
            // useType=3
            case 3:
                if (empty(request()->header('uid'))) {
                    return self::checkInfo(ErrorCodeService::USER_ERROR);
                }
                if ($type == 1) {
                    $result = self::RuleEmail($account);
                    if (! $result) {
                        return self::checkInfo(ErrorCodeService::EMAIL_REGEX_ERROR);
                    }
                    $userInfo = FresnsUsers::where('uuid', request()->header('uid'))->first();
                    if (empty($userInfo)) {
                        return self::checkInfo(ErrorCodeService::USER_ERROR);
                    }
                    if ($userInfo['email']) {
                        return self::checkInfo(ErrorCodeService::EMAIL_BAND_ERROR);
                    }
                } else {
                    $userInfo = FresnsUsers::where('uuid', request()->header('uid'))->first();
                    if (empty($userInfo)) {
                        return self::checkInfo(ErrorCodeService::USER_ERROR);
                    }
                    if ($userInfo['pure_phone']) {
                        return self::checkInfo(ErrorCodeService::PHONE_BAND_ERROR);
                    }
                }
                break;
            // useType=4
            case 4:
                if (empty(request()->header('uid'))) {
                    return self::checkInfo(ErrorCodeService::USER_ERROR);
                }
                $userInfo = FresnsUsers::where('uuid', request()->header('uid'))->first();
                if (empty($userInfo)) {
                    return self::checkInfo(ErrorCodeService::USER_ERROR);
                }
                if ($type == 1) {
                    if (! $userInfo['email']) {
                        return self::checkInfo(ErrorCodeService::EMAIL_EXIST_ERROR);
                    }
                } else {
                    if (! $userInfo['pure_phone']) {
                        return self::checkInfo(ErrorCodeService::PHONE_EXIST_ERROR);
                    }
                }
                break;
            // default
            default:
                if ($type == 1) {
                    $result = self::RuleEmail($account);
                    if (! $result) {
                        return self::checkInfo(ErrorCodeService::EMAIL_REGEX_ERROR);
                    }
                } else {
                    // code
                }
                break;
        }
    }

    // Check Plugin Callback
    public static function checkPluginCallback($uuid)
    {
        $callInfo = FresnsPluginCallbacks::where('uuid', $uuid)->first();
        if (! $callInfo) {
            return self::checkInfo(ErrorCodeService::CALLBACK_UUID_ERROR);
        }
        $createdTimes = strtotime($callInfo['created_at']) + (10 * 60);
        if ($createdTimes < time()) {
            return self::checkInfo(ErrorCodeService::CALLBACK_TIME_ERROR);
        }
        if ($callInfo['status'] != FsConfig::NOT_USE_CALLBACKS) {
            return self::checkInfo(ErrorCodeService::CALLBACK_STATUS_ERROR);
        }
    }

    // Check Phone Number
    public static function RulePhone($phone)
    {
        $result = preg_match("/^1[34578]{1}\d{9}$/", $phone);

        return $result;
    }

    // Check Email
    public static function RuleEmail($email)
    {
        $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/";
        preg_match($pattern, $email, $matches);

        return $matches;
    }
}
