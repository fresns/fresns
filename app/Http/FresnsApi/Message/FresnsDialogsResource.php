<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Message;

use App\Base\Resources\BaseAdminResource;
use App\Http\Center\Common\GlobalService;
use App\Http\FresnsApi\Content\FsConfig as ContentConfig;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsDb\FresnsDialogMessages\FresnsDialogMessages;
use App\Http\FresnsDb\FresnsDialogs\FresnsDialogs;
use App\Http\FresnsDb\FresnsDialogs\FresnsDialogsConfig;
use App\Http\FresnsDb\FresnsMembers\FresnsMembersConfig;
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
        $mid = GlobalService::getGlobalKey('member_id');
        // Determine whether a member is A or B
        $is_member_A = FresnsDialogs::where('a_member_id', $mid)->where('id', $this->id)->count();
        if ($is_member_A > 0) {
            $member_id = $this->b_member_id;
            $status = $this->a_status;
        } else {
            $member_id = $this->a_member_id;
            $status = $this->b_status;
        }
        $memberInfo = DB::table(FresnsMembersConfig::CFG_TABLE)->where('id', $member_id)->first();
        $member = [];
        $member['deactivate'] = false;
        $member['mid'] = '';
        $member['mname'] = '';
        $member['nickname'] = '';
        $member['avatar'] = $memberInfo->avatar_file_url ?? '';
        $member['decorate'] = '';
        $member['verifiedStatus'] = '';
        $member['verifiedIcon'] = '';
        $member['verifiedDesc'] = '';
        // Default Avatar
        if (empty($member['avatar'])) {
            $defaultIcon = ApiConfigHelper::getConfigByItemKey(FsConfig::DEFAULT_AVATAR);
            $member['avatar'] = $defaultIcon;
        }
        // Deactivate Avatar
        if ($memberInfo) {
            if ($memberInfo->deleted_at != null) {
                $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(FsConfig::DEACTIVATE_AVATAR);
                $member['avatar'] = $deactivateAvatar;
            }
            if ($memberInfo->deleted_at == null) {
                $member['deactivate'] = true;
                $member['mid'] = $memberInfo->uuid;
                $member['mname'] = $memberInfo->name;
                $member['nickname'] = $memberInfo->nickname;
                $member['avatar'] = ApiFileHelper::getImageAvatarUrl($member['avatar']);
                $member['decorate'] = ApiFileHelper::getImageSignUrlByFileIdUrl($memberInfo->decorate_file_id, $memberInfo->decorate_file_url);
                $member['verifiedStatus'] = $memberInfo->verified_status;
                $member['verifiedIcon'] = ApiFileHelper::getImageSignUrlByFileIdUrl($memberInfo->verified_file_id, $memberInfo->verified_file_url);
                $member['verifiedDesc'] = $memberInfo->verified_desc;
            }
        }
        $messageId = $this->latest_message_id;
        $messageTime = $this->latest_message_time;
        $messageBrief = $this->latest_message_brief;

        // Number of unread
        $messageUnread = 0;
        if ($status == 1) {
            $messageUnread = FresnsDialogMessages::where('recv_member_id', $mid)->where('recv_read_at', null)->count();
        }

        // Default Field
        $default = [
            'dialogId' => $dialogId,
            'member' => $member,
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
