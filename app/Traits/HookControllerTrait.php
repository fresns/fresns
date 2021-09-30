<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Traits;

use App\Helpers\CommonHelper;
use Illuminate\Support\Facades\Route;

trait HookControllerTrait
{
    // Hook functions: store after verification, such as secondary verification
    public function hookStoreValidateAfter()
    {
        return true;
    }

    // Hook functions: update after verification, such as secondary verification
    public function hookUpdateValidateAfter()
    {
        return true;
    }

    // Check the server returns
    public function checkServerResp($serverResp)
    {
        return true;
    }

    // Formatting server returns
    public function formatServerResp($serverResp)
    {
        $code = $serverResp['server_code'];
        $msg = $serverResp['server_msg'];
        $output = [];
        $common = [];

        $serverData = $serverResp['server_data'];

        if (isset($serverData['output'])) {
            $output = $serverData['output'];
        }
        if (isset($serverData['common'])) {
            $common = $serverData['common'];
        }

        $ret = [];
        $ret['code'] = $code;
        $ret['msg'] = $msg;
        $ret['output'] = $output;
        $ret['common'] = $common;
        $ret['data']['output'] = $output;
        $ret['data']['common'] = $common;

        return $ret;
    }
}
