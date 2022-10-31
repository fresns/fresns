<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Http\DTO\NotifyDTO;
use App\Fresns\Api\Http\DTO\NotifyListDTO;
use App\Fresns\Api\Services\CommentService;
use App\Fresns\Api\Services\GroupService;
use App\Fresns\Api\Services\HashtagService;
use App\Fresns\Api\Services\PostService;
use App\Fresns\Api\Services\UserService;
use App\Helpers\DateHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Notify;
use Illuminate\Http\Request;

class NotifyController extends Controller
{
    // list
    public function list(Request $request)
    {
        $dtoRequest = new NotifyListDTO($request->all());

        $langTag = $this->langTag();
        $timezone = $this->timezone();
        $authUserId = $this->user()->id;

        $typeArr = array_filter(explode(',', $dtoRequest->types));

        $notifyQuery = Notify::with('actionUser')->where('user_id', $authUserId);

        $notifyQuery->when($typeArr, function ($query, $value) {
            $query->whereIn('type', $value);
        });

        $notifyQuery->when($dtoRequest->status, function ($query, $value) {
            $query->where('is_read', $value);
        });

        $notifies = $notifyQuery->latest()->paginate($request->get('pageSize', 15));

        $userService = new UserService();
        $groupService = new GroupService();
        $hashtagService = new HashtagService();
        $postService = new PostService();
        $commentService = new CommentService();

        $notifyList = [];
        foreach ($notifies as $notify) {
            $item['notifyId'] = $notify->id;
            $item['type'] = $notify->type;
            $item['content'] = $notify->is_multilingual ? LanguageHelper::fresnsLanguageByTableId('notifies', 'content', $notify->id, $langTag) : $notify->content;
            $item['isMarkdown'] = (bool) $notify->is_markdown;
            $item['isAccessPlugin'] = (bool) $notify->is_access_plugin;
            $item['pluginUrl'] = ! empty($notify->plugin_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($notify->plugin_unikey) : null;
            $item['actionUser'] = $notify->action_user_id ? $userService->userData($notify?->actionUser, $langTag, $timezone, $authUserId) : null;
            $item['actionType'] = $notify->action_type;
            $item['actionObject'] = $notify->action_object;
            $item['actionInfo'] = null;
            $item['actionCid'] = $notify->action_comment_id ? PrimaryHelper::fresnsModelById('comment', $notify?->action_comment_id)?->cid : null;
            $item['notifyTime'] = DateHelper::fresnsDateTimeByTimezone($notify->created_at, $timezone, $langTag);
            $item['notifyTimeFormat'] = DateHelper::fresnsFormatDateTime($notify->created_at, $timezone, $langTag);
            $item['readStatus'] = (bool) $notify->is_read;

            if ($notify->action_object && $notify->action_id) {
                $actionInfo = match ($notify->action_object) {
                    default => null,
                    Notify::ACTION_OBJECT_USER => $userService->userData($notify?->user, $langTag, $timezone, $authUserId),
                    Notify::ACTION_OBJECT_GROUP => $groupService->groupData($notify?->group, $langTag, $timezone, $authUserId),
                    Notify::ACTION_OBJECT_HASHTAG => $hashtagService->hashtagData($notify?->hashtag, $langTag, $timezone, $authUserId),
                    Notify::ACTION_OBJECT_POST => $postService->postData($notify?->post, 'list', $langTag, $timezone, $authUserId),
                    Notify::ACTION_OBJECT_COMMENT => $commentService->commentData($notify?->comment, 'list', $langTag, $timezone, $authUserId),
                    Notify::ACTION_OBJECT_POST_LOG => $postService->postLogData($notify?->postLog, 'list', $langTag, $timezone),
                    Notify::ACTION_OBJECT_COMMENT_LOG => $commentService->commentLogData($notify?->commentLog, 'list', $langTag, $timezone),
                    Notify::ACTION_OBJECT_EXTEND => $notify?->extend->getExtendInfo($langTag),
                };

                $item['actionInfo'] = $actionInfo;
            }

            $notifyList[] = $item;
        }

        return $this->fresnsPaginate($notifyList, $notifies->total(), $notifies->perPage());
    }

    // markAsRead
    public function markAsRead(Request $request)
    {
        $dtoRequest = new NotifyDTO($request->all());

        $authUserId = $this->user()->id;

        if ($dtoRequest->type == 'all') {
            Notify::where('user_id', $authUserId)->where('type', $dtoRequest->notifyType)->where('is_read', 0)->update([
                'is_read' => 1,
            ]);
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->notifyIds));

            Notify::where('user_id', $authUserId)->whereIn('id', $idArr)->where('is_read', 0)->update([
                'is_read' => 1,
            ]);
        }

        return $this->success();
    }

    // delete
    public function delete(Request $request)
    {
        $dtoRequest = new NotifyDTO($request->all());

        $authUserId = $this->user()->id;

        if ($dtoRequest->type == 'all') {
            Notify::where('user_id', $authUserId)->where('type', $dtoRequest->notifyType)->delete();
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->notifyIds));

            Notify::where('user_id', $authUserId)->whereIn('id', $idArr)->delete();
        }

        return $this->success();
    }
}
