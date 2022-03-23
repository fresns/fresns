<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsCmd;

use App\Fresns\Api\Center\Base\BasePlugin;
use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\Center\Helper\PluginHelper;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigs;

/**
 * Class FresnsCrontabPlugin
 * Subscription Event handle
 * https://fresns.org/extensions/basis.html.
 */
class FresnsSubPlugin extends BasePlugin
{
    // Constructors
    public function __construct()
    {
        $this->pluginConfig = new FresnsSubPluginConfig();
        $this->pluginCmdHandlerMap = FresnsSubPluginConfig::FRESNS_CMD_HANDLE_MAP;
    }

    // Get status code
    public function getCodeMap()
    {
        return FresnsSubPluginConfig::CODE_MAP;
    }

    // Scan for specified subscription information
    protected function subAddTableHandler($input)
    {
        $tableName = $input['tableName'];
        $insertId = $input['insertId'];
        // Query subscription information (configs > item_key: subscribe_plugins)
        $subscribe = FresnsConfigs::where('item_key', FresnsSubPluginConfig::SUB_ADD_TABLE_PLUGINS)->where('is_enable', 1)->first();
        $subscribeArr = '';
        if ($subscribe) {
            // $subscribeArr = $subscribe['item_value'];
            $subscribeArr = json_decode($subscribe['item_value'], true);
        }
        // Get the cmd and unikey of the sent command word
        $cmd = '';
        $unikey = '';
        if (! empty($subscribeArr)) {
            // Subscription type: 2
            // Execute anget_plugin_cmd for anget_plugin_unikey
            foreach ($subscribeArr as $s) {
                // if ($s['subscribe_type'] == FresnsSubPluginConfig::SUBSCRITE_TYPE2) {
                //     $cmd = $s['anget_plugin_cmd'];
                //     $unikey = $s['anget_plugin_unikey'];
                // }
                // Subscription type: 3
                // Execute subscribe_plugin_cmd for subscribe_plugin_unikey
                if ($s['subscribe_type'] == FresnsSubPluginConfig::SUBSCRITE_TYPE3 && $s['subscribe_table_name'] == $tableName) {
                    $cmd = $s['subscribe_plugin_cmd'];
                    $unikey = $s['subscribe_plugin_unikey'];
                    $pluginClass = PluginHelper::findPluginClass($unikey);
                    if (! $pluginClass) {
                        return $this->pluginSuccess();
                    }
                    $input = [
                        'tableName' => $tableName,
                        'insertId' => $insertId,
                    ];
                    $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
                }
            }
        }

        return $this->pluginSuccess();
    }

    // Subscribe to account activity status
    protected function subAccountActiveHandler($input)
    {
        // Query subscription information (configs > item_key: subscribe_plugins)
        $subscribe = FresnsConfigs::where('item_key', FresnsSubPluginConfig::SUB_ADD_TABLE_PLUGINS)->where('is_enable', 1)->first();
        if (! empty($subscribe)) {
            // $subscribeInfo = json_decode($subscribe['item_value'], true);
            $subscribeInfo = json_decode($subscribe['item_value'], true);
            if ($subscribeInfo) {
                foreach ($subscribeInfo as $s) {
                    if ($s['subscribe_type'] == FresnsSubPluginConfig::SUBSCRITE_TYPE4) {
                        $cmd = $s['subscribe_plugin_cmd'];
                        $unikey = $s['subscribe_plugin_unikey'];
                        $pluginClass = PluginHelper::findPluginClass($unikey);
                        if (! $pluginClass) {
                            return $this->pluginSuccess();
                        }
                        $input = [
                            'aid' => request()->header('aid'),
                            'uid' => request()->header('uid'),
                        ];
                        $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
                    }
                }
            }
        }

        return $this->pluginSuccess();
    }

    protected function subActiveCmdHandler($input)
    {
        $tableName = $input['tableName'];
        $insertId = $input['insertId'];
        $commandWord = $input['commandWord'];
        // Query subscription information (configs > item_key: subscribe_plugins)
        $subscribe = FresnsConfigs::where('item_key', FresnsSubPluginConfig::SUB_ADD_TABLE_PLUGINS)->where('is_enable', 1)->first();
        if (! empty($subscribe)) {
            $subscribeInfo = json_decode($subscribe['item_value'], true);
            // $subscribeInfo = $subscribe['item_value'];

            if ($subscribeInfo) {
                foreach ($subscribeInfo as $s) {
                    // Subscription type: 5
                    // Execute subscribe_plugin_cmd for subscribe_plugin_unikey
                    if ($s['subscribe_type'] == FresnsSubPluginConfig::SUBSCRITE_TYPE5 && $s['subscribe_command_word'] == $commandWord) {
                        $cmd = $s['subscribe_plugin_cmd'];
                        $unikey = $s['subscribe_plugin_unikey'];
                        $pluginClass = PluginHelper::findPluginClass($unikey);
                        if (! $pluginClass) {
                            return $this->pluginSuccess();
                        }
                        $input = [
                            'tableName' => $tableName,
                            'insertId' => $insertId,
                        ];
                        $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
                    }
                }
            }
        }

        return $this->pluginSuccess();
    }
}
