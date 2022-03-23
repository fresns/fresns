<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsCmd;

use App\Fresns\Api\Center\Base\BasePlugin;
use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\FsDb\FresnsAccountConnects\FresnsAccountConnectsConfig;
use App\Fresns\Api\FsDb\FresnsAccounts\FresnsAccountsConfig;
use App\Fresns\Api\FsDb\FresnsAccountWalletLogs\FresnsAccountWalletLogsConfig;
use App\Fresns\Api\FsDb\FresnsAccountWallets\FresnsAccountWalletsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsCommentsConfig;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigs;
use App\Fresns\Api\FsDb\FresnsDialogs\FresnsDialogsConfig;
use App\Fresns\Api\FsDb\FresnsFileAppends\FresnsFileAppendsConfig;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFilesConfig;
use App\Fresns\Api\FsDb\FresnsNotifies\FresnsNotifiesConfig;
use App\Fresns\Api\FsDb\FresnsPluginBadges\FresnsPluginBadgesConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsConfig;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogsConfig;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogsService;
use App\Fresns\Api\FsDb\FresnsSessionTokens\FresnsSessionTokensConfig;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocksConfig;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollowsConfig;
use App\Fresns\Api\FsDb\FresnsUserIcons\FresnsUserIconsConfig;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikesConfig;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRoles;
use App\Fresns\Api\FsDb\FresnsUserRoles\FresnsUserRolesConfig;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\FsDb\FresnsUserStats\FresnsUserStatsConfig;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use Illuminate\Support\Facades\DB;

/**
 * Class FresnsCrontabPlugin
 * Fresns (https://fresns.org) Timed Tasks.
 */
class FresnsCrontabPlugin extends BasePlugin
{
    // Constructors
    public function __construct()
    {
        $this->pluginConfig = new FresnsCrontabPluginConfig();
        $this->pluginCmdHandlerMap = FresnsCrontabPluginConfig::FRESNS_CMD_HANDLE_MAP;
    }

    // Add subscription information
    protected function addSubPluginItemHandler($input)
    {
        $item = $input['sub_table_plugin_item'];
        $itemSubscribeType = $item['subscribe_type'] ?? null;
        $itemSubscribePluginUnikey = $item['subscribe_plugin_unikey'] ?? null;
        $itemSubscribeTableName = $item['subscribe_table_name'] ?? null;
        $itemSubscribeCommandWord = $item['subscribe_command_word'] ?? null;
        $config = FresnsConfigs::where('item_key', FresnsSubPluginConfig::SUB_ADD_TABLE_PLUGINS)->first();
        if (! empty($config)) {
            $configArr = json_decode($config['item_value'], true);
            $newConfigArr = [];
            foreach ($configArr as $value) {
                $subscribeType = $value['subscribe_type'] ?? null;
                $subscribePluginUnikey = $value['subscribe_plugin_unikey'] ?? null;
                $subscribeTableName = $value['subscribe_table_name'] ?? null;
                $subscribeCommandWord = $value['subscribe_command_word'] ?? null;
                if ($itemSubscribeType == 3) {
                    if ($itemSubscribeType == $subscribeType && $itemSubscribePluginUnikey == $subscribePluginUnikey && $itemSubscribeTableName == $subscribeTableName) {
                        continue;
                    }
                }
                if ($itemSubscribeType == 4) {
                    if ($itemSubscribeType == $subscribeType && $itemSubscribePluginUnikey == $subscribePluginUnikey) {
                        continue;
                    }
                }
                if ($itemSubscribeType == 5) {
                    if ($itemSubscribeType == $subscribeType && $itemSubscribePluginUnikey == $subscribePluginUnikey && $itemSubscribeCommandWord == $subscribeCommandWord) {
                        continue;
                    }
                }
                $newConfigArr[] = $value;
            }

            $data = array_merge([$item], $newConfigArr);

            FresnsConfigs::where('item_key', FresnsSubPluginConfig::SUB_ADD_TABLE_PLUGINS)->update(['item_value' => $data]);
        } else {
            $input = [
                'item_key' => FresnsSubPluginConfig::SUB_ADD_TABLE_PLUGINS,
                'item_tag' => 'sites',
                'item_type' => 'plugin',
                'item_value' => json_encode($item),
            ];
            FresnsConfigs::insert($input);
        }

        return $this->pluginSuccess();
    }

