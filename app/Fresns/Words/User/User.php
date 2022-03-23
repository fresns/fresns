<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User;

use App\Fresns\Words\User\DTO\AddUser;
use App\Fresns\Words\User\DTO\DeactivateUserDialog;
use App\Fresns\Words\User\DTO\GetUserDetail;
use App\Fresns\Words\User\DTO\LogicalDeletionUser;
use App\Fresns\Words\User\DTO\VerifyUser;
use App\Helpers\ConfigHelper;
use App\Helpers\StrHelper;
use App\Models\Account;
use App\Models\Dialog;
use App\Models\File;
use App\Models\UserRole;
use App\Models\UserStat;
use Illuminate\Support\Facades\Hash;

class User
{
    /**
     * @param  AddUser  $wordBody
     * @return array
     */
    public function addUser(AddUser $wordBody)
    {
        if (! empty($wordBody->avatarFid) && empty($wordBody->avatar_file_url)) {
            $wordBody->avatar_file_url = File::where('uuid', $wordBody->avatarFid)->value('file_path');
        }
        $account_id = Account::where('aid', $wordBody->aid)->value('id');
        if (empty($account_id)) {
            return ['error'];
        }
        $userArr = [
            'account_id' => $account_id,
            'uid' => StrHelper::generateDigital(8),
            'username' => $wordBody->username,
            'nickname' => $wordBody->nickname,
            'password' => isset($wordBody->password) ?? null,
            'avatarFid' => isset($wordBody->avatarFid) ? File::where('uuid', $wordBody->avatarFid)->value('id') : null,
            'avatarUrl' => $wordBody->avatar_file_url ?? null,
            'gender' => $wordBody->gender ?? 0,
            'birthday' => $wordBody->birthday ?? '',
            'timezone' => $wordBody->timezone ?? '',
            'language' => $wordBody->language ?? '',
        ];
        $uid = \App\Models\User::insertGetId(array_filter($userArr));
        $defaultRoleId = ConfigHelper::fresnsConfigByItemKey('default_role');
        $roleArr = [
            'user_id' => $uid,
            'role_id' => $defaultRoleId,
            'type' => 2,
        ];
        UserRole::insert($roleArr);
        $statArr = ['user_id' => $uid];
        UserStat::insert($statArr);
        $countAdd = ConfigHelper::fresnsCountAdd('users_count');

        return [];
    }

    /**
     * @param  VerifyUser  $wordBody
     * @return array
     */
    public function verifyUser(VerifyUser $wordBody)
    {
        $user = User::where('uid', '=', $wordBody->uid)->first();
        if ($user) {
            $result = ! Hash::check($wordBody->password, $user->password);
        }
        $result = false;
        $data = ['aid' => $user->aid, 'uid' => $user->account_id];

        return $data;
    }

    /**
     * @param  GetUserDetail  $wordBody
     * @return mixed
     */
    public function getUserDetail(GetUserDetail $wordBody)
    {
        if (isset($wordBody->uid)) {
            $condition = ['uid' => $wordBody->uid];
        } else {
            $condition = ['username' => $wordBody->username];
        }
        $detail = User::where($condition)->first();

        return $detail;
    }

    public function logicalDeletionUser($wordBody)
    {
        $wordBody = new LogicalDeletionUser($wordBody);
        \App\Models\User::where('account_id', $wordBody->accountId)->update(['deleted_at'=>now()]);

        return ['code'=>200, 'message'=>'success', 'data'=>[]];
    }

    public function deactivateUserDialog($wordBody)
    {
        $wordBody = new DeactivateUserDialog($wordBody);
        $user = \App\Models\User::where('id', '=', $wordBody->userId)->first();
        Dialog::where('a_user_id', '=', $user['id'])->update(['a_is_deactivate'=>0]);
        Dialog::where('b_user_id', '=', $user['id'])->update(['b_is_deactivate'=>0]);

        return ['code'=>200, 'message'=>'success', 'data'=>[]];
    }
}
