<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Editor;

use App\Base\Resources\BaseAdminResource;
use App\Http\FresnsDb\FresnsCommentLogs\FresnsCommentLogsConfig;
use App\Http\FresnsDb\FresnsComments\FresnsComments;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;
use App\Http\FresnsDb\FresnsPostLogs\FresnsPostLogsConfig;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;

/**
 * List resource config handle.
 */
class FresnsCommentLogsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FresnsCommentLogsConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }

        // Comment Info
        $commentInfo = FresnsComments::find($this->comment_id);

        // Default Field
        $default = [
            'id' => $this->id,
            'cid' => $commentInfo['uuid'] ?? null,
            'isPluginEditor' => $this->is_plugin_editor,
            'editorUrl' => FresnsPluginsService::getPluginUrlByUnikey($this->editor_unikey),
            'content' => mb_substr($this->content, 0, 140),
            'state' => $this->state,
            'reason' => $this->reason,
            'submitTime' => $this->submit_at,
            'time' => $this->created_at,
        ];

        return $default;
    }
}
