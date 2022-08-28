<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\LanguageHelper;
use App\Helpers\PluginHelper;
use App\Models\GroupAdmin;

trait GroupServiceTrait
{
    public function getGroupInfo(?string $langTag = null, ?string $timezone = null)
    {
        $groupData = $this;
        $parentGroup = $this->category;

        $configKey = ConfigHelper::fresnsConfigByItemKeys([
            'website_group_detail_path',
            'site_url',
        ]);

        $info['gid'] = $groupData->gid;
        $info['url'] = $configKey['site_url'].'/'.$configKey['website_group_detail_path'].'/'.$groupData->gid;
        $info['type'] = $groupData->type;
        $info['gname'] = LanguageHelper::fresnsLanguageByTableId('groups', 'name', $groupData->id, $langTag);
        $info['description'] = LanguageHelper::fresnsLanguageByTableId('groups', 'description', $groupData->id, $langTag);
        $info['cover'] = FileHelper::fresnsFileUrlByTableColumn($groupData->cover_file_id, $groupData->cover_file_url);
        $info['banner'] = FileHelper::fresnsFileUrlByTableColumn($groupData->banner_file_id, $groupData->banner_file_url);
        $info['recommend'] = (bool) $groupData->is_recommend;
        $info['mode'] = $groupData->type_mode;
        $info['find'] = $groupData->type_find;
        $info['followType'] = $groupData->type_follow;
        $info['followUrl'] = ! empty($groupData->plugin_unikey) ? PluginHelper::fresnsPluginUrlByUnikey($groupData->plugin_unikey) : null;
        $info['parentGid'] = $parentGroup?->gid ?? null;
        $info['category'] = $parentGroup?->getCategoryInfo($langTag) ?? null;
        $info['likeCount'] = $groupData->like_count;
        $info['dislikeCount'] = $groupData->dislike_count;
        $info['followCount'] = $groupData->follow_count;
        $info['blockCount'] = $groupData->block_count;
        $info['postCount'] = $groupData->post_count;
        $info['postDigestCount'] = $groupData->post_digest_count;
        $info['permissions'] = $groupData->permissions;
        $info['createDate'] = date(ConfigHelper::fresnsConfigDateFormat($langTag), strtotime(DateHelper::fresnsDateTimeByTimezone($groupData->created_at, $timezone, $langTag)));

        return $info;
    }

    public function getGroupAdmins(?string $langTag = null, ?string $timezone = null)
    {
        $groupData = $this;

        $admins = GroupAdmin::with('user')->where('group_id', $groupData->id)->get();

        $adminList = null;
        foreach ($admins as $admin) {
            $userProfile = $admin->user->getUserProfile($timezone);
            $userMainRole = $admin->user->getUserMainRole($langTag, $timezone);

            $adminList[] = array_merge($userProfile, $userMainRole);
        }

        return $adminList;
    }

    public function getCategoryInfo(?string $langTag = null)
    {
        $parentGroup = $this;

        $info['gid'] = $parentGroup->gid;
        $info['gname'] = LanguageHelper::fresnsLanguageByTableId('groups', 'name', $parentGroup->id, $langTag);
        $info['description'] = LanguageHelper::fresnsLanguageByTableId('groups', 'description', $parentGroup->id, $langTag);
        $info['cover'] = FileHelper::fresnsFileUrlByTableColumn($parentGroup->cover_file_id, $parentGroup->cover_file_url);
        $info['banner'] = FileHelper::fresnsFileUrlByTableColumn($parentGroup->banner_file_id, $parentGroup->banner_file_url);

        return $info;
    }

    public function getCoverUrl()
    {
        return FileHelper::fresnsFileUrlByTableColumn($this->cover_file_id, $this->cover_file_url);
    }

    public function getBannerUrl()
    {
        return FileHelper::fresnsFileUrlByTableColumn($this->banner_file_id, $this->banner_file_url);
    }
}
