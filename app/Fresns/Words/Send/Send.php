<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send;

use App\Fresns\Words\Send\DTO\SendAppNotificationDTO;
use App\Fresns\Words\Send\DTO\SendEmailDTO;
use App\Fresns\Words\Send\DTO\SendNotificationDTO;
use App\Fresns\Words\Send\DTO\SendSmsDTO;
use App\Fresns\Words\Send\DTO\SendWechatMessageDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\CommentLog;
use App\Models\Language;
use App\Models\Notification;
use App\Models\PostLog;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

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

        $this->ensureUnikeyIsNotEmpty(
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

        $this->ensureUnikeyIsNotEmpty(
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('send_sms_service')
        );

        $fresnsResp = \FresnsCmdWord::plugin($pluginUniKey)->sendSms($wordBody);

        return $fresnsResp->getOrigin();
    }

    /**
     * @param $wordBody
     * @return mixed
     *
     * @throws \Throwable
     */
    public function sendNotification($wordBody)
    {
        $dtoWordBody = new SendNotificationDTO($wordBody);

        $service = ConfigHelper::fresnsConfigByItemKey('notifications_service');

        if ($service) {
            $fresnsResp = \FresnsCmdWord::plugin($service)->sendSms($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns Generate Notification

        if ($dtoWordBody->type == Notification::TYPE_LIKE || $dtoWordBody->type == Notification::TYPE_DISLIKE || $dtoWordBody->type == Notification::TYPE_FOLLOW || $dtoWordBody->type == Notification::TYPE_BLOCK) {
            // Such message notifications are not supported
            return $this->failure(22200);
        }

        $user = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody->uid);
        if (! $user) {
            return $this->failure(22201); // User does not exist
        }

        if ($dtoWordBody->isMultilingual ?? 0) {
            if (! Str::isJson(json_decode($dtoWordBody->content, true))) {
                return $this->failure(30003);
            }
        }

        $send = self::generateNotification($user->id, $dtoWordBody->toArray());

        Cache::forget("fresns_api_user_panel_notifications_{$dtoWordBody->uid}");

        if ($send != 0) {
            return $this->failure($send);
        }

        return $this->success();
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

        $this->ensureUnikeyIsNotEmpty(
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

        $this->ensureUnikeyIsNotEmpty(
            $pluginUniKey = ConfigHelper::fresnsConfigByItemKey('send_wechat_service')
        );

        $fresnsResp = \FresnsCmdWord::plugin($pluginUniKey)->sendWechatMessage($wordBody);

        return $fresnsResp->getOrigin();
    }

    protected function ensureUnikeyIsNotEmpty(string $string)
    {
        if (empty($string)) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::PLUGIN_CONFIG_ERROR)::throw();
        }
    }

    // generate notification
    public static function generateNotification(int $userId, array $dtoWordBody): int
    {
        $actionUser = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody['actionUid']);

        $actionObject = $dtoWordBody['actionObject'];
        $fsid = $dtoWordBody['actionFsid'];

        $actionModel = match ($dtoWordBody['actionObject']) {
            Notification::ACTION_OBJECT_USER => PrimaryHelper::fresnsModelByFsid('user', $fsid),
            Notification::ACTION_OBJECT_GROUP => PrimaryHelper::fresnsModelByFsid('group', $fsid),
            Notification::ACTION_OBJECT_HASHTAG => PrimaryHelper::fresnsModelByFsid('hashtag', $fsid),
            Notification::ACTION_OBJECT_POST => PrimaryHelper::fresnsModelByFsid('post', $fsid),
            Notification::ACTION_OBJECT_COMMENT => PrimaryHelper::fresnsModelByFsid('comment', $fsid),
            Notification::ACTION_OBJECT_POST_LOG => PostLog::withTrashed()->where('id', $fsid)->first(),
            Notification::ACTION_OBJECT_COMMENT_LOG => CommentLog::withTrashed()->where('id', $fsid)->first(),
            Notification::ACTION_OBJECT_EXTEND => PrimaryHelper::fresnsModelByFsid('extend', $fsid),
            default => null,
        };

        if ($dtoWordBody['actionObject'] && empty($actionModel)) {
            return 22202; // Action model does not exist
        }

        $notificationQuery = Notification::withTrashed()->where('user_id', $userId)->type($dtoWordBody['type']);

        $notificationQuery->when($actionUser, function ($query, $value) {
            $query->where('action_user_id', $value->id);
        });

        $notificationQuery->when($dtoWordBody['actionType'], function ($query, $value) {
            $query->where('action_type', $value);
        });

        $notificationQuery->when($actionModel, function ($query, $value) use ($actionObject) {
            $query->where('action_object', $actionObject)->where('action_id', $value->id);
        });

        $checkNotification = $notificationQuery->first();

        if ($checkNotification) {
            return 22203; // The same message has been notified
        }

        $isMultilingual = $dtoWordBody['isMultilingual'] ?? 0;

        // notification data
        $notificationData = [
            'user_id' => $userId,
            'type' => $dtoWordBody['type'],
            'content' => $isMultilingual ? null : $dtoWordBody['content'],
            'is_markdown' => $dtoWordBody['isMarkdown'] ?? 0,
            'is_multilingual' => $dtoWordBody['isMultilingual'] ?? 0,
            'is_access_plugin' => $dtoWordBody['isAccessPlugin'] ?? 0,
            'plugin_unikey' => $dtoWordBody['pluginUnikey'] ?? null,
            'action_user_id' => $actionUser?->id ?? null,
            'action_type' => $dtoWordBody['actionType'] ?? null,
            'action_object' => $dtoWordBody['actionObject'] ?? null,
            'action_id' => $actionModel?->id ?? null,
            'action_comment_id' => $dtoWordBody['actionCid'] ? PrimaryHelper::fresnsModelByFsid('comment', $dtoWordBody['actionCid'])?->id : null,
        ];

        $notification = Notification::create($notificationData);

        if ($isMultilingual) {
            $contentArr = json_decode($dtoWordBody['content'], true);

            foreach ($contentArr as $content) {
                $langItems = [
                    'table_name' => 'notifications',
                    'table_column' => 'content',
                    'table_id' => $notification->id,
                    'table_key' => null,
                    'lang_tag' => $content['langTag'],
                    'lang_content' => $content['content'],
                ];

                Language::updateOrCreate($langItems);
            }
        }

        return 0;
    }
}
