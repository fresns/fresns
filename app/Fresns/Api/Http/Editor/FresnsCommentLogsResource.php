<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Editor;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\FsDb\FresnsCommentLogs\FresnsCommentLogsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsComments;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPluginsService;

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
            'cid' => $commentInfo['cid'] ?? null,
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
