<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
use App\Helpers\PluginHelper;
use App\Helpers\StrHelper;
use App\Models\ArchiveUsage;
use App\Models\Comment;
use App\Models\ExtendUsage;
use App\Utilities\DetailUtility;
use App\Utilities\ExtendUtility;
use Illuminate\Support\Str;

trait CommentLogServiceTrait
{
    public function getCommentHistoryInfo(): array
    {
        $commentLogData = $this;
        $permissions = $commentLogData->permissions;

        $comment = $this->comment;

        $info['hcid'] = $commentLogData->hcid;
        $info['cid'] = $comment->cid;

        $info['privacy'] = ($commentLogData->privacy_state == Comment::PRIVACY_PUBLIC) ? 'public' : 'private';
        $info['content'] = $commentLogData->content;
        $info['contentLength'] = Str::length($commentLogData->content);
        $info['langTag'] = $commentLogData->lang_tag;
        $info['writingDirection'] = $permissions['contentWritingDirection'] ?? 'ltr'; // ltr or rtl
        $info['isBrief'] = false;
        $info['isMarkdown'] = (bool) $commentLogData->is_markdown;
        $info['isAnonymous'] = (bool) $comment->is_anonymous;

        $info['createdDatetime'] = $commentLogData->created_at;
        $info['createdTimeAgo'] = null;
        $info['status'] = (bool) $commentLogData->is_enabled;

        $info['locationInfo'] = $commentLogData->location_info;
        $info['moreInfo'] = $commentLogData->more_info;

        return $info;
    }

    public function getDraftInfo(?string $langTag = null, ?string $timezone = null, ?array $groupOptions = [], ?array $geotagOptions = []): array
    {
        $commentLogData = $this;
        $permissions = $commentLogData->permissions;

        $parentComment = $commentLogData->parentComment;
        $post = $commentLogData->post;
        $postPermissions = $post?->permissions;
        $privacy = $postPermissions['commentConfig']['privacy'] ?? 'public';

        // permissions
        $activeButtonConfig = $permissions['activeButton'] ?? [];

        $permissions['activeButton'] = [
            'hasActiveButton' => $activeButtonConfig['hasActiveButton'] ?? false,
            'buttonName' => StrHelper::languageContent($activeButtonConfig['buttonName'] ?? null, $langTag),
            'buttonStyle' => $activeButtonConfig['buttonStyle'] ?? null,
            'appUrl' => PluginHelper::fresnsPluginUrlByFskey($activeButtonConfig['appFskey'] ?? null),
        ];
        // end permissions

        $geotag = $commentLogData->geotag;

        $comment = $this->comment;

        $info['did'] = $commentLogData->hcid;
        $info['fsid'] = $comment?->cid; // published content cid
        $info['quotedPid'] = null;
        $info['replyToPid'] = $post?->pid;
        $info['replyToCid'] = $parentComment?->cid;

        $info['title'] = null;
        $info['content'] = $commentLogData->content;
        $info['contentLength'] = Str::length($commentLogData->content);
        $info['langTag'] = $commentLogData->lang_tag;
        $info['writingDirection'] = $permissions['contentWritingDirection'] ?? 'ltr'; // ltr or rtl
        $info['isMarkdown'] = (bool) $commentLogData->is_markdown;
        $info['isAnonymous'] = (bool) $commentLogData->is_anonymous;
        $info['isPrivate'] = ($privacy == 'private') ? true : $commentLogData->is_private;

        $info['locationInfo'] = $commentLogData->location_info;
        $info['moreInfo'] = $commentLogData->more_info;
        $info['permissions'] = $permissions;

        $info['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_COMMENT_LOG, $commentLogData->id, $langTag);
        $info['files'] = FileHelper::fresnsFileInfoListByTableColumn('comment_logs', 'id', $commentLogData->id);
        $info['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_COMMENT_LOG, $commentLogData->id, $langTag);

        $info['group'] = null;
        $info['geotag'] = $geotag ? DetailUtility::geotagDetail($geotag, $langTag, $timezone, null, $geotagOptions) : null;

        $info['createdDatetime'] = DateHelper::fresnsFormatDateTime($commentLogData->created_at, $timezone, $langTag);
        $info['createdTimeAgo'] = DateHelper::fresnsHumanReadableTime($commentLogData->created_at, $langTag);
        $info['state'] = $commentLogData->state;
        $info['reason'] = $commentLogData->reason;

        return $info;
    }
}
