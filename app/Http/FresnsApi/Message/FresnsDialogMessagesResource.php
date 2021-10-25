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
use App\Http\FresnsDb\FresnsDialogMessages\FresnsDialogMessagesConfig;
use App\Http\FresnsDb\FresnsDialogs\FresnsDialogs;
use App\Http\FresnsDb\FresnsDialogs\FresnsDialogsConfig;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMembers\FresnsMembersConfig;
use Illuminate\Support\Facades\DB;

/**
 * List resource config handle.
 */
class FresnsDialogMessagesResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FresnsDialogMessagesConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }

        // Dialog Messages Data
        $mid = GlobalService::getGlobalKey('member_id');
        $memberInfo = DB::table(FresnsMembersConfig::CFG_TABLE)->where('id', $this->send_member_id)->first();
        $messageArr = [];
        $sendDeactivate = true;
        $sendMid = $this->send_member_id;
        if ($memberInfo) {
            if ($memberInfo->deleted_at != null) {
                $sendMid = '';
                $sendDeactivate = false;
            }
        } else {
            $sendMid = '';
            $sendDeactivate = false;
        }
        $sendMemberInfo = FresnsMembers::find($sendMid);

        if ($this->message_text) {
            $messageArr['messageId'] = $this->id;
            $messageArr['isMe'] = $this->send_member_id == $mid ? 1 : 2;
            $messageArr['type'] = 1;
            $messageArr['content'] = $this->message_text;
            $messageArr['sendDeactivate'] = $sendDeactivate;
            $messageArr['sendMid'] = $sendMemberInfo['uuid'] ?? '';
            $messageArr['sendAvatar'] = $memberInfo->avatar_file_url ?? '';

            // Default Avatar
            if (empty($messageArr['sendAvatar'])) {
                $defaultIcon = ApiConfigHelper::getConfigByItemKey(ContentConfig::DEFAULT_AVATAR);
                $messageArr['sendAvatar'] = $defaultIcon;
            }
            // Deactivate Avatar
            if ($memberInfo) {
                if ($memberInfo->deleted_at != null) {
                    $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(ContentConfig::DEACTIVATE_AVATAR);
                    $messageArr['sendAvatar'] = $deactivateAvatar;
                }
            } else {
                $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(ContentConfig::DEACTIVATE_AVATAR);
                $messageArr['sendAvatar'] = $deactivateAvatar;
            }

            $messageArr['sendAvatar'] = ApiFileHelper::getImageAvatarUrl($messageArr['sendAvatar']);
            $messageArr['sendTime'] = $this->created_at;
        }

        // File Helper
        $fileInfo = [];
        if ($this->file_id) {
            $fileInfo = ApiFileHelper::getMessageFileInfo($this->id, $this->file_id, $mid);
        }

        // Default Field
        if ($messageArr) {
            $default = $messageArr;
        } else {
            $default = $fileInfo;
        }

        return $default;
    }
}
