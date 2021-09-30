<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\Center\Base;

use App\Http\Center\Common\LogService;
use App\Http\Center\Common\ValidateService;

class BasePlugin
{
    use PluginTrait;

    /**
     * Service config class.
     */
    public $pluginConfig = null;

    /**
     * Command word mapping.
     *
     * @var array
     */
    public $pluginCmdHandlerMap = [];

    // Constructors
    public function __construct()
    {
        $this->pluginConfig = new BasePluginConfig();
        $this->pluginCmdHandlerMap = $this->getPluginCmdHandlerMap();
        $this->initPlugin();
    }

    // Check command word
    protected function checkPluginCmdExist($cmd)
    {
        $method = $this->pluginCmdHandlerMap[$cmd] ?? '';
        if (empty($method)) {
            return false;
        }

        return true;
    }

    // Execute method calls
    public function handle($cmd, $params, $options = [])
    {
        LogService::info("Plugin Request: cmd [$cmd] Parameter", $params);

        // Check command word
        if (! $this->checkPluginCmdExist($cmd)) {
            return $this->pluginError(BasePluginConfig::CODE_NOT_EXIST);
        }

        $method = $this->pluginCmdHandlerMap[$cmd] ?? '';
        $methodRule = $method.'Rule';

        // Parameter verification
        if (method_exists($this->pluginConfig, $methodRule)) {
            $validRes = ValidateService::validateServerRule($params, $this->pluginConfig->{$methodRule}());

            if ($validRes !== true) {
                LogService::info("Plugin Request: cmd [$cmd] Parameter Exception", $validRes);

                return $this->pluginError(BasePluginConfig::CODE_PARAMS_ERROR, $validRes);
            }
        }

        // Implementation Method
        $result = $this->$method($params);

        LogService::info("Plugin Request: cmd [$cmd] Results", $result);

        // Proxy mode returns service results directly without any handle
        return $result;
    }

    // Get Plugin Cmd Handler Map
    public function getPluginCmdHandlerMap()
    {
        return [];
    }

    // Get Status Code
    public function getCodeMap()
    {
        return BasePluginConfig::CODE_MAP;
    }
}
