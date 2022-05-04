<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Message;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\FsDb\FresnsComments\FresnsComments;
use App\Fresns\Api\FsDb\FresnsNotifies\FresnsNotifiesConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPosts;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\DB;

/**
 * List resource config handle.
 */
class FresnsNotifiesResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FresnsNotifiesConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }

        // Notify Data
        $messageId = $this->id;
        $sourceType = $this->source_type;
        $sourceClass = $this->source_class;
        $sourceId = $this->source_id;
        if ($sourceClass == 1) {
            $sourceFsid = FresnsPosts::where('id', $sourceId)->value('pid');
        } else {
            $sourceFsid = FresnsComments::where('id', $sourceId)->value('cid');
        }
        $user = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $this->source_user_id)->first();
        $sourceUser = [];
        if ($user) {
            $user = FresnsUsers::find($this->source_user_id);
            $sourceUser = [
                'uid' => $user['uid'] ?? null,
                'username' => $user->username ?? null,
                'nickname' => $user->nickname ?? null,
                'avatar' => ApiFileHelper::getUserAvatar($user->uid),
                'decorate' => ApiFileHelper::getImageSignUrlByFileIdUrl($user->decorate_file_id, $user->decorate_file_url),
                'verifiedStatus' => $user->verified_status ?? 1,
                'verifiedIcon' => ApiFileHelper::getImageSignUrlByFileIdUrl($user->verified_file_id, $user->verified_file_url),
                'verifiedDesc' => $user->verified_desc ?? null,
            ];
        } else {
            $sourceUser = [
                'uid' => null,
                'username' => null,
                'nickname' => null,
                'avatar' => ConfigHelper::fresnsConfigByItemKey('deactivate_avatar'),
                'decorate' => null,
                'verifiedStatus' => 1,
                'verifiedIcon' => null,
                'verifiedDesc' => null,
            ];
        }
        $sourceBrief = $this->source_brief;
        $accessUrl = $this->access_url;
        $status = $this->status;
        $time = DateHelper::fresnsFormatDateTime($this->created_at);

        // Default Field
        $default = [
            'notifyId' => $messageId,
            'sourceType' => $sourceType,
            'sourceClass' => $sourceClass,
            'sourceFsid' => $sourceFsid ?? null,
            'sourceUser' => $sourceUser,
            'sourceBrief' => $sourceBrief,
            'accessUrl' => $accessUrl,
            'status' => $status,
            'time' => $time,
        ];

        // Merger
        $arr = $default;

        return $arr;
    }
}
