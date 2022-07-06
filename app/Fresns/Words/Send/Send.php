<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send;

use App\Fresns\Words\Send\DTO\SendAppNotificationDTO;
use App\Fresns\Words\Send\DTO\SendEmailDTO;
use App\Fresns\Words\Send\DTO\SendSmsDTO;
use App\Fresns\Words\Send\DTO\SendWechatMessageDTO;
use App\Helpers\ConfigHelper;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;

class Send
{
    use CmdWordResponseTrait;

    /**
     * @param $wordBody
     * @return string
     *
     * @throws \Throwable
     */
    public function sendEmail($wordBody)
    {
        $dtoWordBody = new SendEmailDTO($wordBody);

        $this->ensureUnikeyIsNotEmpry(
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('send_email_service')
        );

        $fresnsResp = \FresnsCmdWord::plugin($pluginUniKey)->sendEmail($wordBody);

        return $fresnsResp->getOrigin();
    }

    /**
     * @param $wordBody
     * @return mixed
     *
     * @throws \Throwable
     */
    public function sendSms($wordBody)
    {
        $dtoWordBody = new SendSmsDTO($wordBody);

        $this->ensureUnikeyIsNotEmpry(
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('send_sms_service')
        );

        $fresnsResp = \FresnsCmdWord::plugin($pluginUniKey)->sendSms($wordBody);

        return $fresnsResp->getOrigin();
    }

    /**
     * @param $wordBody
     * @return string
     *
     * @throws \Throwable
     */
    public function sendAppNotification($wordBody)
    {
        $dtoWordBody = new SendAppNotificationDTO($wordBody);

        $channelMap = [
            1 => 'send_ios_service',
            2 => 'send_android_service',
        ];

        $itemKey = $channelMap[$dtoWordBody->channel];

        $this->ensureUnikeyIsNotEmpry(
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey($itemKey)
        );

        $fresnsResp = \FresnsCmdWord::plugin($pluginUniKey)->sendAppNotification($wordBody);

        return $fresnsResp->getOrigin();
    }

    /**
     * @param $wordBody
     * @return string
     *
     * @throws \Throwable
     */
    public function sendWechatMessage($wordBody)
    {
        $dtoWordBody = new SendWechatMessageDTO($wordBody);

        $this->ensureUnikeyIsNotEmpry(
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('send_wechat_service')
        );

        $fresnsResp = \FresnsCmdWord::plugin($pluginUniKey)->sendWechatMessage($wordBody);

        return $fresnsResp->getOrigin();
    }

    protected function ensureUnikeyIsNotEmpry(string $string)
    {
        if (empty($string)) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::PLUGIN_CONFIG_ERROR)::throw();
        }
    }
}
