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
use App\Models\AccountConnect;
use App\Models\Config;
use App\Models\Plugin;
use App\Models\User;
use App\Models\UserRole;
use App\Utilities\AppUtility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Crontab
{
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
        Config::where('item_key', '=', 'crontab_items')->update(['item_value' => $cronArr]);
        Cache::forever('cronArr', $cronArr);

        return ['code' => 0, 'message' => 'success', 'data' => []];
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
        Config::where('item_key', '=', 'crontab_items')->update(['item_value' => $cronArr]);
        Cache::forever('cronArr', $cronArr);

        return ['code' => 0, 'message' => 'success', 'data' => []];
    }

    /**
     * @return array
     */
    public function checkUserRoleExpired()
    {
        $roleArr = UserRole::where('is_main', '=', 1)->where('expired_at', '<', now())->whereNull('deleted_at')->get()->toArray();
        foreach ($roleArr as $role) {
            if (! empty($role['restore_role_id'])) {
                UserRole::where('id', '=', $role['id'])->update(['deleted_at' => now()]);
                $nextRole = UserRole::where(['role_id' => $role['restore_role_id'], 'user_id' => $role['user_id']])->where('id', '!=', $role['id'])->get();
                if (! empty($nextRole)) {
                    UserRole::where(['role_id' => $role['restore_role_id'], 'user_id' => $role['user_id']])->where('id', '!=', $role['id'])->whereNull('deleted_at')->update(['is_main' => 1]);
                } else {
                    UserRole::insert(['user_id' => $role['user_id'], 'role_id' => $role['restore_role_id'], 'is_main' => 1]);
                }
            }
        }

        ConfigHelper::fresnsCountAdd('crontab_count');

        return ['code' => 0, 'message' => 'success', 'data' => []];
    }

    /**
     * @return array
     */
    public function checkDeleteAccount()
    {
        $deleteAccount = ConfigHelper::fresnsConfigByItemKey('delete_account');
        $bufferDay = ConfigHelper::fresnsConfigByItemKey('delete_account_todo');
        if ($deleteAccount == 2) {
            $this->logicDelete($bufferDay);
        } elseif ($deleteAccount == 3) {
        }

        return ['code' => 0, 'message' => 'success', 'data' => []];
    }

    /**
     * @param $bufferDay
     */
    protected function logicDelete($bufferDay)
    {
        $endTime = Carbon::now()->subDay($bufferDay)->toDateString();
        $startTime = Carbon::now()->subDay($bufferDay + 1)->toDateString();
        $delList = Account::onlyTrashed()->where('deleted_at', '<', $endTime)->where('deleted_at', '>', $startTime)->get();
        foreach ($delList as $k => $v) {
            $account = $v->toArray();
            if (strpos($account['phone'], 'deleted#') === false && strpos($account['email'], 'deleted#') === false) {
                Account::onlyTrashed()->where('id', '=', $v['id'])->update(['phone' => 'deleted#'.date('YmdHis').'#'.$v['phone'], 'email' => 'deleted#'.date('YmdHis').'#'.$v['email']]);
                AccountConnect::where('account_id', $v['id'])->delete();
                $userList = User::where('account_id', '=', $v['id'])->get();
                foreach ($userList as $user) {
                    $user = $user->toArray();
                    \FresnsCmdWord::plugin('Fresns')->deactivateUserDialog(['userId' => $user['id']]);
                }
                \FresnsCmdWord::plugin('Fresns')->logicalDeletionUser(['accountId' => $v['id']]);
            }
        }
    }

    public function checkExtensionsVersion()
    {
        $plugins = Plugin::all();

        AppUtility::macroMarketHeader();

        $response = Http::market()->get('/api/extensions/v1/check', [
            'unikeys' => json_encode($plugins->pluck('unikey')->all()),
        ]);

        // Request error
        if ($response->failed()) {
            return ['code' => 22500, 'message' => 'Error: request failed (host or api)', 'data' => []];
        }

        foreach ($response->json('data') as $unikey => $version) {
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

        // Time to cache execution detection
        cache([
            'checkExtensionsVersion-time' => now()->toDateTimeString(),
        ], now()->addMonths(3));

        return ['code' => 0, 'message' => 'success', 'data' => []];
    }
}
