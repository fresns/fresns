<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Helpers;

use App\Http\FresnsDb\FresnsConfigs\FresnsConfigs;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguages;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsagesService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ApiLanguageHelper
{
    // table_id
    public static function getLanguagesByTableId($table, $table_field, $table_id)
    {
        if (! $table_id) {
            return '';
        }
        $langTag = ApiLanguageHelper::getLangTagByHeader();
        // Leave blank to output the content of the default language, the query does not default language to output the first
        $input = [
            'table_name' => $table,
            'table_field' => $table_field,
            'table_id' => $table_id,
            'lang_tag' => $langTag,
        ];
        $langContent = FresnsLanguages::where($input)->first();
        if (! $langContent) {
            $input = [
                'table_name' => $table,
                'table_field' => $table_field,
                'table_id' => $table_id,
            ];
            $langContent = FresnsLanguages::where($input)->first();
        }
        $content = $langContent['lang_content'] ?? '';

        return $content;
    }

    // table_key
    public static function getLanguagesByTableKey($table, $table_field, $table_key)
    {
        if (! $table_key) {
            return '';
        }
        $langTag = ApiLanguageHelper::getLangTagByHeader();
        // Leave blank to output the content of the default language, the query does not default language to output the first
        $input = [
            'table_name' => $table,
            // 'table_field' => 'item_key',
            'table_key' => $table_key,
            'lang_tag' => $langTag,
        ];
        $langContent = FresnsLanguages::where($input)->first();
        if (! $langContent) {
            $input = [
                'table_name' => $table,
                // 'table_field' => 'item_key',
                'table_key' => $table_key,
            ];
            $langContent = FresnsLanguages::where($input)->first();
        }
        $content = $langContent['lang_content'] ?? '';

        return $content;
    }

    // Get default language
    public static function getDefaultLanguage()
    {
        $defaultLanguage = ApiConfigHelper::getConfigByItemKey(FresnsConfigsConfig::DEFAULT_LANGUAGE);
        if (empty($defaultLanguage)) {
            $defaultLanguage = FresnsConfigs::where('item_key', FresnsConfigsConfig::DEFAULT_LANGUAGE)->value('item_value');
        }

        return $defaultLanguage;
    }

    // Get langTag
    public static function getLangTagByHeader()
    {
        $langTagHeader = request()->header('langTag');
        $langTag = null;
        if (! empty($langTagHeader)) {
            // If it is not empty, check if the language exists
            $langSetting = FresnsConfigs::where('item_key', FresnsConfigsConfig::LANG_SETTINGS)->value('item_value');
            if (! empty($langSetting)) {
                $langSettingArr = json_decode($langSetting, true);
                foreach ($langSettingArr as $v) {
                    if ($v['langTag'] == $langTagHeader) {
                        $langTag = $langTagHeader;
                    }
                }
            }
        }

        // If no multiple languages are passed or not queried, the default language is queried
        if (empty($langTag)) {
            $langTag = ApiLanguageHelper::getDefaultLanguage();
        }

        return $langTag;
    }

    // Use default language
    public static function getDefaultLanguageByApi()
    {
        $defaultLanguage = FresnsConfigs::where('item_key', FresnsConfigsConfig::DEFAULT_LANGUAGE)->where('is_restful', 1)->value('item_value');

        return $defaultLanguage;
    }

    // Filter by key for the corresponding language tag
    public static function getDefaultLanguageByKey($key)
    {
        $langSettings = FresnsConfigs::where('item_key', FresnsConfigsConfig::LANG_SETTINGS)->value('item_value');
        $langSettingsArr = json_decode($langSettings, true);
        $default = null;
        foreach ($langSettingsArr as $v) {
            if ($v['langTag'] == $key) {
                $default = $v['langTag'];
            }
        }

        return $default;
    }

    // Look up the corresponding key by language tag
    public static function getDefaultLanguageByLangTag($langTag)
    {
        $langSettings = FresnsConfigs::where('item_key', FresnsConfigsConfig::LANG_SETTINGS)->value('item_value');
        $langSettingsArr = json_decode($langSettings, true);
        $default = null;
        foreach ($langSettingsArr as $v) {
            if ($v['langTag'] == $langTag) {
                $default = $v['langTag'];
            }
        }

        return $default;
    }

    // Get all languages
    public static function getAllLanguages($table, $table_field, $table_id)
    {
        if (! $table_id) {
            return '';
        }

        $input = [
            'table_name' => $table,
            'table_field' => $table_field,
            'table_id' => $table_id,
        ];
        $info = FresnsLanguages::where($input)->get();

        return $info;
    }

    public static function getLangTag()
    {
        $isControlApi = request()->input('is_control_api');
        if ($isControlApi == 1) {
            $userId = Auth::id();
            $langTag = request()->input('lang', Cache::get('lang_tag_'.$userId));
        } else {
            $langTag = ApiLanguageHelper::getLangTagByHeader();
        }

        return $langTag;
    }
}
