<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Message;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\FsDb\FresnsDialogMessages\FresnsDialogMessagesConfig;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\Helpers\ApiFileHelper;
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
        $uid = GlobalService::getGlobalKey('user_id');
        $userInfo = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $this->send_user_id)->first();
        $messageArr = [];
        $sendDeactivate = true;
        $sendUid = $this->send_user_id;
        if ($userInfo) {
            if ($userInfo->deleted_at != null) {
                $sendUid = null;
                $sendDeactivate = false;
            }
        } else {
            $sendUid = null;
            $sendDeactivate = false;
        }
        $sendUserInfo = FresnsUsers::find($sendUid);

        if ($this->message_text) {
            $messageArr['messageId'] = $this->id;
            $messageArr['isMe'] = $this->send_user_id == $uid ? 1 : 2;
            $messageArr['type'] = 1;
            $messageArr['content'] = $this->message_text;
            $messageArr['sendDeactivate'] = $sendDeactivate;
            $messageArr['sendUid'] = $sendUserInfo['uid'] ?? null;
            $messageArr['sendAvatar'] = ApiFileHelper::getUserAvatar($sendUserInfo['uid']);
            $messageArr['sendTime'] = $this->created_at;
        }

        // File Helper
        $fileInfo = [];
        if ($this->file_id) {
            $fileInfo = ApiFileHelper::getMessageFileInfo($this->id, $this->file_id, $uid);
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
