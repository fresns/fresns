<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Center\Base;

/**
 * Install of basic class.
 */
class BaseInstaller
{
    /**
     * Install config class.
     */
    protected $pluginConfig;

    /**
     * The installation function corresponding to each version when installing the plugin
     * Key: Integer version number (versionInt)
     * Value: Name of the installation function (functionName), example: installV1.
     *
     * @var array
     */
    public $versionIntInstallFunctionNameMap = [
        1   =>  'installV1',
    ];

    public function getPluginConfig(): BasePluginConfig
    {
        return $this->pluginConfig;
    }

    /**
     * Correspondence between each integer version and three versions, storing release records
     * Key: Integer version number (versionInt)
     * Value: Semantic version number (version), example: 1.0.0.
     *
     * @var array
     */
    public $versionIntToVersionMap = [
        1   =>  '1.0.0',
    ];

    /**
     * install, example:execute some sql insert.
     */
    public function install()
    {
        //
    }

    /**
     * uninstall, example:execute some sql delete.
     */
    public function uninstall()
    {
        //
    }

    /**
     * upgrade.
     */
    public function upgrade()
    {
        // Execute the install function if it is available in the current version
        // $currVersionInt = $this->pluginConfig->currVersionInt;
        $currVersionInt = request()->input('localVision');
        $remoteVision = request()->input('remoteVision');
        $installFunc = $this->versionIntInstallFunctionNameMap;

        for ($i = $currVersionInt + 1; $i <= $remoteVision; $i++) {
            $installFunc = $this->versionIntInstallFunctionNameMap[$i] ?? '';
            if (! empty($installFunc)) {
                $this->$installFunc();
            }
        }

        return true;
    }
}
