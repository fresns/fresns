<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\Center\Helper;

class CmdRpcHelper
{
    // rpc call
    public static function call($plugin, $cmd, $input, $option = [])
    {
        $plugin = new $plugin;
        $res = $plugin->handle($cmd, $input, $option);
        $res = self::formatCmdResp($res);

        return $res;
    }

    // Check if the service return is invalid
    public static function isErrorCmdResp($pluginResp)
    {
        $serverCode = $pluginResp['code'];
        if (intval($serverCode) == 0) {
            return false;
        }

        return true;
    }

    // Formatting server returns
    private static function formatCmdResp($pluginResp)
    {
        $code = $pluginResp['plugin_code'];
        $msg = $pluginResp['plugin_msg'];
        $output = [];

        $pluginData = $pluginResp['plugin_data'];

        if (isset($pluginData['output'])) {
            $output = $pluginData['output'];
        }

        $ret = [];
        $ret['code'] = $code;
        $ret['message'] = $msg;
        $ret['output'] = $output;

        return $ret;
    }
}
