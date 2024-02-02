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

    public function getPostDraftInfo(?string $langTag = null, ?string $timezone = null): array
    {
        $postLogData = $this;
        $permissions = $postLogData->permissions;

        $quotedPost = $postLogData->quotedPost;
        $group = $postLogData->group;
        $geotag = $postLogData->geotag;

        $info['did'] = $postLogData->hpid;
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
        $info['isPrivate'] = false;

        $info['locationInfo'] = $postLogData->location_info;
        $info['moreInfo'] = $postLogData->more_info;
        $info['permissions'] = $permissions;

        $info['archives'] = ExtendUtility::getArchives(ArchiveUsage::TYPE_POST_LOG, $postLogData->id, $langTag);
        $info['extends'] = ExtendUtility::getExtends(ExtendUsage::TYPE_POST_LOG, $postLogData->id, $langTag);

        $info['group'] = $group ? DetailUtility::groupDetail($group, $langTag, $timezone) : null;
        $info['geotag'] = $geotag ? DetailUtility::geotagDetail($geotag, $langTag, $timezone) : null;

        $info['createdDatetime'] = $postLogData->created_at;
        $info['createdTimeAgo'] = DateHelper::fresnsHumanReadableTime($postLogData->created_at, $langTag);
        $info['state'] = $postLogData->state;
        $info['reason'] = $postLogData->reason;

        return $info;
    }
}