    // Delete subscription information
    protected function deleteSubPluginItemHandler($input)
    {
        $item = $input['sub_table_plugin_item'];
        $config = FresnsConfigs::where('item_key', FresnsSubPluginConfig::SUB_ADD_TABLE_PLUGINS)->first();
        if (empty($config['item_value'])) {
            return $this->pluginError(ErrorCodeService::DATA_EXCEPTION_ERROR, 'Data is empty');
        }
        $configArr = json_decode($config['item_value'], true);
        $dataArr = [];
        foreach ($configArr as $v) {
            $isDel = 0;
            foreach ($item as $value) {
                if ($v['subscribe_plugin_unikey'] == $value['subscribe_plugin_unikey']) {
                    // if ($v['subscribe_plugin_unikey'] == $value['subscribe_plugin_unikey'] && $v['subscribe_plugin_cmd'] == $value['subscribe_plugin_cmd'] && $v['subscribe_table_name'] == $value['subscribe_table_name']) {
                    $isDel = 1;
                    break;
                }
            }
            if ($isDel == 1) {
                continue;
            }
            $dataArr[] = $v;
        }

        FresnsConfigs::where('item_key', FresnsSubPluginConfig::SUB_ADD_TABLE_PLUGINS)->update(['item_value' => $dataArr]);

        return $this->pluginSuccess();
    }

    // New Timed Tasks
    protected function addCrontabPluginItemHandler($input)
    {
        $item = $input['crontab_plugin_item'];
        $config = FresnsConfigs::where('item_key', 'crontab_plugins')->first();
        if (! empty($config)) {
            $configArr = json_decode($config['item_value'], true);
            foreach ($item as $v) {
                foreach ($configArr as $value) {
                    if ($v['crontab_plugin_unikey'] == $value['crontab_plugin_unikey'] && $v['crontab_plugin_cmd'] == $value['crontab_plugin_cmd']) {
                        return $this->pluginError(ErrorCodeService::DATA_EXCEPTION_ERROR, 'There are duplicate data');
                    }
                }
            }
            $data = array_merge($item, $configArr);
            FresnsConfigs::where('item_key', 'crontab_plugins')->update(['item_value' => $data]);
        } else {
            $input = [
                'item_key' => 'crontab_plugins',
                'item_value' => json_encode($item),
            ];
            FresnsConfigs::insert($input);
        }

        return $this->pluginSuccess();
    }

    // Delete Timed Tasks
    protected function deleteCrontabPluginItemHandler($input)
    {
        $item = $input['crontab_plugin_item'];
        $config = FresnsConfigs::where('item_key', 'crontab_plugins')->first();

        if (empty($config['item_value'])) {
            return $this->pluginError(ErrorCodeService::DATA_EXCEPTION_ERROR, 'Data is empty');
        }
        $configArr = json_decode($config['item_value'], true);
        $dataArr = [];
        foreach ($configArr as $v) {
            $isDel = 0;
            foreach ($item as $value) {
                if ($v['crontab_plugin_unikey'] == $value['crontab_plugin_unikey'] && $v['crontab_plugin_cmd'] == $value['crontab_plugin_cmd']) {
                    $isDel = 1;
                    break;
                }
            }
            if ($isDel == 1) {
                continue;
            }
            $dataArr[] = $v;
        }

        FresnsConfigs::where('item_key', 'crontab_plugins')->update(['item_value' => $dataArr]);

        return $this->pluginSuccess();
    }

    /**
     * Perform account role expiration time detection every 10 minutes
     * https://fresns.org/contributing/information/task.html.
     */
    // Database Table: user_roles
    protected function crontabCheckRoleExpiredHandler($input)
    {
        $sessionId = FresnsSessionLogsService::addSessionLogs('fresns_cmd_crontab_check_role_expired', 'Timed Tasks: Check Role Expired', null, null, null, null, 15);
        $userInfo = FresnsUserRoles::where('type', 2)->where('expired_at', '!=', null)->get()->toArray();
        if ($userInfo) {
            foreach ($userInfo as $m) {
                $expire_times = strtotime($m['expired_at']);
                // Determine whether the date has passed, and delete the record if the date has passed
                if ($expire_times < time()) {
                    // Determine if the record restore_role_id has a value
                    if (! empty($m['restore_role_id'])) {
                        // Determines if the value is associated with the user
                        $userCount = FresnsUserRoles::where('user_id', $m['user_id'])->where('role_id', $m['restore_role_id'])->count();
                        if ($userCount == 0) {
                            // No association, delete the expired record and create a record with the estore_role_id field value
                            $inputs = [
                                'type' => 2,
                                'user_id' => $m['user_id'],
                                'role_id' => $m['restore_role_id'],
                            ];
                            (new FresnsUserRoles())->store($inputs);
                        } else {
                            // There is an association, delete the expired record, change the associated type to 2
                            FresnsUserRoles::where('user_id', $m['user_id'])->where('role_id', $m['restore_role_id'])->update(['type' => 2]);
                        }
                        FresnsUserRoles::where('id', $m['id'])->delete();
                    }
                }
            }
        }

        FresnsSessionLogsService::updateSessionLogs($sessionId, 2);

        return $this->pluginSuccess();
    }

