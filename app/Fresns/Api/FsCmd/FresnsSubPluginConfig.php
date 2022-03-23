<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsCmd;

use App\Fresns\Api\Center\Base\BasePluginConfig;

class FresnsSubPluginConfig extends BasePluginConfig
{
    const SUB_ADD_TABLE_PLUGINS = 'subscribe_plugins';
    const SUBSCRITE_TYPE1 = 1;
    const SUBSCRITE_TYPE2 = 2;
    const SUBSCRITE_TYPE3 = 3;
    const SUBSCRITE_TYPE4 = 4;
    const SUBSCRITE_TYPE5 = 5;

    // Scan for specified subscription information
    public const FRESNS_CMD_SUB_ADD_TABLE = 'fresns_cmd_sub_add_table';

    // Subscribe to account active status
    public const FRESNS_CMD_SUB_ACCOUNT_ACTIVE = 'fresns_cmd_sub_account_active';

    // Subscribe to command word active status
    public const FRESNS_CMD_SUB_ACTIVE_COMMAND_WORD = 'fresns_cmd_sub_active_command_word';

    const FRESNS_CMD_HANDLE_MAP = [
        self::FRESNS_CMD_SUB_ADD_TABLE => 'subAddTableHandler',
        self::FRESNS_CMD_SUB_ACCOUNT_ACTIVE => 'subAccountActiveHandler',
        self::FRESNS_CMD_SUB_ACTIVE_COMMAND_WORD => 'subActiveCmdHandler',
    ];

    public function subAddTableHandlerRule()
    {
        $rule = [
            'tableName' => 'required',
            'insertId' => 'required',
        ];

        return $rule;
    }
}
