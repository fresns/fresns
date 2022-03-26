<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User;

use App\Fresns\Words\User\DTO\AddUserDTO;
use App\Fresns\Words\User\DTO\DeactivateUserDialogDTO;
use App\Fresns\Words\User\DTO\GetUserDetailDTO;
use App\Fresns\Words\User\DTO\LogicalDeletionUserDTO;
use App\Fresns\Words\User\DTO\VerifyUserDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\StrHelper;
use App\Models\Account;
use App\Models\Dialog;
use App\Models\File;
use App\Models\UserRole;
use App\Models\UserStat;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;
use Illuminate\Support\Facades\Hash;

class User
{
    /**
     * @param  AddUserDTO  $dtoWordBody
     * @return array
     */
    public function addUser($wordBody)
    {
        $dtoWordBody = new AddUserDTO($wordBody);
        if (! empty($dtoWordBody->avatarFid) && empty($dtoWordBody->avatar_file_url)) {
            $dtoWordBody->avatar_file_url = File::where('uuid', $dtoWordBody->avatarFid)->value('file_path');
        }
        $account_id = Account::where('aid', $dtoWordBody->aid)->value('id');
        if (empty($account_id)) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::ERROR_CODE_20009)::throw();
        }
        $userArr = [
            'account_id' => $account_id,
            'uid' => StrHelper::generateDigital(8),
            'username' => $dtoWordBody->username,
            'nickname' => $dtoWordBody->nickname,
            'password' => isset($dtoWordBody->password) ?? null,
            'avatarFid' => isset($dtoWordBody->avatarFid) ? File::where('uuid', $dtoWordBody->avatarFid)->value('id') : null,
            'avatarUrl' => $dtoWordBody->avatar_file_url ?? null,
            'gender' => $dtoWordBody->gender ?? 0,
            'birthday' => $dtoWordBody->birthday ?? '',
            'timezone' => $dtoWordBody->timezone ?? '',
            'language' => $dtoWordBody->language ?? '',
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

        return ['code' => 0, 'message' => 'success', 'data' => []];
    }

    /**
     * @param  VerifyUserDTO  $wordBody
     * @return array
     */
    public function verifyUser($wordBody)
    {
        $dtoWordBody = new VerifyUserDTO($wordBody);
        $user = User::where('uid', '=', $dtoWordBody->uid)->first();
        if ($user) {
            $result = ! Hash::check($dtoWordBody->password, $user->password);
        }
        $result = false;
        $data = ['aid' => $user->aid, 'uid' => $user->account_id];

        return $data;
    }

    /**
     * @param  GetUserDetailDTO  $wordBody
     * @return mixed
     */
    public function getUserDetail($wordBody)
    {
        $dtoWordBody = new GetUserDetailDTO($wordBody);
        if (isset($wordBody->uid)) {
            $condition = ['uid' => $dtoWordBody->uid];
        } else {
            $condition = ['username' => $dtoWordBody->username];
        }
        $detail = User::where($condition)->first();

        return $detail;
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function logicalDeletionUser($wordBody)
    {
        $dtoWordBody = new LogicalDeletionUserDTO($wordBody);
        \App\Models\User::where('uid', $dtoWordBody->uid)->update(['deleted_at' => now()]);

        return ['code' => 0, 'message' => 'success', 'data' => []];
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function deactivateUserDialog($wordBody)
    {
        $dtoWordBody = new DeactivateUserDialogDTO($wordBody);
        $user = \App\Models\User::where('uid', '=', $dtoWordBody->uid)->first();
        Dialog::where('a_user_id', '=', $user['id'])->update(['a_is_deactivate' => 0]);
        Dialog::where('b_user_id', '=', $user['id'])->update(['b_is_deactivate' => 0]);

        return ['code' => 0, 'message' => 'success', 'data' => []];
    }
}
