<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Center\Common;

use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccounts;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigs;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogsService;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;

class GlobalService
{
    // Loading data
    public static function loadData()
    {
        self::initSessionLog();
        self::loadGlobal();
        self::loadGlobalData();
    }

    // Initial Configuration
    public static function loadGlobal()
    {
        $fresns = [];

        $aid = request()->header('aid');
        $uid = request()->header('uid');

        $arr = ['platform', 'version', 'appId', 'aid', 'uid'];
        foreach ($arr as $field) {
            $fresns[$field] = request()->header($field);
        }

        // account and user data
        $fresns['account'] = null;
        $fresns['user'] = null;
        if (! empty($aid)) {
            $account = FresnsAccounts::staticFindByField('aid', $aid);
            $fresns['account'] = $account ?? null;
            $fresns['account_id'] = $account->id ?? null;
        }

        if (! empty($uid)) {
            $user = FresnsUsers::staticFindByField('uid', $uid);
            $fresns['user'] = $user ?? null;
            $fresns['user_id'] = $user->id ?? null;
        }

        $langTag = ApiLanguageHelper::getLangTagByHeader();
        $fresns['langTag'] = $langTag;
        $GLOBALS['fresns'] = $fresns;
    }

    // Get the value based on key
    public static function getGlobalKey($globalKey)
    {
        return $GLOBALS['fresns'][$globalKey] ?? null;
    }

    // Initialization Log
    public static function initSessionLog()
    {
        $sessionLogInfo = [];
        $deviceInfo = request()->header('deviceInfo');
        $aid = GlobalService::getGlobalKey('account_id');
        $uid = GlobalService::getGlobalKey('user_id');
        $uri = Request::getRequestUri();
        if ($deviceInfo) {
            $addDeviceInfoUrlArr = GlobalConfig::ADD_DEVICE_INFO_URI_ARR;
            if (! in_array($uri, $addDeviceInfoUrlArr)) {
                return true;
            }
            $map = GlobalConfig::URI_CONVERSION_OBJECT_TYPE_NO;

            $objectType = '';
            foreach ($map as $k => $v) {
                if (in_array($uri, $v)) {
                    $objectType = $k;
                }
            }

            if (! empty($objectType)) {
                $objectName = $uri;
                $objectNameMap = GlobalConfig::URI_CONVERSION_OBJECT_NAME;
                foreach ($objectNameMap as $k => $v) {
                    if (in_array($uri, $v)) {
                        $objectName = $k;
                    }
                }

                $actionMap = GlobalConfig::URI_API_NAME_MAP;
                $uriAction = $actionMap[$uri] ?? 'Unknown';

                $sessionLogInfoId = FresnsSessionLogsService::addSessionLogs($objectName, $objectType, $aid, $uid, null, $uriAction);

                $GLOBALS['session_logs_info']['session_log_id'] = $sessionLogInfoId;
            }
        }
    }

    public static function getGlobalSessionKey($globalKey)
    {
        return $GLOBALS['session_logs_info'][$globalKey] ?? null;
    }

    // Update Log
    public static function updateSessionLog()
    {
        $sessionLogInfo = [];

        $sessionLogTypeUriMap = [
            'type_register' => ['uri1', 'uri2', 'uri3'],
            'type_login' => ['uri6', 'uri4', 'uri5'],
        ];

        // $GLOBALS['session_logs_info'] = $sessionLogInfo;
        // $GLOBALS['session_log_id'] = 3333;
    }

    // Loading config data
    public static function loadGlobalData()
    {
        $hasConfig = Schema::hasTable(FresnsConfigsConfig::CFG_TABLE);
        if ($hasConfig) {
            $itemArr = FresnsConfigs::get()->toArray();
            $arr = [];
            foreach ($itemArr as $v) {
                $item = [];
                $item['item_key'] = $v['item_key'];
                $item['item_tag'] = $v['item_tag'];
                $item['item_type'] = $v['item_type'];
                $item['item_value'] = $v['item_value'];
                if ($v['item_tag'] == 'checkbox' || $v['item_type'] == 'select') {
                    if (strstr($item['item_value'], ',')) {
                        $item['item_value'] = explode(',', $v['item_value']);
                    }
                }
                if ($v['item_tag'] != 'file') {
                    if ($v['item_value'] == 'true') {
                        $item['item_value'] = true;
                    }
                    if ($v['item_value'] == 'false') {
                        $item['item_value'] = false;
                    }
                }
                $item['is_restful'] = $v['is_restful'];
                $item['is_multilingual'] = $v['is_multilingual'];
                $item['is_enable'] = $v['is_enable'];
                $arr[] = $item;
            }
            config([GlobalConfig::CONFIGS_LIST_API => $arr]);

            $mapArr = [];
            foreach ($arr as $v) {
                $mapArr[$v['item_tag']][] = $v;
            }

            $map = [];
            foreach ($mapArr as $k => $v) {
                $it = [];
                foreach ($v as $value) {
                    $it[$value['item_key']] = $value['item_value'];
                    $map[$k] = $it;
                }
            }
            $languageStatus = FresnsConfigs::where('item_key', FresnsConfigsConfig::LANGUAGE_STATUS)->where('is_restful', 1)->value('item_value');
            $langSettings = FresnsConfigs::where('item_key', FresnsConfigsConfig::LANG_SETTINGS)->where('is_restful', 1)->value('item_value');
            $langSettingsArr = json_decode($langSettings, true);
            $default = ApiLanguageHelper::getDefaultLanguageByApi();

            $lang['language_status'] = empty($languageStatus) ? null : boolval($languageStatus);
            $lang['default_language'] = $default;
            $lang['language_menus'] = $langSettingsArr;
            if (! empty($lang['language_status']) || ! empty($lang['default_language']) || ! empty($lang['language_menus'])) {
                $map['language'] = $lang;
            }

            config([GlobalConfig::CONFIGS_LIST => $map]);
        }
        // config(["lang.{key}_{lang_tag}", "{value}"]);
    }
}
