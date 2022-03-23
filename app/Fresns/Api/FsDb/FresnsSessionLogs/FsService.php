<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsSessionLogs;

use App\Fresns\Api\Base\Services\BaseAdminService;
use App\Fresns\Api\Center\Common\GlobalConfig;
use App\Fresns\Api\FsCmd\FresnsSubPluginService;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use Illuminate\Support\Facades\Request;

class FsService extends BaseAdminService
{
    public function __construct()
    {
        $this->model = new FsModel();
        $this->resource = FsResource::class;
        $this->resourceDetail = FsResourceDetail::class;
    }

    public function common()
    {
        $common = parent::common();

        return $common;
    }

    // Insert data into the session_logs table
    public static function addSessionLogs(
        $objectName,
        $objectAction,
        $accountId = null,
        $userId = null,
        $objectOrderId = null,
        $uri = null,
        $type = null
    ) {
        $deviceInfo = request()->header('deviceInfo');
        $platform_id = request()->header('platform');
        $version = request()->header('version');
        $versionInt = request()->header('versionInt');
        $langTag = ApiLanguageHelper::getLangTagByHeader();
        if (empty($platform_id) || empty($version) || empty($versionInt)) {
            return true;
        }

        $requestUri = Request::getRequestUri();
        $map = GlobalConfig::URI_CONVERSION_OBJECT_TYPE_NO;
        $objectType = '';

        if (empty($type)) {
            foreach ($map as $k => $v) {
                if (in_array($requestUri, $v)) {
                    $objectType = $k;
                }
            }
        } else {
            $objectType = $type;
        }
        if ($objectType == 15) {
            $objectName = $objectName;
        } else {
            $objectName = $requestUri;
        }

        $input = [
            'platform_id' => $platform_id,
            'version' => $version,
            'version_int' => $versionInt,
            'lang_tag' => $langTag,
            'object_type' => $objectType ?? 1,
            'object_name' => $objectName,
            'object_action' => $uri ?? $objectAction,
            'object_result' => 0,
            'object_order_id' => $objectOrderId ?? null,
            'device_info' => $deviceInfo,
            'account_id' => $accountId ?? null,
            'user_id' => $userId ?? null,
        ];

        $id = FresnsSessionLogs::insertGetId($input);
        FresnsSubPluginService::addSubTablePluginItem(FresnsSessionLogsConfig::CFG_TABLE, $id);

        return $id;
    }

    public static function updateSessionLogs($sessionLogsId, $status, $aid = null, $uid = null, $objectOrderId = null)
    {
        $input['object_result'] = $status;
        if ($aid) {
            $input['account_id'] = $aid;
        }
        if ($uid) {
            $input['user_id'] = $uid;
        }
        if ($objectOrderId) {
            $input['object_order_id'] = $objectOrderId;
        }

        FresnsSessionLogs::where('id', $sessionLogsId)->update($input);
    }

    // Fresns Console (Panel) Add Log
    public static function addConsoleSessionLogs($objectType, $objectAction, $accountId = null)
    {
        $fresnsVersion = ApiConfigHelper::getConfigByItemKey('fresns_version');
        $fresnsVersionInt = ApiConfigHelper::getConfigByItemKey('fresns_version_int');

        $input = [
            'platform_id' => '4',
            'version' => $fresnsVersion ?? 1,
            'version_int' => $fresnsVersionInt ?? 1,
            'object_type' => $objectType,
            'object_name' => Request::getRequestUri(),
            'object_action' => $objectAction,
            'object_result' => 0,
            'object_order_id' => $objectOrderId ?? null,
            'device_info' => '[1]',
            'account_id' => $accountId ?? null,
            'user_id' => $userId ?? null,
        ];

        $id = FresnsSessionLogs::insertGetId($input);

        return $id;
    }
}
