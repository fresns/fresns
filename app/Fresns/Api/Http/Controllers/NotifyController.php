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
use App\Fresns\Api\Services\HeaderService;
use App\Fresns\Api\Services\PostService;
use App\Fresns\Api\Services\UserService;
use App\Helpers\DateHelper;
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
        $headers = HeaderService::getHeaders();

        $authUserId = null;
        if (! empty($headers['uid'])) {
            $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);
        }

        $readStatus = $dtoRequest->status ?: 0;
        $typeArr = array_filter(explode(',', $dtoRequest->types));

        $notifyQuery = Notify::with('actionUser')->where('user_id', $authUserId)->where('is_read', $readStatus);

        if ($typeArr) {
            $notifyQuery->whereIn('type', $typeArr);
        }

        $notifies = $notifyQuery->latest()->paginate($request->get('pageSize', 15));

        $userService = new UserService();
        $groupService = new GroupService();
        $hashtagService = new HashtagService();
        $postService = new PostService();
        $commentService = new CommentService();

        $notifyList = null;
        foreach ($notifies as $notify) {
            $item['notifyId'] = $notify->id;
            $item['type'] = $notify->type;
            $item['content'] = $notify->content;
            $item['isMarkdown'] = (bool) $notify->is_markdown;
            $item['pluginUrl'] = null;
            $item['isAccessPlugin'] = (bool) $notify->is_access_plugin;
            $item['actionUser'] = null;
            $item['actionType'] = $notify->action_type;
            $item['actionInfo'] = null;
            $info['notifyTime'] = DateHelper::fresnsFormatDateTime($notify->created_at, $headers['timezone'], $headers['langTag']);
            $info['notifyTimeFormat'] = DateHelper::fresnsFormatTime($notify->created_at, $headers['langTag']);
            $item['status'] = (bool) $notify->is_read;

            if ($notify->is_access_plugin) {
                $item['pluginUrl'] = ! empty($notify->plugin_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($notify->plugin_unikey) : null;
            }

            if ($notify->action_user_id) {
                $item['actionUser'] = $notify->actionUser?->getUserProfile($headers['langTag'], $headers['timezone']);
            }

            if ($notify->action_id) {
                $actionInfo = match ($notify->action_type) {
                    default => null,
                    Notify::ACTION_TYPE_USER => $userService->userList($notify->user, $headers['langTag'], $headers['timezone'], $authUserId),
                    Notify::ACTION_TYPE_GROUP => $groupService->groupList($notify->group, $headers['langTag'], $headers['timezone'], $authUserId),
                    Notify::ACTION_TYPE_HASHTAG => $hashtagService->hashtagList($notify->hashtag, $headers['langTag'], $authUserId),
                    Notify::ACTION_TYPE_POST => $postService->postDetail($notify->post, 'list', $headers['langTag'], $headers['timezone'], $authUserId),
                    Notify::ACTION_TYPE_COMMENT => $commentService->commentDetail($notify->comment, 'list', $headers['langTag'], $headers['timezone'], $authUserId),
                };

                $item['actionInfo'] = $actionInfo;
            }

            $notifyList[] = $item;
        }

        return $this->fresnsPaginate($notifyList, $notifies->total(), $notifies->perPage());
    }

    // read
    public function read(Request $request)
    {
        $dtoRequest = new NotifyDTO($request->all());
        $headers = HeaderService::getHeaders();

        $authUserId = null;
        if (! empty($headers['uid'])) {
            $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);
        }

        if ($dtoRequest->markType == 'all') {
            Notify::where('user_id', $authUserId)->where('type', $dtoRequest->type)->where('is_read', 0)->update([
                'is_read' => 1,
            ]);
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->ids));

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
        $headers = HeaderService::getHeaders();

        $authUserId = null;
        if (! empty($headers['uid'])) {
            $authUserId = PrimaryHelper::fresnsUserIdByUid($headers['uid']);
        }

        if ($dtoRequest->markType == 'all') {
            Notify::where('user_id', $authUserId)->where('type', $dtoRequest->type)->delete();
        } else {
            $idArr = array_filter(explode(',', $dtoRequest->ids));

            Notify::where('user_id', $authUserId)->whereIn('id', $idArr)->delete();
        }

        return $this->success();
    }
}
