<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\InteractionHelper;
use App\Helpers\PluginHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\ArchiveUsage;
use App\Models\ExtendUsage;
use App\Models\User;
use App\Utilities\DetailUtility;
use App\Utilities\ExtendUtility;
use Illuminate\Support\Str;

trait PostLogServiceTrait
{
    public function getPostHistoryInfo(): array
    {
        $postLogData = $this;
        $permissions = $postLogData->permissions;

        $post = $this->post;

        $info['hpid'] = $postLogData->hpid;
        $info['pid'] = $post->pid;

        $info['title'] = $postLogData->title;
        $info['content'] = $postLogData->content;
        $info['contentLength'] = Str::length($postLogData->content);
        $info['langTag'] = $postLogData->lang_tag;
        $info['writingDirection'] = $permissions['contentWritingDirection'] ?? 'ltr'; // ltr or rtl
        $info['isBrief'] = false;
        $info['isMarkdown'] = (bool) $postLogData->is_markdown;
        $info['isAnonymous'] = (bool) $post->is_anonymous;

        $info['createdDatetime'] = $postLogData->created_at;
        $info['createdTimeAgo'] = null;
        $info['status'] = (bool) $postLogData->is_enabled;

        $info['locationInfo'] = $postLogData->location_info;
        $info['moreInfo'] = $postLogData->more_info;

        return $info;
    }

    public function getDraftInfo(?string $langTag = null, ?string $timezone = null, ?array $groupOptions = [], ?array $geotagOptions = []): array
    {
        $postLogData = $this;
        $permissions = $postLogData->permissions;

        $quotedPost = $postLogData->quotedPost;
        $group = $postLogData->group;
        $geotag = $postLogData->geotag;

        $post = $this->post;

        // permissions
        $readConfig = $permissions['readConfig'] ?? [];
        $associatedUserListConfig = $permissions['associatedUserListConfig'] ?? [];
        $commentConfig = $permissions['commentConfig'] ?? [];

        $whitelistUsers = $readConfig['whitelist']['users'] ?? [];
        $whitelistRoles = $readConfig['whitelist']['roles'] ?? [];

        $configKeys = ConfigHelper::fresnsConfigByItemKeys([
            'user_identifier',
            'website_user_detail_path',
        ]);
        $siteUrl = ConfigHelper::fresnsSiteUrl();

        $userList = [];
        foreach ($whitelistUsers as $userId) {
            $user = PrimaryHelper::fresnsModelById('user', $userId);

            $fsid = $configKeys['user_identifier'] == 'uid' ? $user->uid : $user->username;

            $userItem['fsid'] = $fsid;
            $userItem['uid'] = $user->uid;
            $userItem['url'] = $siteUrl.'/'.$configKeys['website_user_detail_path'].'/'.$fsid;
            $userItem['username'] = $user->username;
            $userItem['nickname'] = $user->nickname;
            $userItem['avatar'] = $user->getUserAvatar();

            $userList[] = $userItem;
        }

        $roleList = [];
        foreach ($whitelistRoles as $roleId) {
            $role = InteractionHelper::fresnsRoleInfo($roleId, $langTag);

            $roleItem['rid'] = $role['rid'];
            $roleItem['name'] = $role['name'];
            $roleItem['icon'] = $role['icon'];

            $roleList[] = $roleItem;
        }

        $permissions['readConfig'] = [
            'isReadLocked' => $readConfig['isReadLocked'] ?? false,
            'previewPercentage' => $readConfig['previewPercentage'] ?? 0,
            'whitelist' => [
                'users' => $userList,
                'roles' => $roleList,
            ],
            'buttonName' => StrHelper::languageContent($readConfig['buttonName'] ?? null, $langTag),
            'buttonUrl' => PluginHelper::fresnsPluginUrlByFskey($readConfig['appFskey'] ?? null),
        ];

        $permissions['associatedUserListConfig'] = [
            'hasUserList' => $associatedUserListConfig['hasUserList'] ?? false,
            'userListName' => StrHelper::languageContent($associatedUserListConfig['userListName'] ?? null, $langTag),
            'userListUrl' => PluginHelper::fresnsPluginUrlByFskey($associatedUserListConfig['appFskey'] ?? null),
        ];

        $permissions['commentConfig'] = [
            'visible' => $commentConfig['visible'] ?? true,
            'policy' => $commentConfig['policy'] ?? User::POLICY_EVERYONE,
            'privacy' => $commentConfig['privacy'] ?? 'public',
            'action' => [
                'hasActionButton' => $commentConfig['action']['hasActionButton'] ?? false,
                'buttonName' => StrHelper::languageContent($commentConfig['action']['buttonName'] ?? null, $langTag),
                'buttonStyle' => $commentConfig['action']['buttonStyle'] ?? null,
                'buttonUrl' => PluginHelper::fresnsPluginUrlByFskey($commentConfig['action']['appFskey'] ?? null),
            ],
        ];

        $privacy = $commentConfig['privacy'] ?? 'public';
        // end permissions

        $info['did'] = $postLogData->hpid;
        $info['fsid'] = $post?->pid; // published content pid
        $info['quotedPid'] = $quotedPost?->pid;
        $info['replyToPid'] = null;
        $info['replyToCid'] = null;

        $info['title'] = $postLogData->title;
        $info['content'] = $postLogData->content;
        $info['contentLength'] = Str::length($postLogData->content);
        $info['langTag'] = $postLogData->lang_tag;
        $info['writingDirection'] = $permissions['contentWritingDirection'] ?? 'ltr'; // ltr or rtl
        $info['isMarkdown'] = (bool) $postLogData->is_markdown;
        $info['isAnonymous'] = (bool) $postLogData->is_anonymous;
        $info['isPrivate'] = ($privacy == 'private');

        $info['locationInfo'] = $postLogData->location_info;
        $info['moreInfo'] = $postLogData->more_info;
        $info['permissions'] = $permissions;

        $info['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST_LOG, $postLogData->id, $langTag);
        $info['files'] = FileHelper::fresnsFileInfoListByTableColumn('post_logs', 'id', $postLogData->id);
        $info['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_POST_LOG, $postLogData->id, $langTag);

        $info['group'] = $group ? DetailUtility::groupDetail($group, $langTag, $timezone, null, $groupOptions) : null;
        $info['geotag'] = $geotag ? DetailUtility::geotagDetail($geotag, $langTag, $timezone, null, $geotagOptions) : null;

        $info['createdDatetime'] = DateHelper::fresnsFormatDateTime($postLogData->created_at, $timezone, $langTag);
        $info['createdTimeAgo'] = DateHelper::fresnsHumanReadableTime($postLogData->created_at, $langTag);
        $info['state'] = $postLogData->state;
        $info['reason'] = $postLogData->reason;

        return $info;
    }
}
