<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsPanel;

use App\Base\Services\BaseAdminService;
use App\Helpers\NetworkHelper;
use App\Http\UpgradeController;

class FsService extends BaseAdminService
{
    // Get the current setting language
    public static function getLanguage($lang)
    {
        $map = FsConfig::LANGUAGE_MAP;

        return $map[$lang] ?? 'English - English';
    }

    /**
     * version check.
     */
    public static function getVersionInfo()
    {
        $url = FsConfig::VERSION_URL;
        $result = NetworkHelper::get($url);
        if ($result->code != 200) {
            return ['currentVersion'=>UpgradeController::$version, 'canUpgrade'=>false, 'upgradeVersion'=>UpgradeController::$version, 'upgradePackage'=>''];
        }
        $api_version = json_decode($result->body, true);
        $current_version = UpgradeController::$versionInt;
        if (isset($api_version['versionInt']) && $api_version['versionInt'] > $current_version) {
            return ['currentVersion'=>UpgradeController::$version, 'canUpgrade'=>true, 'upgradeVersion'=>$api_version['version'], 'upgradePackage'=>$api_version['upgradePackage']];
        } else {
            return ['currentVersion'=>UpgradeController::$version, 'canUpgrade'=>false, 'upgradeVersion'=>UpgradeController::$version, 'upgradePackage'=>''];
        }
    }
}
