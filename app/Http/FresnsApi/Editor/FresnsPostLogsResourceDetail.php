<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Editor;

use App\Base\Resources\BaseAdminResource;
use App\Http\FresnsApi\Helpers\ApiFileHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsDb\FresnsExtends\FresnsExtends;
use App\Http\FresnsDb\FresnsExtends\FresnsExtendsConfig;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsPlugins\FresnsPluginsService;
use App\Http\FresnsDb\FresnsPostLogs\FresnsPostLogsConfig;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;

/**
 * Detail resource config handle.
 */
class FresnsPostLogsResourceDetail extends BaseAdminResource
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
        // Group Info
        $groupInfo = FresnsGroups::find($this->group_id);

        // Extend Info
        $extends_json = json_decode($this->extends_json, true);
        $extends = [];
        if ($extends_json) {
            foreach ($extends_json as $e) {
                $arr = [];
                $extendsInfo = FresnsExtends::where('uuid', $e['eid'])->first();
                if ($extendsInfo) {
                    $arr['eid'] = $e['eid'];
                    $arr['canDelete'] = $e['canDelete'] ?? 'true';
                    $arr['rankNum'] = $e['rankNum'] ?? 9;
                    $arr['plugin'] = $extendsInfo['plugin_unikey'] ?? '';
                    $arr['frame'] = $extendsInfo['frame'] ?? '';
                    $arr['position'] = $extendsInfo['position'] ?? '';
                    $arr['content'] = $extendsInfo['text_content'] ?? '';
                    if ($extendsInfo['frame'] == 1) {
                        $arr['files'] = json_decode($extendsInfo['text_files'], true);
                        if ($arr['files']) {
                            $arr['files'] = ApiFileHelper::getMoreJsonSignUrl($arr['files']);
                        }
                    }
                    $arr['cover'] = ApiFileHelper::getImageSignUrlByFileIdUrl($extendsInfo['cover_file_id'], $extendsInfo['cover_file_url']);
                    $arr['title'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'title', $extendsInfo['id']);
                    $arr['titleColor'] = $extendsInfo['title_color'] ?? '';
                    $arr['descPrimary'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'desc_primary', $extendsInfo['id']);
                    $arr['descPrimaryColor'] = $extendsInfo['desc_primary_color'] ?? '';
                    $arr['descSecondary'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'desc_secondary', $extendsInfo['id']);
                    $arr['descSecondaryColor'] = $extendsInfo['desc_secondary_color'] ?? '';
                    $arr['btnName'] = ApiLanguageHelper::getLanguagesByTableId(FresnsExtendsConfig::CFG_TABLE, 'btn_name', $extendsInfo['id']);
                    $arr['btnColor'] = $extendsInfo['btn_color'] ?? '';
                    $arr['type'] = $extendsInfo['extend_type'] ?? '';
                    $arr['target'] = $extendsInfo['extend_target'] ?? '';
                    $arr['value'] = $extendsInfo['extend_value'] ?? '';
                    $arr['support'] = $extendsInfo['extend_support'] ?? '';
                    $arr['moreJson'] = ApiFileHelper::getMoreJsonSignUrl($extendsInfo['moreJson']) ?? [];
                    $extends[] = $arr;
                }
            }
        }

        // File Info
        $files_decode = json_decode($this->files_json, true);
        $files = [];
        if ($files_decode) {
            $files = ApiFileHelper::getMoreJsonSignUrl($files_decode);
        }

        // Default Field
        $default = [
            'id' => $this->id,
            'pid' => $postInfo['uuid'] ?? null,
            'isPluginEditor' => $this->is_plugin_editor,
            'editorUrl' => FresnsPluginsService::getPluginUrlByUnikey($this->editor_unikey),
            'gid' => $groupInfo['uuid'] ?? null,
            'types' => $this->types,
            'title' => $this->title,
            'content' => $this->content,
            'isMarkdown' => $this->is_markdown,
            'isAnonymous' => $this->is_anonymous,
            'memberList' => json_decode($this->member_list_json, true) ?? null,
            'commentSetting' => json_decode($this->comment_set_json, true) ?? null,
            'allow' => json_decode($this->allow_json, true) ?? null,
            'location' => json_decode($this->location_json, true) ?? null,
            'files' => $files,
            'extends' => $extends,
            'state' => $this->state,
        ];

        return $default;
    }
}
