<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Editor;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use App\Fresns\Api\FsDb\FresnsCommentLogs\FresnsCommentLogsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsComments;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtends;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtendsConfig;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPluginsService;

/**
 * Detail resource config handle.
 */
class FresnsCommentLogsResourceDetail extends BaseAdminResource
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

        // Extend Info
        $extends_json = json_decode($this->extends_json, true);
        $extends = [];
        if ($extends_json) {
            foreach ($extends_json as $e) {
                $arr = [];
                $extendsInfo = FresnsExtends::where('eid', $e['eid'])->first();
                if ($extendsInfo) {
                    $arr['eid'] = $e['eid'];
                    $arr['canDelete'] = $e['canDelete'] ?? 'true';
                    $arr['rankNum'] = $e['rankNum'] ?? 9;
                    $arr['plugin'] = $extendsInfo['plugin_unikey'] ?? null;
                    $arr['frame'] = $extendsInfo['frame'] ?? null;
                    $arr['position'] = $extendsInfo['position'] ?? null;
                    $arr['content'] = $extendsInfo['text_content'] ?? null;
                    if ($extendsInfo['frame'] == 1) {
                        $arr['files'] = $extendsInfo['text_files'];
                    }
                    $arr['cover'] = ApiFileHelper::getImageSignUrlByFileIdUrl($extendsInfo['cover_file_id'], $extendsInfo['cover_file_url']);
                    $arr['title'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'title', $extendsInfo['id']);
                    $arr['titleColor'] = $extendsInfo['title_color'] ?? null;
                    $arr['descPrimary'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'desc_primary', $extendsInfo['id']);
                    $arr['descPrimaryColor'] = $extendsInfo['desc_primary_color'] ?? null;
                    $arr['descSecondary'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'desc_secondary', $extendsInfo['id']);
                    $arr['descSecondaryColor'] = $extendsInfo['desc_secondary_color'] ?? null;
                    $arr['btnName'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'btn_name', $extendsInfo['id']);
                    $arr['btnColor'] = $extendsInfo['btn_color'] ?? null;
                    $arr['type'] = $extendsInfo['extend_type'] ?? null;
                    $arr['target'] = $extendsInfo['extend_target'] ?? null;
                    $arr['value'] = $extendsInfo['extend_value'] ?? null;
                    $arr['support'] = $extendsInfo['extend_support'] ?? null;
                    $arr['moreJson'] = ApiFileHelper::getMoreJsonSignUrl($extendsInfo['moreJson']) ?? null;
                    $extends[] = $arr;
                }
            }
        }

        // Default Field
        $default = [
            'id' => $this->id,
            'cid' => $commentInfo['cid'] ?? null,
            'isPluginEditor' => $this->is_plugin_editor,
            'editorUrl' => FresnsPluginsService::getPluginUrlByUnikey($this->editor_unikey),
            'types' => $this->types,
            'content' => $this->content,
            'isMarkdown' => $this->is_markdown,
            'isAnonymous' => $this->is_anonymous,
            'location' => json_decode($this->location_json, true) ?? null,
            'files' => json_decode($this->files_json, true) ?? [],
            'extends' => $extends,
            'state' => $this->state,
        ];

        return $default;
    }
}
