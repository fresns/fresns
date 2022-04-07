<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User;

use App\Fresns\Words\Service\UserService;
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
use App\Models\User as UserModel;
use App\Models\UserRole;
use App\Models\UserStat;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;
use Illuminate\Support\Facades\Hash;

class User
{
    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function addUser($wordBody)
    {
        $dtoWordBody = new AddUserDTO($wordBody);

        $account_id = Account::where('aid', $dtoWordBody->aid)->value('id');
        if (empty($account_id)) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_DATA_ERROR)::throw();
        }

        $userArr = [
            'account_id' => $account_id,
            'uid' => StrHelper::generateDigital(8),
            'username' => $dtoWordBody->username ?? \Str::random(8),
            'nickname' => $dtoWordBody->nickname,
            'password' => isset($dtoWordBody->password) ? Hash::make($dtoWordBody->password) : null,
            'avatarFid' => isset($dtoWordBody->avatarFid) ? File::where('fid', $dtoWordBody->avatarFid)->value('id') : null,
            'avatarUrl' => $dtoWordBody->avatar_file_url ?? null,
            'gender' => $dtoWordBody->gender ?? 0,
            'birthday' => $dtoWordBody->birthday ?? null,
            'timezone' => $dtoWordBody->timezone ?? null,
            'language' => $dtoWordBody->language ?? null,
        ];
        $userId = UserModel::insertGetId(array_filter($userArr));
        ConfigHelper::fresnsCountAdd('users_count');

        $defaultRoleId = ConfigHelper::fresnsConfigByItemKey('default_role');
        $roleArr = [
            'user_id' => $userId,
            'role_id' => $defaultRoleId,
            'is_main' => 1,
        ];
        UserRole::insert($roleArr);

        $statArr = ['user_id' => $userId];
        UserStat::insert($statArr);

        return ['code' => 0, 'message' => 'success', 'data' => []];
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
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

        return ['code' => 0, 'message' => 'success', 'data' => $data];
    }

    /**
     * @param $wordBody
     * @return mixed
     *
     * @throws \Throwable
     */
    public function getUserDetail($wordBody)
    {
        $dtoWordBody = new GetUserDetailDTO($wordBody);

        if (isset($dtoWordBody->uid)) {
            $condition = ['uid' => $dtoWordBody->uid];
        } else {
            $condition = ['username' => $dtoWordBody->username];
        }
        $userId = UserModel::where($condition)->value('id');
        if (empty($userId)) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_DATA_ERROR)::throw();
        }

        if (empty($dtoWordBody->langTag)) {
            $dtoWordBody->langTag = ConfigHelper::fresnsConfigByItemKey('default_language');
        }
        if (empty($dtoWordBody->timezone)) {
            $dtoWordBody->timezone = ConfigHelper::fresnsConfigByItemKey('default_timezone');
        }

        $service = new UserService();
        $detail = $service->detail($userId, $dtoWordBody->langTag, $dtoWordBody->timezone);

        return ['code' => 0, 'message' => 'success', 'data' => $detail];
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
        UserModel::where('uid', $dtoWordBody->uid)->update(['deleted_at' => now()]);

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
        $user = UserModel::where('uid', '=', $dtoWordBody->uid)->first();
        Dialog::where('a_user_id', '=', $user['id'])->update(['a_is_deactivate' => 0]);
        Dialog::where('b_user_id', '=', $user['id'])->update(['b_is_deactivate' => 0]);

        return ['code' => 0, 'message' => 'success', 'data' => []];
    }
}
