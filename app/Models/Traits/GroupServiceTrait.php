<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\StrHelper;
use App\Models\Group;

trait GroupServiceTrait
{
    public function getGroupInfo(?string $langTag = null): array
    {
        $groupData = $this;
        $parentGroup = $this->parentGroup;

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'website_group_detail_path',
            'site_url',
            'group_like_public_count',
            'group_dislike_public_count',
            'group_follow_public_count',
            'group_block_public_count',
        ]);

        $siteUrl = $configKeys['site_url'] ?? config('app.url');

        $info['gid'] = $groupData->gid;
        $info['url'] = $siteUrl.'/'.$configKeys['website_group_detail_path'].'/'.$groupData->gid;
        $info['name'] = StrHelper::languageContent($groupData->name, $langTag);
        $info['description'] = StrHelper::languageContent($groupData->description, $langTag);
        $info['cover'] = FileHelper::fresnsFileUrlByTableColumn($groupData->cover_file_id, $groupData->cover_file_url);
        $info['banner'] = FileHelper::fresnsFileUrlByTableColumn($groupData->banner_file_id, $groupData->banner_file_url);
        $info['recommend'] = (bool) $groupData->is_recommend;
        $info['privacy'] = ($groupData->privacy == Group::PRIVACY_PUBLIC) ? 'public' : 'private';
        $info['privateEndAfter'] = $groupData->private_end_after;
        $info['visibility'] = $groupData->visibility;
        $info['parentGid'] = $parentGroup?->gid ?? null;
        $info['parentInfo'] = $groupData->getParentInfo($langTag);
        $info['subgroupCount'] = $groupData->subgroup_count;
        $info['viewCount'] = $groupData->view_count;
        $info['likeCount'] = $configKeys['group_like_public_count'] ? $groupData->like_count : null;
        $info['dislikeCount'] = $configKeys['group_dislike_public_count'] ? $groupData->dislike_count : null;
        $info['followCount'] = $configKeys['group_follow_public_count'] ? $groupData->follow_count : null;
        $info['blockCount'] = $configKeys['group_block_public_count'] ? $groupData->block_count : null;
        $info['postCount'] = $groupData->post_count;
        $info['postDigestCount'] = $groupData->post_digest_count;
        $info['commentCount'] = $groupData->comment_count;
        $info['commentDigestCount'] = $groupData->comment_digest_count;
        $info['createdDatetime'] = $groupData->created_at;
        $info['moreInfo'] = $groupData->more_info;

        return $info;
    }

    public function getGroupAdmins(): ?array
    {
        $adminUsers = $this->admins;

        $adminList = [];
        foreach ($adminUsers as $user) {
            $adminList[] = $user->getUserProfile();
        }

        return $adminList;
    }

    public function getParentInfo(?string $langTag = null): ?array
    {
        $parentGroup = $this->parentGroup;

        if (! $parentGroup) {
            return null;
        }

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'website_group_detail_path',
            'site_url',
        ]);

        $siteUrl = $configKeys['site_url'] ?? config('app.url');

        $info['gid'] = $parentGroup->gid;
        $info['url'] = $siteUrl.'/'.$configKeys['website_group_detail_path'].'/'.$parentGroup->gid;
        $info['name'] = StrHelper::languageContent($parentGroup->name, $langTag);
        $info['description'] = StrHelper::languageContent($parentGroup->description, $langTag);
        $info['cover'] = FileHelper::fresnsFileUrlByTableColumn($parentGroup->cover_file_id, $parentGroup->cover_file_url);
        $info['banner'] = FileHelper::fresnsFileUrlByTableColumn($parentGroup->banner_file_id, $parentGroup->banner_file_url);

        return $info;
    }

    public function getCoverUrl(): ?string
    {
        return FileHelper::fresnsFileUrlByTableColumn($this->cover_file_id, $this->cover_file_url);
    }

    public function getBannerUrl(): ?string
    {
        return FileHelper::fresnsFileUrlByTableColumn($this->banner_file_id, $this->banner_file_url);
    }
}