    /**
     * Perform account deletion tasks every 8 hours：
     * configs > item_key: delete_account
     * 1.Delete function is not enabled
     * 2.Logical Deletion
     * 3.Physical Deletion
     * https://fresns.org/contributing/information/task.html.
     */
    protected function crontabCheckDeleteAccountHandler($input)
    {
        $sessionId = FresnsSessionLogsService::addSessionLogs('fresns_cmd_crontab_check_delete_account', 'Timed Tasks: Check Delete Account', null, null, null, null, 15);
        $deleteAccount = ApiConfigHelper::getConfigByItemKey('delete_account');
        $deleteAccountTodo = ApiConfigHelper::getConfigByItemKey('delete_account_todo') ?? 0;
        if ($deleteAccount == 1) {
            return $this->pluginSuccess();
        }
        // Query all the data with the value of deleted_at
        $accounts = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('deleted_at', '!=', null)->get([
            'id',
            'email',
            'phone',
            'deleted_at',
        ])->toArray();
        // $accounts = DB::table(FresnsAccountsConfig::CFG_TABLE)->where('deleted_at',NULL)->get(['id','email','phone','deleted_at'])->toArray();
        $time = date('Y-m-d H:i:s', time());
        if ($accounts) {
            foreach ($accounts as $v) {
                $accountDeleteTime = date('Y-m-d H:i:s', strtotime("+$deleteAccountTodo day", strtotime($v->deleted_at)));
                if ($accountDeleteTime > $time) {
                    continue;
                }

                // Determine which deletion method is currently in use
                // 2.Logical Deletion
                // 3.Physical Deletion
                if ($deleteAccount == 2) {
                    $isEmail = strstr($v->email, 'deleted#');
                    $isPhone = strstr($v->phone, 'deleted#');
                    if ($isEmail != false || $isPhone != false) {
                        continue;
                    }
                    $this->softDelete($v);
                }

                if ($deleteAccount == 3) {
                    $this->hardDelete($v);
                }
            }
        }

        FresnsSessionLogsService::updateSessionLogs($sessionId, 2);

        return $this->pluginSuccess();
    }

    /**
     * 2.Logical Deletion.
     */
    public function softDelete($input)
    {
        $deleteTime = date('YmdHis', time());
        $id = $input->id;
        $email = $input->email;
        $phone = $input->phone;

        $deletePrefix = 'deleted#'.$deleteTime.'#';
        $input = [];
        if ($email) {
            $input['email'] = $deletePrefix.$email;
        }
        if ($phone) {
            $input['phone'] = $deletePrefix.$phone;
        }

        DB::table(FresnsAccountsConfig::CFG_TABLE)->where('id', $id)->update($input);
        DB::table(FresnsAccountConnectsConfig::CFG_TABLE)->where('account_id', $id)->delete();
        /**
         * Handle Dialogs: If a record exists, it is marked as deactivated
         * The column is a_is_deactivate or b_is_deactivate.
         */
        // Query all user ids under the account
        $userIdArr = DB::table(FresnsUsersConfig::CFG_TABLE)->where('account_id', $id)->pluck('id')->toArray();
        if ($userIdArr) {
            $aInput = [
                'a_is_deactivate' => 0,
            ];
            $bInput = [
                'b_is_deactivate' => 0,
            ];
            DB::table(FresnsDialogsConfig::CFG_TABLE)->whereIn('a_user_id', $userIdArr)->update($aInput);
            DB::table(FresnsDialogsConfig::CFG_TABLE)->whereIn('b_user_id', $userIdArr)->update($bInput);
        }
    }

