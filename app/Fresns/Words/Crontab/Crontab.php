<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Crontab;

use App\Fresns\Words\Crontab\DTO\AddCrontabItemDTO;
use App\Fresns\Words\Crontab\DTO\DeleteCrontabItemDTO;
use App\Helpers\ConfigHelper;
use App\Models\Account;
use App\Models\Config;
use App\Models\Plugin;
use App\Models\User;
use App\Models\UserRole;
use App\Utilities\AppUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Crontab
{
    use CmdWordResponseTrait;

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function addCrontabItem($wordBody)
    {
        $dtoWordBody = new AddCrontabItemDTO($wordBody);
        $cronArr = ConfigHelper::fresnsConfigByItemKey('crontab_items');
        $cronIsset = 0;
        foreach ($cronArr as $k => $v) {
            if ($v['unikey'] == $dtoWordBody->unikey && $v['cmdWord'] == $dtoWordBody->cmdWord) {
                $cronArr[$k] = $wordBody;
                $cronIsset = 1;
            }
        }
        if (empty($cronIsset)) {
            $cronArr[] = $wordBody;
        }
        Config::where('item_key', 'crontab_items')->update(['item_value' => $cronArr]);
        Cache::forever('cronArr', $cronArr);

        return $this->success();
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function deleteCrontabItem($wordBody)
    {
        $dtoWordBody = new DeleteCrontabItemDTO($wordBody);
        $cronArr = ConfigHelper::fresnsConfigByItemKey('crontab_items');
        foreach ($cronArr as $k => $v) {
            if ($v['unikey'] == $dtoWordBody->unikey && $v['cmdWord'] == $dtoWordBody->cmdWord) {
                unset($cronArr[$k]);
            }
        }
        Config::where('item_key', 'crontab_items')->update(['item_value' => $cronArr]);
        Cache::forever('cronArr', $cronArr);

        return $this->success();
    }

    /**
     * @return array
     */
    public function checkUserRoleExpired()
    {
        $roleArr = UserRole::where('is_main', 1)->where('expired_at', '<', now())->get();

        foreach ($roleArr as $role) {
            if ($role->restore_role_id) {
                $nextRole = UserRole::where('id', '!=', $role->id)
                    ->where('user_id', $role->user_id)
                    ->where('role_id', $role->restore_role_id)
                    ->first();

                // change role
                if (empty($nextRole)) {
                    UserRole::create([
                        'user_id' => $role->user_id,
                        'role_id' => $role->restore_role_id,
                        'is_main' => 1,
                    ]);
                } else {
                    $nextRole->update([
                        'is_main' => 1,
                    ]);
                }

                // delete old role
                $role->delete();
            }

            // clear role cache
            Cache::forget("fresns_user_main_role_{$role->user_id}");
        }

        return $this->success();
    }

    /**
     * @return array
     */
    public function checkDeleteAccount()
    {
        $deleteType = ConfigHelper::fresnsConfigByItemKey('delete_account_type');

        if ($deleteType == 2) {
            $this->logicalDeletionAccount();
            $this->logicalDeletionUser();
        } elseif ($deleteType == 3) {
            $this->logicalDeletionAccount();
            $this->logicalDeletionUser();
        }

        return $this->success();
    }

    // logical deletion account
    protected function logicalDeletionAccount()
    {
        $deleteList = Account::where('wait_delete', 1)->where('wait_delete_at', '<', now())->get();

        foreach ($deleteList as $account) {
            \FresnsCmdWord::plugin('Fresns')->logicalDeletionAccount([
                'aid' => $account->aid,
            ]);
        }
    }

    // logical deletion user
    protected function logicalDeletionUser()
    {
        $deleteList = User::where('wait_delete', 1)->where('wait_delete_at', '<', now())->get();

        foreach ($deleteList as $user) {
            \FresnsCmdWord::plugin('Fresns')->logicalDeletionUser([
                'uid' => $user->uid,
            ]);
        }
    }

    public function checkExtensionsVersion()
    {
        $plugins = Plugin::all();

        AppUtility::macroMarketHeader();

        $response = Http::market()->get('/api/open-source/v2/check', [
            'unikeys' => json_encode($plugins->pluck('unikey')->all()),
        ]);

        // Request error
        if ($response->failed()) {
            return [
                'code' => 12000,
                'message' => 'Error: request failed (host or api)',
                'data' => [],
            ];
        }

        foreach ($response->json('data') as $unikey => $version) {
            if (is_null($version)) {
                continue;
            }

            $plugin = $plugins->where('unikey', $unikey)->first();

            // Same version number
            if (version_compare($plugin->version, $version) === 0) {
                continue;
            }

            $plugin->update([
                'is_upgrade' => 1,
                'upgrade_version' => $version,
            ]);
        }

        // Time of the latest check version
        $checkConfig = Config::where('item_key', 'check_version_datetime')->firstOrNew();
        $checkConfig->item_value = now();
        $checkConfig->save();

        return $this->success();
    }
}
