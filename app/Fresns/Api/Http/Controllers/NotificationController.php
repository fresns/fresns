<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Http\DTO\NotificationDTO;
use App\Fresns\Api\Http\DTO\NotificationListDTO;
use App\Helpers\CacheHelper;
use App\Helpers\DateHelper;
use App\Helpers\InteractionHelper;
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Notification;
use App\Utilities\DetailUtility;
use Illuminate\Http\Request;

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

        if (isset($dtoRequest->status)) {
            $notificationQuery->where('is_read', $dtoRequest->status);
        }

        $notifications = $notificationQuery->latest()->paginate($dtoRequest->pageSize ?? 15);

        // filter
        $userOptions = [
            'viewType' => 'quoted',
            'isLiveStats' => false,
            'filter' => [
                'type' => $dtoRequest->filterUserType,
                'keys' => $dtoRequest->filterUserKeys,
            ],
        ];
        $infoOptions = [
            'viewType' => 'quoted',
            'filter' => [
                'type' => $dtoRequest->filterInfoType,
                'keys' => $dtoRequest->filterInfoKeys,
            ],
        ];

        $notificationList = [];
        foreach ($notifications as $notify) {
            $actionUser = null;
            if ($notify->action_user_id) {
                $actionUser = $notify->action_is_anonymous ? InteractionHelper::fresnsUserSubstitutionProfile('anonymous', $dtoRequest->filterUserType, $dtoRequest->filterUserKeys) : DetailUtility::userDetail($notify->actionUser, $langTag, $timezone, $authUserId, $userOptions);
            }

            $contentFsid = match ($notify->type) {
                Notification::TYPE_COMMENT => PrimaryHelper::fresnsModelById('comment', $notify->action_content_id)?->cid,
                Notification::TYPE_QUOTE => PrimaryHelper::fresnsModelById('post', $notify->action_content_id)?->pid,
                default => null,
            };

            $item['nmid'] = $notify->nmid;
            $item['type'] = $notify->type;
            $item['content'] = StrHelper::languageContent($notify->content, $langTag);
            $item['isMarkdown'] = (bool) $notify->is_markdown;
            $item['isMention'] = (bool) $notify->is_mention;
            $item['isAccessApp'] = (bool) $notify->is_access_app;
            $item['appUrl'] = PluginHelper::fresnsPluginUrlByFskey($notify->app_fskey);
            $item['actionUser'] = $actionUser;
            $item['actionUserIsAnonymous'] = (bool) $notify->action_is_anonymous;
            $item['actionType'] = $notify->action_type;
            $item['actionTarget'] = $notify->action_target;
            $item['actionInfo'] = null;
            $item['contentFsid'] = $contentFsid;
            $item['datetime'] = DateHelper::fresnsDateTimeByTimezone($notify->created_at, $timezone, $langTag);
            $item['datetimeFormat'] = DateHelper::fresnsFormatDateTime($notify->created_at, $timezone, $langTag);
            $item['timeAgo'] = DateHelper::fresnsHumanReadableTime($notify->created_at, $langTag);
            $item['readStatus'] = (bool) $notify->is_read;

            if ($notify->action_target && $notify->action_id) {
                $actionInfo = match ($notify->action_target) {
                    Notification::ACTION_TARGET_USER => DetailUtility::userDetail($notify->user, $langTag, $timezone, $authUserId, $infoOptions),
                    Notification::ACTION_TARGET_GROUP => DetailUtility::groupDetail($notify->group, $langTag, $timezone, $authUserId, $infoOptions),
                    Notification::ACTION_TARGET_HASHTAG => DetailUtility::hashtagDetail($notify->hashtag, $langTag, $timezone, $authUserId, $infoOptions),
                    Notification::ACTION_TARGET_GEOTAG => DetailUtility::geotagDetail($notify->geotag, $langTag, $timezone, $authUserId, $infoOptions),
                    Notification::ACTION_TARGET_POST => DetailUtility::postDetail($notify->post, $langTag, $timezone, $authUserId, $infoOptions),
                    Notification::ACTION_TARGET_COMMENT => DetailUtility::commentDetail($notify->comment, $langTag, $timezone, $authUserId, $infoOptions),
                    Notification::ACTION_TARGET_EXTEND => $notify->extend?->getExtendInfo($langTag),
                    default => null,
                };

                $item['actionInfo'] = $actionInfo;
            }

            $notificationList[] = $item;
        }

        return $this->fresnsPaginate($notificationList, $notifications->total(), $notifications->perPage());
    }

    // readStatus
    public function readStatus(Request $request)
    {
        $dtoRequest = new NotificationDTO($request->all());

        $authUser = $this->user();

        if ($dtoRequest->type == 'all') {
            Notification::where('user_id', $authUser->id)->when($dtoRequest->notificationType, function ($query, $value) {
                $query->where('type', $value);
            })->where('is_read', 0)->update([
                'is_read' => 1,
            ]);
        } else {
            $nmidArr = array_filter(explode(',', $dtoRequest->notificationIds));

            Notification::where('user_id', $authUser->id)->whereIn('nmid', $nmidArr)->where('is_read', 0)->update([
                'is_read' => 1,
            ]);
        }

        CacheHelper::forgetFresnsKey("fresns_user_overview_notifications_{$authUser->uid}", 'fresnsUsers');

        return $this->success();
    }

    // delete
    public function delete(Request $request)
    {
        $dtoRequest = new NotificationDTO($request->all());

        $authUser = $this->user();

        if ($dtoRequest->type == 'all') {
            Notification::where('user_id', $authUser->id)->where('type', $dtoRequest->notificationType)->delete();
        } else {
            $nmidArr = array_filter(explode(',', $dtoRequest->notificationIds));

            Notification::where('user_id', $authUser->id)->whereIn('id', $nmidArr)->delete();
        }

        CacheHelper::forgetFresnsKey("fresns_user_overview_notifications_{$authUser->uid}", 'fresnsUsers');

        return $this->success();
    }
}
