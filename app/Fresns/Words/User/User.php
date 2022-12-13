<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User;

use App\Fresns\Words\User\DTO\AddUserDTO;
use App\Fresns\Words\User\DTO\CreateUserTokenDTO;
use App\Fresns\Words\User\DTO\LogicalDeletionUserDTO;
use App\Fresns\Words\User\DTO\VerifyUserDTO;
use App\Fresns\Words\User\DTO\VerifyUserTokenDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Account;
use App\Models\Conversation;
use App\Models\File;
use App\Models\SessionToken;
use App\Models\User as UserModel;
use App\Models\UserRole;
use App\Models\UserStat;
use App\Utilities\ConfigUtility;
use Carbon\Carbon;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Facades\Cache;
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

        if ($dtoWordBody->aidToken) {
            $verifyAccountToken = \FresnsCmdWord::plugin()->verifyAccountToken($wordBody);

            if ($verifyAccountToken->isErrorResponse()) {
                return $verifyAccountToken->errorResponse();
            }
        }

        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $account = Account::where('aid', $dtoWordBody->aid)->first();
        if (empty($account)) {
            return $this->failure(
                34301,
                ConfigUtility::getCodeMessage(34301, 'Fresns', $langTag)
            );
        }

        $userArr = [
            'account_id' => $account->id,
            'username' => $dtoWordBody->username,
            'nickname' => $dtoWordBody->nickname,
            'password' => isset($dtoWordBody->password) ? Hash::make($dtoWordBody->password) : null,
            'avatarFid' => isset($dtoWordBody->avatarFid) ? File::where('fid', $dtoWordBody->avatarFid)->value('id') : null,
            'avatarUrl' => $dtoWordBody->avatar_file_url ?? null,
            'gender' => $dtoWordBody->gender ?? 0,
            'birthday' => $dtoWordBody->birthday ?? null,
            'timezone' => $dtoWordBody->timezone ?? null,
            'language' => $dtoWordBody->language ?? null,
        ];
        $userModel = UserModel::create(array_filter($userArr));

        $defaultRoleId = ConfigHelper::fresnsConfigByItemKey('default_role');
        $roleArr = [
            'user_id' => $userModel->id,
            'role_id' => $defaultRoleId,
            'is_main' => 1,
        ];
        UserRole::create($roleArr);

        $statArr = ['user_id' => $userModel->id];
        UserStat::create($statArr);

        return $this->success([
            'aid' => $account->aid,
            'aidToken' => $dtoWordBody->aidToken,
            'uid' => $userModel->uid,
            'username' => $userModel->username,
            'nickname' => $userModel->nickname,
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

        $verifyAccountToken = \FresnsCmdWord::plugin()->verifyAccountToken($wordBody);

        if ($verifyAccountToken->isErrorResponse()) {
            return $verifyAccountToken->errorResponse();
        }

        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $user = UserModel::where('uid', $dtoWordBody->uid)->first();

        if (empty($user) || $user?->account_id != $accountId) {
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
                    34111,
                    ConfigUtility::getCodeMessage(34111, 'Fresns', $langTag),
                );
            }

            if (! Hash::check($dtoWordBody->password, $user->password)) {
                return $this->failure(
                    35204,
                    ConfigUtility::getCodeMessage(35204, 'Fresns', $langTag),
                );
            }
        }

        $data['aid'] = $dtoWordBody->aid;
        $data['aidToken'] = $dtoWordBody->aidToken;
        $data['uid'] = $user->uid;

        return $this->success($data);
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function createUserToken($wordBody)
    {
        $dtoWordBody = new CreateUserTokenDTO($wordBody);

        $verifyAccountToken = \FresnsCmdWord::plugin()->verifyAccountToken($wordBody);

        if ($verifyAccountToken->isErrorResponse()) {
            return $verifyAccountToken->errorResponse();
        }

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);

        $userTokenModel = SessionToken::where('app_id', $dtoWordBody->appId)
            ->where('account_id', $accountId)
            ->where('account_token', $dtoWordBody->aidToken)
            ->where('user_id', $userId)
            ->first();

        if ($userTokenModel) {
            if (empty($userTokenModel->expired_at) || $userTokenModel->expired_at->greaterThan(Carbon::now())) {
                $expiredHours = null;
                $expiredDays = null;
                $expiredDateTime = null;
                if ($userTokenModel->expired_at) {
                    $expiredHours = $userTokenModel->expired_at->diffInHours(Carbon::now());
                    $expiredDays = $userTokenModel->expired_at->diffInDays(Carbon::now());
                    $expiredDateTime = date('Y-m-d H:i:s', $userTokenModel->expired_at);
                }

                return $this->success([
                    'aid' => $dtoWordBody->aid,
                    'aidToken' => $dtoWordBody->aidToken,
                    'uid' => $dtoWordBody->uid,
                    'uidToken' => $userTokenModel->user_token,
                    'uidTokenId' => $userTokenModel->id,
                    'expiredHours' => $expiredHours,
                    'expiredDays' => $expiredDays,
                    'expiredDateTime' => $expiredDateTime,
                ]);
            }
        }

        $token = \Str::random(32);
        $expiredHours = null;
        $expiredDays = null;
        $expiredDateTime = null;
        if ($dtoWordBody->expiredTime) {
            $now = time();
            $time = $dtoWordBody->expiredTime * 3600;
            $expiredTime = $now + $time;

            $dt = Carbon::parse($expiredTime);

            $expiredHours = $dtoWordBody->expiredTime;
            $expiredDays = $dt->diffInDays(Carbon::now());
            $expiredDateTime = date('Y-m-d H:i:s', $expiredTime);
        }

        $condition = [
            'platform_id' => $dtoWordBody->platformId,
            'version' => $dtoWordBody->version,
            'app_id' => $dtoWordBody->appId,
            'account_id' => $accountId,
            'account_token' => $dtoWordBody->aidToken,
            'user_id' => $userId,
            'user_token' => $token,
            'expired_at' => $expiredDateTime,
        ];

        $tokenModel = SessionToken::create($condition);

        return $this->success([
            'aid' => $dtoWordBody->aid,
            'aidToken' => $dtoWordBody->aidToken,
            'uid' => $dtoWordBody->uid,
            'uidToken' => $token,
            'uidTokenId' => $tokenModel->id,
            'expiredHours' => $expiredHours,
            'expiredDays' => $expiredDays,
            'expiredDateTime' => $expiredDateTime,
        ]);
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function verifyUserToken($wordBody)
    {
        $dtoWordBody = new VerifyUserTokenDTO($wordBody);

        $verifyAccountToken = \FresnsCmdWord::plugin()->verifyAccountToken($wordBody);

        if ($verifyAccountToken->isErrorResponse()) {
            return $verifyAccountToken->errorResponse();
        }

        $langTag = \request()->header('langTag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);
        $uidToken = $dtoWordBody->uidToken;

        $cacheKey = "fresns_token_user_{$userId}_{$uidToken}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        // Cache::tags(['fresnsSystems'])
        $userToken = Cache::remember($cacheKey, $cacheTime, function () use ($accountId, $userId, $uidToken) {
            return SessionToken::where('account_id', $accountId)
                ->where('user_id', $userId)
                ->where('user_token', $uidToken)
                ->first();
        });

        if (is_null($userToken)) {
            Cache::forget($cacheKey);

            return $this->failure(
                31603,
                ConfigUtility::getCodeMessage(31603, 'Fresns', $langTag)
            );
        }

        if ($userToken->platform_id != $dtoWordBody->platformId) {
            return $this->failure(
                31103,
                ConfigUtility::getCodeMessage(31103, 'Fresns', $langTag)
            );
        }

        if ($userToken->expired_at && $userToken->expired_at < now()) {
            return $this->failure(
                31504,
                ConfigUtility::getCodeMessage(31504, 'Fresns', $langTag)
            );
        }

        return $this->success();
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

        $user = UserModel::where('uid', $dtoWordBody->uid)->first();

        $user->delete();

        Conversation::where('a_user_id', $user->id)->update(['a_is_deactivate' => 0]);
        Conversation::where('b_user_id', $user->id)->update(['b_is_deactivate' => 0]);

        return $this->success();
    }
}
