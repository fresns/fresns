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
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

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
        foreach ($cronArr as $k => $v) {
            if ($v['unikey'] == $dtoWordBody->unikey && $v['cmdWord'] == $dtoWordBody->cmdWord) {
                $cronArr[$k] = $dtoWordBody;
            } else {
                $cronArr[] = $dtoWordBody;
            }
        }
        Config::where('item_key', '=', 'crontab_items')->update(['item_value' => $cronArr]);
        Cache::forever('cronArr', $cronArr);

        return ['code' => 200, 'message' => 'success', 'data' => []];
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

        return ['code' => 200, 'message' => 'success', 'data' => []];
    }

    /**
     * @return array
     */
    public function checkUserRoleExpired()
    {
        $roleArr = UserRole::where('type', '=', 2)->where('expired_at', '<', now())->whereNull('deleted_at')->get()->toArray();
        foreach ($roleArr as $role) {
            if (! empty($role['restore_role_id'])) {
                UserRole::where('id', '=', $role['id'])->update(['deleted_at' => now()]);
                $nextRole = UserRole::where(['role_id' => $role['restore_role_id'], 'user_id' => $role['user_id']])->where('id', '!=', $role['id'])->get();
                if (! empty($nextRole)) {
                    UserRole::where(['role_id' => $role['restore_role_id'], 'user_id' => $role['user_id']])->where('id', '!=', $role['id'])->whereNull('deleted_at')->update(['type' => 2]);
                } else {
                    UserRole::insert(['user_id' => $role['user_id'], 'role_id' => $role['restore_role_id'], 'type' => 2]);
                }
            }
        }

        return ['code' => 200, 'message' => 'success', 'data' => []];
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

        return ['code' => 200, 'message' => 'success', 'data' => []];
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
}
