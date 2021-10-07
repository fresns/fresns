<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Editor;

use App\Base\Resources\BaseAdminResource;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;
use App\Http\FresnsDb\FresnsPostLogs\FresnsPostLogsConfig;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;

/**
 * List resource config handle.
 */
class FresnsPostLogsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FresnsPostLogsConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }

        // Post Info
        $postInfo = FresnsPosts::find($this->post_id);

        // Default Field
        $default = [
            'id' => $this->id,
            'pid' => $postInfo['uuid'] ?? null,
            'isPluginEditor' => $this->is_plugin_editor,
            'editorUrl' => FresnsPluginsService::getPluginUrlByUnikey($this->editor_unikey),
            'types' => $this->types,
            'title' => $this->title,
            'content' => mb_substr($this->content, 0, 140),
            'state' => $this->state,
            'reason' => $this->reason,
            'submitTime' => $this->submit_at,
            'time' => $this->created_at,
        ];

        return $default;
    }
}
