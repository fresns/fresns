<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Helpers;

use App\Fresns\Api\Center\Common\LogService;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsService;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguagesService;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPlugins;
use App\Fresns\Api\Helpers\StrHelper;
use App\Fresns\Api\Http\Base\FsApiConfig;

class ApiConfigHelper
{
    // Get config info list
    public static function getConfigsList()
    {
        $map = config(FsApiConfig::CONFIGS_LIST);

        return $map;
    }

    // Get config info list (api)
    public static function getConfigsListApi()
    {
        $map = config(FsApiConfig::CONFIGS_LIST_API);

        $itemArr = [];
        if (! empty($map)) {
            foreach ($map as $v) {
                if ($v['is_restful'] == 0) {
                    continue;
                }
                $item = [];
                $item['itemKey'] = $v['item_key'];
                $itemValue = $v['item_value'];
                $isJson = StrHelper::isJson($itemValue);
                if ($isJson == true) {
                    $itemValue = json_decode($itemValue, true);
                }
                $item['itemValue'] = $itemValue;
                $item['itemTag'] = $v['item_tag'];
                $item['itemType'] = $v['item_type'];
                $item['itemStatus'] = boolval($v['is_enable']);
                $item['isMultilingual'] = $v['is_multilingual'];
                $itemArr[] = $item;
            }
        }

        return $itemArr;
    }

    // Config info list data
    public static function getConfigsListsApi()
    {
        $map = self::getConfigsListApi();
        $itemArr = [];
        if ($map) {
            foreach ($map as $k => $v) {
                $itemArr[] = self::joinData($v);
            }
        }

        return $itemArr;
    }

    // Get by key tag
    public static function getConfigByItemTag($key)
    {
        $map = config(FsApiConfig::CONFIGS_LIST);
        $itemArr = [];
        if ($map) {
            foreach ($map as $k => $v) {
                if ($k == $key) {
                    $itemArr = $v;
                }
            }
        }

        return $itemArr;
    }

    // Get by key tag (api)
    public static function getConfigByItemTagApi($key)
    {
        $map = self::getConfigsListApi();
        $itemArr = [];
        if ($map) {
            foreach ($map as $k => $v) {
                if ($v['itemTag'] == $key) {
                    $itemArr[] = self::joinData($v);
                }
            }
        }

        return $itemArr;
    }

    // Get by key name
    public static function getConfigByItemKey($itemKey)
    {
        $map = config(FsApiConfig::CONFIGS_LIST);
        $data = null;
        if ($map) {
            foreach ($map as $k => $v) {
                if (isset($v[$itemKey])) {
                    $data = $v[$itemKey];
                    break;
                }
            }
        }

        return $data;
    }

    // Get by key name (api)
    public static function getConfigByItemKeyApi($itemKey)
    {
        $map = self::getConfigsListApi();
        $data = [];
        if ($map) {
            foreach ($map as $k => $v) {
                if ($v['itemKey'] == $itemKey) {
                    $data[] = self::joinData($v);
                }
            }
        }

        return $data;
    }

    // Assembling api return data
    public static function joinData($data)
    {
        $langTag = ApiLanguageHelper::getLangTagByHeader();

        $item['itemKey'] = $data['itemKey'];
        $item['itemValue'] = $data['itemValue'];

        // When is_multilingual=1 means that the key is multilingual
        if ($data['isMultilingual'] == 1) {
            $item['itemValue'] = FresnsLanguagesService::getLanguageByTableKey(FresnsConfigsConfig::CFG_TABLE, 'item_value', $item['itemKey'], $langTag);
        }
        if ($data['itemType'] == 'number') {
            if (is_numeric($item['itemValue'])) {
                $item['itemValue'] = intval($item['itemValue']);
            }
        }

        // When item_type=file if the key value starts with http:// or https://, it is output as is without special handling.
        // If it is a number, it is the file ID, with which the file URL is output, or if the file type has anti-blocking enabled, the URL output is requested from the plugin.
        if ($data['itemType'] == 'file') {
            if (is_numeric($item['itemValue'])) {
                $item['itemValue'] = ApiFileHelper::getImageSignUrlByFileId($item['itemValue']);
            }
        }

        // When item_type=plugin means it is a plugin unikey value, the plugin URL is output with unikey.
        if ($data['itemType'] == 'plugin') {
            $plugin = FresnsPlugins::where('unikey', $item['itemValue'])->first();
            if ($plugin) {
                if (! empty($plugin['plugin_domain'])) {
                    $domain = $plugin['plugin_domain'];
                } else {
                    $domain = ApiConfigHelper::getConfigByItemKey('backend_domain');
                }
                $value['unikey'] = $plugin['unikey'];
                $value['url'] = $domain.$plugin['access_path'];
                $item['itemValue'] = $value;
            }
        }

        // When item_type=plugins means it is a multi-select plugin, separated by English commas.
        if ($data['itemType'] == 'plugins') {
            $unikeyArr = explode(',', $item['itemValue']);
            $pluginArr = FresnsPlugins::whereIn('unikey', $unikeyArr)->get([
                'unikey',
                'plugin_domain',
                'access_path',
            ])->toArray();
            if ($pluginArr) {
                $domain = ApiConfigHelper::getConfigByItemKey('backend_domain');
                $itArr = [];
                foreach ($pluginArr as $v) {
                    $it = [];
                    $it['unikey'] = $v['unikey'];
                    if (! empty($v['plugin_domain'])) {
                        $domain = $v['plugin_domain'];
                    }
                    $it['url'] = $domain.$v['access_path'] ?? '';
                    $itArr[] = $it;
                }
                $item['itemValue'] = $itArr;
            }
        }
        $item['itemTag'] = $data['itemTag'];
        $item['itemType'] = $data['itemType'];
        $item['itemStatus'] = $data['itemStatus'];

        return $item;
    }

    // Get all language parameters
    public static function getConfigsLanguageList()
    {
        $map = config(FsApiConfig::CONFIGS_LIST);
        $data = [];
        foreach ($map as $k => $v) {
            if ($k == FresnsConfigsConfig::LANGUAGES) {
                $data = $v;
            }
        }

        return $data;
    }

    // Get distance units
    public static function distanceUnits($langTag)
    {
        $language = self::getConfigsLanguageList();
        $languageArr = FresnsConfigsService::getLanguageStatus();
        LogService::Info('language', $language);
        $distanceUnits = '';
        // Get the distance units for the default language
        $language_menus = json_decode($language['language_menus'], true);
        foreach ($language_menus as $f) {
            if ($f['langTag'] == $languageArr['default_language']) {
                $distanceUnits = $f['lengthUnits'];
            }
        }
        foreach ($language_menus as $v) {
            if ($v['langTag'] == $langTag) {
                if (! empty($v['lengthUnits'])) {
                    $distanceUnits = $v['lengthUnits'];
                }
            }
        }

        return $distanceUnits;
    }
}
