<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Message;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\FsDb\FresnsDialogMessages\FresnsDialogMessages;
use App\Fresns\Api\FsDb\FresnsDialogs\FresnsDialogs;
use App\Fresns\Api\FsDb\FresnsDialogs\FresnsDialogsConfig;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiFileHelper;
use Illuminate\Support\Facades\DB;

/**
 * List resource config handle.
 */
class FresnsDialogsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FresnsDialogsConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }

        // Dialog Data
        $dialogId = $this->id;
        $uid = GlobalService::getGlobalKey('user_id');
        // Determine whether a user is A or B
        $is_user_A = FresnsDialogs::where('a_user_id', $uid)->where('id', $this->id)->count();
        if ($is_user_A > 0) {
            $user_id = $this->b_user_id;
            $status = $this->a_status;
        } else {
            $user_id = $this->a_user_id;
            $status = $this->b_status;
        }
        $userInfo = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $user_id)->first();
        $user = [];
        $user['deactivate'] = false;
        $user['uid'] = null;
        $user['username'] = null;
        $user['nickname'] = null;
        $user['avatar'] = $userInfo->avatar_file_url ?? null;
        $user['decorate'] = null;
        $user['verifiedStatus'] = null;
        $user['verifiedIcon'] = null;
        $user['verifiedDesc'] = null;
        // Default Avatar
        if (empty($user['avatar'])) {
            $defaultIcon = ApiConfigHelper::getConfigByItemKey(FsConfig::DEFAULT_AVATAR);
            $user['avatar'] = $defaultIcon;
        }
        // Deactivate Avatar
        if ($userInfo) {
            if ($userInfo->deleted_at != null) {
                $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::DEACTIVATE_AVATAR);
                $user['avatar'] = $deactivateAvatar;
            }
            if ($userInfo->deleted_at == null) {
                $user['deactivate'] = true;
                $user['uid'] = $userInfo->uid;
                $user['username'] = $userInfo->username;
                $user['nickname'] = $userInfo->nickname;
                $user['avatar'] = ApiFileHelper::getImageAvatarUrl($user['avatar']);
                $user['decorate'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userInfo->decorate_file_id, $userInfo->decorate_file_url);
                $user['verifiedStatus'] = $userInfo->verified_status;
                $user['verifiedIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($userInfo->verified_file_id, $userInfo->verified_file_url);
                $user['verifiedDesc'] = $userInfo->verified_desc;
            }
        }
        $messageId = $this->latest_message_id;
        $messageTime = $this->latest_message_time;
        $messageBrief = $this->latest_message_brief;

        // Number of unread
        $messageUnread = 0;
        if ($status == 1) {
            $messageUnread = FresnsDialogMessages::where('recv_user_id', $uid)->where('recv_read_at', null)->count();
        }

        // Default Field
        $default = [
            'dialogId' => $dialogId,
            'user' => $user,
            'messageId' => $messageId,
            'messageTime' => $messageTime,
            'messageBrief' => $messageBrief,
            'messageUnread' => $messageUnread,
            'status' => $status,
        ];

        // Merger
        $arr = $default;

        return $arr;
    }
}
