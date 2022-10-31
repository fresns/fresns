<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Send;

use App\Fresns\Words\Send\DTO\SendAppNotificationDTO;
use App\Fresns\Words\Send\DTO\SendEmailDTO;
use App\Fresns\Words\Send\DTO\SendNotifyDTO;
use App\Fresns\Words\Send\DTO\SendSmsDTO;
use App\Fresns\Words\Send\DTO\SendWechatMessageDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\CommentLog;
use App\Models\Language;
use App\Models\Notify;
use App\Models\PostLog;
use Illuminate\Support\Str;
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
     * @return mixed
     *
     * @throws \Throwable
     */
    public function sendNotify($wordBody)
    {
        $dtoWordBody = new SendNotifyDTO($wordBody);

        $service = ConfigHelper::fresnsConfigByItemKey('notify_service');

        if ($service) {
            $fresnsResp = \FresnsCmdWord::plugin($service)->sendSms($wordBody);

            return $fresnsResp->getOrigin();
        }

        // Fresns Generate Notify

        if ($dtoWordBody->type == Notify::TYPE_LIKE || $dtoWordBody->type == Notify::TYPE_DISLIKE || $dtoWordBody->type == Notify::TYPE_FOLLOW || $dtoWordBody->type == Notify::TYPE_BLOCK) {
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

        $send = Send::generateNotify($user->id, $dtoWordBody->toArray());

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

    // generate notify
    public static function generateNotify(int $userId, array $dtoWordBody): int
    {
        $actionUser = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody['actionUid']);

        $actionObject = $dtoWordBody['actionObject'];
        $fsid = $dtoWordBody['actionFsid'];

        $actionModel = match ($dtoWordBody['actionObject']) {
            Notify::ACTION_OBJECT_USER => PrimaryHelper::fresnsModelByFsid('user', $fsid),
            Notify::ACTION_OBJECT_GROUP => PrimaryHelper::fresnsModelByFsid('group', $fsid),
            Notify::ACTION_OBJECT_HASHTAG => PrimaryHelper::fresnsModelByFsid('hashtag', $fsid),
            Notify::ACTION_OBJECT_POST => PrimaryHelper::fresnsModelByFsid('post', $fsid),
            Notify::ACTION_OBJECT_COMMENT => PrimaryHelper::fresnsModelByFsid('comment', $fsid),
            Notify::ACTION_OBJECT_POST_LOG => PostLog::withTrashed()->where('id', $fsid)->first(),
            Notify::ACTION_OBJECT_COMMENT_LOG => CommentLog::withTrashed()->where('id', $fsid)->first(),
            Notify::ACTION_OBJECT_EXTEND => PrimaryHelper::fresnsModelByFsid('extend', $fsid),
            default => null,
        };

        if ($dtoWordBody['actionObject'] && empty($actionModel)) {
            return 22202; // Action model does not exist
        }

        $notifyQuery = Notify::withTrashed()->where('user_id', $userId)->type($dtoWordBody['type']);

        $notifyQuery->when($actionUser, function ($query, $value) {
            $query->where('action_user_id', $value->id);
        });

        $notifyQuery->when($dtoWordBody['actionType'], function ($query, $value) {
            $query->where('action_type', $value);
        });

        $notifyQuery->when($actionModel, function ($query, $value) use ($actionObject) {
            $query->where('action_object', $actionObject)->where('action_id', $value->id);
        });

        $checkNotify = $notifyQuery->first();

        if ($checkNotify) {
            return 22203; // The same message has been notified
        }

        $isMultilingual = $dtoWordBody['isMultilingual'] ?? 0;

        // notify data
        $notifyData = [
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

        $notify = Notify::create($notifyData);

        if ($isMultilingual) {
            $contentArr = json_decode($dtoWordBody['content'], true);

            foreach ($contentArr as $content) {
                $langItems = [
                    'table_name' => 'notifies',
                    'table_column' => 'content',
                    'table_id' => $notify->id,
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
