<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Info;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\Helpers\ApiFileHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtends;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtendsConfig;
use App\Fresns\Api\FsDb\FresnsPluginCallbacks\FresnsPluginCallbacks;
use App\Fresns\Api\FsDb\FresnsPluginCallbacks\FresnsPluginCallbacksConfig;

/**
 * List resource config handle.
 */
class FresnsPluginCallbacksResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // Form Field
        $formMap = FresnsPluginCallbacksConfig::FORM_FIELDS_MAP;
        $formMapFieldsArr = [];
        foreach ($formMap as $k => $dbField) {
            $formMapFieldsArr[$dbField] = $this->$dbField;
        }
        // Insert unikey
        $unikey = $request->input('unikey');
        FresnsPluginCallbacks::where('id', $this->id)->update(['use_plugin_unikey' => $unikey, 'status' => 2]);
        $content = json_decode($this->content, true);
        if ($content) {
            foreach ($content as &$t) {
                // callbackType=4
                // Handle file anti hotlinking URL
                if ($t['callbackType'] == 4) {
                    $files = $t['dataValue'];
                    if ($files) {
                        $arr = ApiFileHelper::getMoreJsonSignUrl($files);
                    }
                    $t['dataValue'] = $arr;
                }
            }
            // callbackType=9
            // Get all extended content data
            if ($t['callbackType'] == 9) {
                $extendsArr = $t['dataValue'];
                $extends = [];
                if ($extendsArr) {
                    foreach ($extendsArr as $e) {
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
                                $arr['files'] = json_decode($extendsInfo['text_files'], true);
                                if ($arr['files']) {
                                    $arr['files'] = ApiFileHelper::getMoreJsonSignUrl($arr['files']);
                                }
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
                            $arr['moreJson'] = ApiFileHelper::getMoreJsonSignUrl($extendsInfo['moreJson']) ?? [];
                            $extends[] = $arr;
                        }
                    }
                    $t['dataValue'] = $extends;
                }
            }
        }

        return $content;
    }
}
