<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsCmd;

use App\Fresns\Api\Center\Base\BasePluginConfig;

class FresnsCrontabPluginConfig extends BasePluginConfig
{
    // Add Timed Tasks
    public const ADD_CRONTAB_PLUGIN_ITEM = 'add_crontab_plugin_item';
    // Delete Timed Tasks
    public const DELETE_CRONTAB_PLUGIN_ITEM = 'delete_crontab_plugin_item';
    // Add Subscription Information
    public const ADD_SUB_PLUGIN_ITEM = 'add_sub_plugin_item';
    // Delete Subscription Information
    public const DELETE_SUB_PLUGIN_ITEM = 'delete_sub_plugin_item';
    // Perform account role expiration time detection every 10 minutes
    public const FRESNS_CMD_CRONTAB_CHECK_ROLE_EXPIRED = 'fresns_cmd_crontab_check_role_expired';
    // Timed Tasks: Logical Deletion and Physical Deletion
    public const FRESNS_CMD_CRONTAB_CHECK_DELETE_ACCOUNT = 'fresns_cmd_crontab_check_delete_account';

    // Plugin command word callback mapping
    const FRESNS_CMD_HANDLE_MAP = [
        self::ADD_SUB_PLUGIN_ITEM => 'addSubPluginItemHandler',
        self::DELETE_SUB_PLUGIN_ITEM => 'deleteSubTablePluginItemHandler',
        self::FRESNS_CMD_CRONTAB_CHECK_ROLE_EXPIRED => 'crontabCheckRoleExpiredHandler',
        self::FRESNS_CMD_CRONTAB_CHECK_DELETE_ACCOUNT => 'crontabCheckDeleteAccountHandler',
        self::ADD_CRONTAB_PLUGIN_ITEM => 'addCrontabPluginItemHandler',
        self::DELETE_CRONTAB_PLUGIN_ITEM => 'deleteCrontabPluginItemHandler',
    ];

    // Add Timed Tasks
    public function addCrontabPluginItemHandlerRule()
    {
        $rule = [
            'crontab_plugin_item' => 'required',
        ];

        return $rule;
    }

    // Delete Timed Tasks
    public function deleteCrontabPluginItemHandlerRule()
    {
        $rule = [
            'crontab_plugin_item' => 'required',
        ];

        return $rule;
    }

    // Add Subscription Information
    public function addSubPluginItemHandlerRule()
    {
        $rule = [
            'sub_table_plugin_item' => 'required',
        ];

        return $rule;
    }

    // Delete Subscription Information
    public function deleteSubPluginItemHandlerRule()
    {
        $rule = [
            'sub_table_plugin_item' => 'required',
        ];

        return $rule;
    }
}
