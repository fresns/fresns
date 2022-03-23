<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Center\Base;

use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\FsDb\FresnsCodeMessages\FresnsCodeMessagesService;

trait PluginTrait
{
    // Plugin Status Code
    public $code;

    // Plugin Status Code Message
    public $msg;

    // The data returned by the plugin
    public $data = [];

    // Status Code Mapping
    public $codeMap = [];

    /**
     * Plugin initialization.
     *
     * @return bool
     */
    public function initPlugin()
    {
        return true;
    }

    /**
     * Called Success.
     */
    protected function pluginSuccess($data = [])
    {
        $code = BasePluginConfig::OK;
        $msg = 'ok';

        // $pluginConfig = $m->pluginConfig; // Get unikey method 1
        $uniKey = $this->pluginConfig->uniKey; // Get unikey method 2
        $langTag = GlobalService::getGlobalKey('langTag');
        $message = FresnsCodeMessagesService::getCodeMessage($uniKey, $langTag, $code);
        if (empty($message)) {
            $message = $msg;
        }

        return $this->output($code, $message, $data);
    }

    /**
     * Calling exceptions.
     */
    protected function pluginError($code, $data = [], $msg = '')
    {
        $c = get_called_class();
        $m = new $c;
        $codeMap = $m->getPluginCodeMap();
        // $pluginConfig = $m->pluginConfig; // Get unikey method 1
        $uniKey = $this->pluginConfig->uniKey; // Get unikey method 2
        $langTag = GlobalService::getGlobalKey('langTag');
        $message = FresnsCodeMessagesService::getCodeMessage($uniKey, $langTag, $code);
        if (empty($message)) {
            $message = ErrorCodeService::getMsg($code, $data) ?? "Plugin check exception: [{$code}]";
        }

        return $this->output($code, $message, $data);
    }

    /**
     * Plugin returns data.
     */
    protected function output($code, $msg, $data)
    {
        $ret = [];
        $ret['plugin_code'] = $code;
        $ret['plugin_msg'] = $msg;
        $ret['plugin_data']['output'] = $data;

        return $ret;
    }

    // Status Code Mapping
    public function getPluginCodeMap()
    {
        return $this->codeMap;
    }
}
