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
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\CommentLog;
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

        CacheHelper::forgetFresnsKey("fresns_user_overview_notifications_{$dtoWordBody->uid}", 'fresnsUsers');

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
            3 => 'desktop_notifications_service',
        ];

        $itemKey = $channelMap[$dtoWordBody->channel];

        $this->ensureFskeyIsNotEmpty(
            $pluginFskey = ConfigHelper::fresnsConfigByItemKey($itemKey)
        );

        $fresnsResp = \FresnsCmdWord::plugin($pluginFskey)->sendAppNotification($wordBody);

        return $fresnsResp->getOrigin();
    }

    protected function ensureFskeyIsNotEmpty(?string $string = null)
    {
        if (empty($string)) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::PLUGIN_CONFIG_ERROR)::throw();
        }
    }

    // generate notification
    private static function generateNotification(int $userId, array $dtoWordBody): int
    {
        $actionUser = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody['actionUid']);

        $actionTarget = $dtoWordBody['actionTarget'] ?? null;
        $fsid = $dtoWordBody['actionFsid'] ?? null;

        $actionModel = match ($actionTarget) {
            Notification::ACTION_TARGET_USER => PrimaryHelper::fresnsModelByFsid('user', $fsid),
            Notification::ACTION_TARGET_GROUP => PrimaryHelper::fresnsModelByFsid('group', $fsid),
            Notification::ACTION_TARGET_HASHTAG => PrimaryHelper::fresnsModelByFsid('hashtag', $fsid),
            Notification::ACTION_TARGET_POST => PrimaryHelper::fresnsModelByFsid('post', $fsid),
            Notification::ACTION_TARGET_COMMENT => PrimaryHelper::fresnsModelByFsid('comment', $fsid),
            Notification::ACTION_TARGET_POST_LOG => PostLog::withTrashed()->where('id', $fsid)->first(),
            Notification::ACTION_TARGET_COMMENT_LOG => CommentLog::withTrashed()->where('id', $fsid)->first(),
            Notification::ACTION_TARGET_EXTEND => PrimaryHelper::fresnsModelByFsid('extend', $fsid),
            default => null,
        };

        if ($actionTarget && empty($actionModel)) {
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

            $notificationQuery->when($actionModel, function ($query, $value) use ($actionTarget) {
                $query->where('action_target', $actionTarget)->where('action_id', $value->id);
            });

            $notificationQuery->where('is_read', 0);

            $checkNotification = $notificationQuery->first();

            if ($checkNotification) {
                return 22203; // The same message has been notified
            }
        }

        $contentId = match ($dtoWordBody['type']) {
            Notification::TYPE_COMMENT => PrimaryHelper::fresnsPrimaryId('comment', $dtoWordBody['contentFsid']),
            Notification::TYPE_QUOTE => PrimaryHelper::fresnsPrimaryId('post', $dtoWordBody['contentFsid']),
            default => null,
        };

        // notification data
        $notificationData = [
            'user_id' => $userId,
            'type' => $dtoWordBody['type'],
            'content' => $dtoWordBody['content'] ?? [],
            'is_markdown' => $dtoWordBody['isMarkdown'] ?? 0,
            'is_access_app' => $dtoWordBody['isAccessApp'] ?? 0,
            'app_fskey' => $dtoWordBody['appFskey'] ?? null,
            'action_user_id' => $actionUser?->id ?? null,
            'action_is_anonymous' => $dtoWordBody['actionIsAnonymous'] ?? false,
            'action_type' => $dtoWordBody['actionType'] ?? null,
            'action_target' => $actionTarget,
            'action_id' => $actionModel?->id,
            'action_content_id' => $contentId,
        ];

        Notification::create($notificationData);

        return 0;
    }
}