    /**
     * 3.Physical Deletion
     * Delete all records from the following tables of the account.
     *
     * accounts
     * account_connects
     * account_wallets
     * account_wallet_logs
     * session_tokens
     * session_logs
     * plugin_badges
     * users
     * user_stats
     * user_roles
     * user_icons
     * user_likes
     * user_follows
     * user_blocks
     * files
     * file_appends
     * notifies
     * seo
     * posts
     * post_logs
     * comments
     * comment_logs
     * https://fresns.org/extensions/delete.html
     */
    public function hardDelete($data)
    {
        $deleteTime = date('YmdHis', time());
        $id = $data->id;
        $email = $data->email;
        $phone = $data->phone;
        // Query all user ids under the account
        $userIdArr = DB::table(FresnsUsersConfig::CFG_TABLE)->where('account_id', $id)->pluck('id')->toArray();
        // Physical Deletion: data
        DB::table(FresnsAccountsConfig::CFG_TABLE)->where('id', $id)->delete();
        DB::table(FresnsAccountConnectsConfig::CFG_TABLE)->where('account_id', $id)->delete();
        DB::table(FresnsAccountWalletsConfig::CFG_TABLE)->where('account_id', $id)->delete();
        DB::table(FresnsAccountWalletLogsConfig::CFG_TABLE)->where('account_id', $id)->delete();
        DB::table(FresnsPluginBadgesConfig::CFG_TABLE)->whereIn('user_id', $userIdArr)->delete();
        DB::table(FresnsUsersConfig::CFG_TABLE)->where('account_id', $id)->delete();
        DB::table(FresnsUserStatsConfig::CFG_TABLE)->whereIn('user_id', $userIdArr)->delete();
        DB::table(FresnsUserRolesConfig::CFG_TABLE)->whereIn('user_id', $userIdArr)->delete();
        DB::table(FresnsUserIconsConfig::CFG_TABLE)->whereIn('user_id', $userIdArr)->delete();
        DB::table(FresnsUserLikesConfig::CFG_TABLE)->whereIn('user_id', $userIdArr)->delete();
        DB::table(FresnsUserLikesConfig::CFG_TABLE)->where('like_type', 1)->whereIn('like_id', $userIdArr)->delete();
        DB::table(FresnsUserFollowsConfig::CFG_TABLE)->whereIn('user_id', $userIdArr)->delete();
        DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('follow_type', 1)->whereIn('follow_id', $userIdArr)->delete();
        DB::table(FresnsUserBlocksConfig::CFG_TABLE)->whereIn('user_id', $userIdArr)->delete();
        DB::table(FresnsUserBlocksConfig::CFG_TABLE)->where('block_type', 1)->whereIn('block_id', $userIdArr)->delete();
        DB::table(FresnsSessionLogsConfig::CFG_TABLE)->where('account_id', $id)->delete();
        DB::table(FresnsSessionTokensConfig::CFG_TABLE)->where('account_id', $id)->delete();
        $fileIdArr = DB::table(FresnsFileAppendsConfig::CFG_TABLE)->where('account_id', $id)->pluck('file_id')->toArray();
        $fileUuIdArr = DB::table(FresnsFileAppendsConfig::CFG_TABLE)->where('account_id', $id)->pluck('aid')->toArray();
        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_PHYSICAL_DELETION_FILE;
        // Physical Deletion: files
        foreach ($fileUuIdArr as $v) {
            $input = [];
            $input['fid'] = $v;
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
        }
        DB::table(FresnsFilesConfig::CFG_TABLE)->whereIn('id', $fileIdArr)->delete();
        DB::table(FresnsFileAppendsConfig::CFG_TABLE)->whereIn('file_id', $fileIdArr)->delete();
        DB::table(FresnsNotifiesConfig::CFG_TABLE)->whereIn('user_id', $userIdArr)->delete();
        DB::table(FresnsNotifiesConfig::CFG_TABLE)->whereIn('source_id', $userIdArr)->delete();
        DB::table('seo')->where('linked_type', 1)->whereIn('linked_id', $userIdArr)->delete();
        // Physical Deletion: post data
        $postIdArr = DB::table(FresnsPostsConfig::CFG_TABLE)->whereIn('user_id', $userIdArr)->pluck('id')->toArray();
        if ($postIdArr) {
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_DELETE_CONTENT;
            foreach ($postIdArr as $v) {
                $input = [];
                $input['type'] = 1;
                $input['content'] = $v;
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            }
        }
        // Physical Deletion: comment data
        $commentIdArr = DB::table(FresnsCommentsConfig::CFG_TABLE)->whereIn('user_id', $userIdArr)->pluck('id')->toArray();
        if ($commentIdArr) {
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_DELETE_CONTENT;
            foreach ($commentIdArr as $v) {
                $input = [];
                $input['type'] = 2;
                $input['content'] = $v;
                $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            }
        }
        // Handle Dialogs
        if ($userIdArr) {
            $DialogsInput = [
                'a_is_deactivate' => 0,
                'b_is_deactivate' => 0,
            ];
            DB::table(FresnsDialogsConfig::CFG_TABLE)->whereIn('a_user_id', $userIdArr)->orWhere('b_user_id', $userIdArr)->update($DialogsInput);
        }
    }
}
