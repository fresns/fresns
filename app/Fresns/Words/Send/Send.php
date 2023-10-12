<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send;

use App\Fresns\Words\Send\DTO\SendAppNotificationDTO;
use App\Fresns\Words\Send\DTO\SendEmailDTO;
use App\Fresns\Words\Send\DTO\SendNotificationDTO;
use App\Fresns\Words\Send\DTO\SendSmsDTO;
use App\Fresns\Words\Send\DTO\SendWechatMessageDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\CommentLog;
use App\Models\Language;
use App\Models\Notification;
use App\Models\PostLog;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Str;

class Send
{
    use CmdWordResponseTrait;

    public function sendEmail($wordBody)
    {
        $dtoWordBody = new SendEmailDTO($wordBody);

        $this->ensureFskeyIsNotEmpty(
            $pluginFskey = ConfigHelper::fresnsConfigByItemKey('send_email_service')
        );

        $fresnsResp = \FresnsCmdWord::plugin($pluginFskey)->sendEmail($wordBody);

        return $fresnsResp->getOrigin();
    }

    public function sendSms($wordBody)
    {
        $dtoWordBody = new SendSmsDTO($wordBody);

        $this->ensureFskeyIsNotEmpty(
            $pluginFskey = ConfigHelper::fresnsConfigByItemKey('send_sms_service')
        );

        $fresnsResp = \FresnsCmdWord::plugin($pluginFskey)->sendSms($wordBody);

        return $fresnsResp->getOrigin();
    }

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

        CacheHelper::forgetFresnsKey("fresns_api_user_panel_notifications_{$dtoWordBody->uid}", 'fresnsUsers');

        if ($send != 0) {
            return $this->failure($send);
        }

        return $this->success();
    }

    public function sendAppNotification($wordBody)
    {
        $dtoWordBody = new SendAppNotificationDTO($wordBody);

        $channelMap = [
            1 => 'ios_notifications_service',
            2 => 'android_notifications_service',
        ];

        $itemKey = $channelMap[$dtoWordBody->channel];

        $this->ensureFskeyIsNotEmpty(
            $pluginFskey = ConfigHelper::fresnsConfigByItemKey($itemKey)
        );

        $fresnsResp = \FresnsCmdWord::plugin($pluginFskey)->sendAppNotification($wordBody);

        return $fresnsResp->getOrigin();
    }

    public function sendWechatMessage($wordBody)
    {
        $dtoWordBody = new SendWechatMessageDTO($wordBody);

        $this->ensureFskeyIsNotEmpty(
            $pluginFskey = ConfigHelper::fresnsConfigByItemKey('wechat_notifications_service')
        );

        $fresnsResp = \FresnsCmdWord::plugin($pluginFskey)->sendWechatMessage($wordBody);

        return $fresnsResp->getOrigin();
    }

    protected function ensureFskeyIsNotEmpty(?string $string = null)
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

        if (! in_array($dtoWordBody['type'], [
            Notification::TYPE_SYSTEM,
            Notification::TYPE_RECOMMEND,
        ])) {
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

            $notificationQuery->where('is_read', 0);

            $checkNotification = $notificationQuery->first();

            if ($checkNotification) {
                return 22203; // The same message has been notified
            }
        }

        $isMultilingual = $dtoWordBody['isMultilingual'] ?? 0;

        $contentId = match ($dtoWordBody['type']) {
            Notification::TYPE_COMMENT => PrimaryHelper::fresnsCommentIdByCid($dtoWordBody['contentFsid']),
            Notification::TYPE_QUOTE => PrimaryHelper::fresnsPostIdByPid($dtoWordBody['contentFsid']),
            default => null,
        };

        // notification data
        $notificationData = [
            'user_id' => $userId,
            'type' => $dtoWordBody['type'],
            'content' => $isMultilingual ? null : $dtoWordBody['content'],
            'is_markdown' => $dtoWordBody['isMarkdown'] ?? 0,
            'is_multilingual' => $dtoWordBody['isMultilingual'] ?? 0,
            'is_access_plugin' => $dtoWordBody['isAccessPlugin'] ?? 0,
            'plugin_fskey' => $dtoWordBody['pluginFskey'] ?? null,
            'action_user_id' => $actionUser?->id ?? null,
            'action_is_anonymous' => $dtoWordBody['actionIsAnonymous'] ?? false,
            'action_type' => $dtoWordBody['actionType'] ?? null,
            'action_object' => $dtoWordBody['actionObject'] ?? null,
            'action_id' => $actionModel?->id ?? null,
            'action_content_id' => $contentId,
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
