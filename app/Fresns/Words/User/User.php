<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User;

use App\Fresns\Words\User\DTO\AddUserDTO;
use App\Fresns\Words\User\DTO\DeactivateUserDialogDTO;
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
use App\Utilities\ConfigUtility;
use App\Utilities\PermissionUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Facades\Hash;

class User
{
    use CmdWordResponseTrait;

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function addUser($wordBody)
    {
        $dtoWordBody = new AddUserDTO($wordBody);
        $langTag = \request()->header('langTag', config('app.locale'));

        $account = Account::where('aid', $dtoWordBody->aid)->first();
        if (empty($account)) {
            return $this->failure(
                34301,
                ConfigUtility::getCodeMessage(34301, 'Fresns', $langTag)
            );
        }

        $uid = StrHelper::generateDigital(8);
        $username = $dtoWordBody->username ?? \Str::random(8);
        $userArr = [
            'account_id' => $account->id,
            'uid' => $uid,
            'username' => $username,
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

        $defaultRoleId = ConfigHelper::fresnsConfigByItemKey('default_role');
        $roleArr = [
            'user_id' => $userId,
            'role_id' => $defaultRoleId,
            'is_main' => 1,
        ];
        UserRole::insert($roleArr);

        $statArr = ['user_id' => $userId];
        UserStat::insert($statArr);

        return $this->success([
            'aid' => $account->aid,
            'uid' => $uid,
            'username' => $username,
            'nickname' => $dtoWordBody->nickname,
        ]);
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
        $langTag = \request()->header('langTag', config('app.locale'));

        $user = User::where('uid', $dtoWordBody->uid)->first();
        $aid = $user->account->aid;

        if (empty($user) || $dtoWordBody->aid != $aid) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201, 'Fresns', $langTag)
            );
        }

        $loginErrorCount = ConfigUtility::getLoginErrorCount($user->account->id, $user->id);

        if ($loginErrorCount >= 5) {
            return $this->failure(
                34306,
                ConfigUtility::getCodeMessage(34306, 'Fresns', $langTag),
            );
        }

        if (! empty($user->password)) {
            if (empty($dtoWordBody->password)) {
                return $this->failure(
                    31410,
                    ConfigUtility::getCodeMessage(31410, 'Fresns', $langTag),
                );
            }

            if (! Hash::check($dtoWordBody->password, $user->password)) {
                return $this->failure(
                    35204,
                    ConfigUtility::getCodeMessage(35204, 'Fresns', $langTag),
                );
            }
        }

        $data['aid'] = $user->account->aid;
        $data['uid'] = $user->uid;

        return $this->success($data);
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

        return $this->success();
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

        return $this->success();
    }
}
