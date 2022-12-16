<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Http\DTO\NotificationDTO;
use App\Fresns\Api\Http\DTO\NotificationListDTO;
use App\Fresns\Api\Services\CommentService;
use App\Fresns\Api\Services\GroupService;
use App\Fresns\Api\Services\HashtagService;
use App\Fresns\Api\Services\PostService;
use App\Fresns\Api\Services\UserService;
use App\Helpers\DateHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new NotificationListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()->id;

        $typeArr = array_filter(explode(',', $dtoRequest->types));

        $notificationQuery = Notification::with('actionUser')->where('user_id', $authUserId);

        $notificationQuery->when($typeArr, function ($query, $value) {
            $query->whereIn('type', $value);
        });

        $notificationQuery->when($dtoRequest->status, function ($query, $value) {
            $query->where('is_read', $value);
        });

        $notifications = $notificationQuery->latest()->paginate($request->get('pageSize', 15));

        $userService = new UserService();
        $groupService = new GroupService();
        $hashtagService = new HashtagService();
        $postService = new PostService();
        $commentService = new CommentService();

        $notificationList = [];
        foreach ($notifications as $notification) {
            $item['id'] = $notification->id;
            $item['type'] = $notification->type;
            $item['content'] = $notification->is_multilingual ? LanguageHelper::fresnsLanguageByTableId('notifications', 'content', $notification->id, $langTag) : $notification->content;
            $item['isMarkdown'] = (bool) $notification->is_markdown;
            $item['isAccessPlugin'] = (bool) $notification->is_access_plugin;
            $item['pluginUrl'] = ! empty($notification->plugin_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($notification->plugin_unikey) : null;
            $item['actionUser'] = $notification->action_user_id ? $userService->userData($notification?->actionUser, $langTag, $timezone, $authUserId) : null;
            $item['actionType'] = $notification->action_type;
            $item['actionObject'] = $notification->action_object;
            $item['actionInfo'] = null;
            $item['actionCid'] = $notification->action_comment_id ? PrimaryHelper::fresnsModelById('comment', $notification?->action_comment_id)?->cid : null;
            $item['datetime'] = DateHelper::fresnsDateTimeByTimezone($notification->created_at, $timezone, $langTag);
            $item['datetimeFormat'] = DateHelper::fresnsFormatDateTime($notification->created_at, $timezone, $langTag);
            $item['readStatus'] = (bool) $notification->is_read;

            if ($notification->action_object && $notification->action_id) {
                $actionInfo = match ($notification->action_object) {
                    default => null,
                    Notification::ACTION_OBJECT_USER => $userService->userData($notification?->user, $langTag, $timezone, $authUserId),
                    Notification::ACTION_OBJECT_GROUP => $groupService->groupData($notification?->group, $langTag, $timezone, $authUserId),
                    Notification::ACTION_OBJECT_HASHTAG => $hashtagService->hashtagData($notification?->hashtag, $langTag, $timezone, $authUserId),
                    Notification::ACTION_OBJECT_POST => $postService->postData($notification?->post, 'list', $langTag, $timezone, $authUserId),
                    Notification::ACTION_OBJECT_COMMENT => $commentService->commentData($notification?->comment, 'list', $langTag, $timezone, false, $authUserId),
                    Notification::ACTION_OBJECT_POST_LOG => $postService->postLogData($notification?->postLog, 'list', $langTag, $timezone),
                    Notification::ACTION_OBJECT_COMMENT_LOG => $commentService->commentLogData($notification?->commentLog, 'list', $langTag, $timezone),
                    Notification::ACTION_OBJECT_EXTEND => $notification?->extend->getExtendInfo($langTag),
                };

                $item['actionInfo'] = $actionInfo;
            }

            $notificationList[] = $item;
        }

        return $this->fresnsPaginate($notificationList, $notifications->total(), $notifications->perPage());
    }

    // markAsRead
    public function markAsRead(Request $request)
    {
        $dtoRequest = new NotificationDTO($request->all());

        $authUser = $this->user();

        if ($dtoRequest->type == 'all') {
            Notification::where('user_id', $authUser->id)->where('type', $dtoRequest->notificationType)->where('is_read', 0)->update([
                'is_read' => 1,
            ]);
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->notificationIds));

            Notification::where('user_id', $authUser->id)->whereIn('id', $idArr)->where('is_read', 0)->update([
                'is_read' => 1,
            ]);
        }

        Cache::forget("fresns_api_user_panel_notifications_{$authUser->uid}");

        return $this->success();
    }

    // delete
    public function delete(Request $request)
    {
        $dtoRequest = new NotificationDTO($request->all());

        $authUserId = $this->user()->id;

        if ($dtoRequest->type == 'all') {
            Notification::where('user_id', $authUserId)->where('type', $dtoRequest->notificationType)->delete();
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->notificationIds));

            Notification::where('user_id', $authUserId)->whereIn('id', $idArr)->delete();
        }

        return $this->success();
    }
}
