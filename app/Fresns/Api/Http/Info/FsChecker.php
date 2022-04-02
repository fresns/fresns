<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Info;

use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccounts;
use App\Fresns\Api\FsDb\FresnsPluginCallbacks\FresnsPluginCallbacks;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Http\Base\FsApiChecker;
use App\Helpers\ConfigHelper;

class FsChecker extends FsApiChecker
{
    // Check Verify Code
    public static function checkVerifyCode($type, $useType, $account)
    {
        // Sending Message Settings Plugin
        if ($type == 1) {
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('send_email_service');
        } else {
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('send_sms_service');
        }
        if (empty($pluginUniKey)) {
            return self::checkInfo(ErrorCodeService::PLUGINS_CONFIG_ERROR);
        }
        $countryCode = request()->input('countryCode');
        $templateId = request()->input('templateId');
        $templateBlade = ConfigHelper::fresnsConfigByItemKey('verifycode_template'.$templateId);
        if (! $templateBlade) {
            return self::checkInfo(ErrorCodeService::CODE_TEMPLATE_ERROR);
        }
        $templateData = $templateBlade;
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
                    $count = FresnsAccounts::where('email', $account)->count();
                    if ($count > 0) {
                        return self::checkInfo(ErrorCodeService::EMAIL_ERROR);
                    }
                } else {
                    $count = FresnsAccounts::where('pure_phone', $account)->count();
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
                    $count = FresnsAccounts::where('email', $account)->count();
                    if ($count == 0) {
                        return self::checkInfo(ErrorCodeService::EMAIL_EXIST_ERROR);
                    }
                } else {
                    $count = FresnsAccounts::where('pure_phone', $account)->count();
                    if ($count == 0) {
                        return self::checkInfo(ErrorCodeService::PHONE_EXIST_ERROR);
                    }
                }
                break;
            // useType=3
            case 3:
                if (empty(request()->header('aid'))) {
                    return self::checkInfo(ErrorCodeService::ACCOUNT_ERROR);
                }
                if ($type == 1) {
                    $result = self::RuleEmail($account);
                    if (! $result) {
                        return self::checkInfo(ErrorCodeService::EMAIL_REGEX_ERROR);
                    }
                    $accountInfo = FresnsAccounts::where('aid', request()->header('aid'))->first();
                    if (empty($accountInfo)) {
                        return self::checkInfo(ErrorCodeService::ACCOUNT_ERROR);
                    }
                    if ($accountInfo['email']) {
                        return self::checkInfo(ErrorCodeService::EMAIL_BAND_ERROR);
                    }
                } else {
                    $accountInfo = FresnsAccounts::where('aid', request()->header('aid'))->first();
                    if (empty($accountInfo)) {
                        return self::checkInfo(ErrorCodeService::ACCOUNT_ERROR);
                    }
                    if ($accountInfo['pure_phone']) {
                        return self::checkInfo(ErrorCodeService::PHONE_BAND_ERROR);
                    }
                }
                break;
            // useType=4
            case 4:
                if (empty(request()->header('aid'))) {
                    return self::checkInfo(ErrorCodeService::ACCOUNT_ERROR);
                }
                $accountInfo = FresnsAccounts::where('aid', request()->header('aid'))->first();
                if (empty($accountInfo)) {
                    return self::checkInfo(ErrorCodeService::ACCOUNT_ERROR);
                }
                if ($type == 1) {
                    if (! $accountInfo['email']) {
                        return self::checkInfo(ErrorCodeService::EMAIL_EXIST_ERROR);
                    }
                } else {
                    if (! $accountInfo['pure_phone']) {
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
