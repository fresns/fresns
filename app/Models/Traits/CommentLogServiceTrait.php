<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\DateHelper;
use App\Models\ArchiveUsage;
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

        $info['privacy'] = 'public';
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

    public function getCommentDraftInfo(?string $langTag = null, ?string $timezone = null): array
    {
        $commentLogData = $this;
        $permissions = $commentLogData->permissions;

        $post = $commentLogData->post;
        $parentComment = $commentLogData->parentComment;

        $group = $commentLogData?->group;
        $geotag = $commentLogData?->geotag;

        $info['did'] = $commentLogData->hpid;
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

        $info['locationInfo'] = $commentLogData->location_info;
        $info['moreInfo'] = $commentLogData->more_info;
        $info['permissions'] = $permissions;

        $info['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST_LOG, $commentLogData->id, $langTag);
        $info['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_POST_LOG, $commentLogData->id, $langTag);

        $info['group'] = $group ? DetailUtility::groupDetail($group, $langTag, $timezone) : null;
        $info['geotag'] = $geotag ? DetailUtility::geotagDetail($geotag, $langTag, $timezone) : null;

        $info['createdDatetime'] = $commentLogData->created_at;
        $info['createdTimeAgo'] = DateHelper::fresnsHumanReadableTime($commentLogData->created_at, $langTag);;
        $info['state'] = $commentLogData->state;
        $info['reason'] = $commentLogData->reason;

        return $info;
    }
}
