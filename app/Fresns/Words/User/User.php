<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User;

use App\Fresns\Words\User\DTO\CreateUserDTO;
use App\Fresns\Words\User\DTO\CreateUserTokenDTO;
use App\Fresns\Words\User\DTO\LogicalDeletionUserDTO;
use App\Fresns\Words\User\DTO\SetUserExpiryDatetimeDTO;
use App\Fresns\Words\User\DTO\SetUserExtcreditsDTO;
use App\Fresns\Words\User\DTO\SetUserGroupExpiryDatetimeDTO;
use App\Fresns\Words\User\DTO\VerifyUserDTO;
use App\Fresns\Words\User\DTO\VerifyUserTokenDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Account;
use App\Models\File;
use App\Models\SessionToken;
use App\Models\User as UserModel;
use App\Models\UserExtcreditsLog;
use App\Models\UserFollow;
use App\Models\UserRole;
use App\Models\UserStat;
use App\Utilities\ConfigUtility;
use App\Utilities\InteractionUtility;
use Carbon\Carbon;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class User
{
    use CmdWordResponseTrait;

    // createUser
    public function createUser($wordBody)
    {
        $dtoWordBody = new CreateUserDTO($wordBody);

        if ($dtoWordBody->aidToken) {
            $verifyAccountToken = \FresnsCmdWord::plugin()->verifyAccountToken($wordBody);

            if ($verifyAccountToken->isErrorResponse()) {
                return $verifyAccountToken->errorResponse();
            }
        }

        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());

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
            'nickname' => $dtoWordBody->nickname ?? Str::random(8),
            'password' => isset($dtoWordBody->password) ? Hash::make($dtoWordBody->password) : null,
            'avatar_file_id' => isset($dtoWordBody->avatarFid) ? File::where('fid', $dtoWordBody->avatarFid)->value('id') : null,
            'avatar_file_url' => $dtoWordBody->avatarUrl ?? null,
            'banner_file_id' => isset($dtoWordBody->bannerFid) ? File::where('fid', $dtoWordBody->bannerFid)->value('id') : null,
            'banner_file_url' => $dtoWordBody->bannerUrl ?? null,
            'gender' => $dtoWordBody->gender ?? UserModel::GENDER_UNKNOWN,
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

    // verifyUser
    public function verifyUser($wordBody)
    {
        $dtoWordBody = new VerifyUserDTO($wordBody);

        $verifyAccountToken = \FresnsCmdWord::plugin()->verifyAccountToken($wordBody);

        if ($verifyAccountToken->isErrorResponse()) {
            return $verifyAccountToken->errorResponse();
        }

        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());

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

        if ($user->password) {
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

    // createUserToken
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

    // verifyUserToken
    public function verifyUserToken($wordBody)
    {
        $dtoWordBody = new VerifyUserTokenDTO($wordBody);

        $verifyAccountToken = \FresnsCmdWord::plugin()->verifyAccountToken($wordBody);

        if ($verifyAccountToken->isErrorResponse()) {
            return $verifyAccountToken->errorResponse();
        }

        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);
        $uidToken = $dtoWordBody->uidToken;

        $cacheKey = "fresns_token_user_{$userId}_{$uidToken}";
        $cacheTag = 'fresnsUsers';

        // is known to be empty
        $isKnownEmpty = CacheHelper::isKnownEmpty($cacheKey);
        if ($isKnownEmpty) {
            return $this->failure(
                31505,
                ConfigUtility::getCodeMessage(31505, 'Fresns', $langTag)
            );
        }

        $userToken = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($userToken)) {
            $userToken = SessionToken::where('account_id', $accountId)
                ->where('user_id', $userId)
                ->where('user_token', $uidToken)
                ->first();

            if (empty($userToken)) {
                return $this->failure(
                    31603,
                    ConfigUtility::getCodeMessage(31603, 'Fresns', $langTag)
                );
            }

            CacheHelper::put($userToken, $cacheKey, $cacheTag);
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

    // logicalDeletionUser
    public function logicalDeletionUser($wordBody)
    {
        $dtoWordBody = new LogicalDeletionUserDTO($wordBody);

        $user = UserModel::where('uid', $dtoWordBody->uid)->first();

        $user->delete();

        return $this->success();
    }

    // setUserExtcredits
    public function setUserExtcredits($wordBody)
    {
        $dtoWordBody = new SetUserExtcreditsDTO($wordBody);
        $langTag = \request()->header('X-Fresns-Client-Lang-Tag', ConfigHelper::fresnsConfigDefaultLangTag());

        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);
        $userStat = UserStat::where('user_id', $userId)->first();
        $extcreditsIdName = 'extcredits'.$dtoWordBody->extcreditsId;

        $openingAmount = $userStat->$extcreditsIdName;

        $checkClosingAmount = static::checkClosingAmount($userStat, $dtoWordBody->extcreditsId);
        if (! $checkClosingAmount) {
            return $this->failure(21006, 'Error closing amount');
        }

        $amount = $dtoWordBody->amount ?? 1;

        switch ($dtoWordBody->operation) {
            case 'increment':
                $type = 1;
                $operationStat = $userStat->increment($extcreditsIdName, $amount);
                $closingAmount = $openingAmount + $amount;
                break;

            case 'decrement':
                if ($openingAmount == 0) {
                    return $this->failure(21006, 'User value is 0 and cannot be decrement');
                }

                $type = 2;
                $operationStat = $userStat->decrement($extcreditsIdName, $amount);
                $closingAmount = $openingAmount - $amount;
                break;
        }

        if (! $operationStat) {
            return $this->failure(
                21006,
                ConfigUtility::getCodeMessage(21006, 'Fresns', $langTag)
            );
        }

        $log = [
            'user_id' => $userId,
            'extcredits_id' => $dtoWordBody->extcreditsId,
            'type' => $type,
            'amount' => $amount,
            'opening_amount' => $openingAmount,
            'closing_amount' => $closingAmount,
            'plugin_fskey' => $dtoWordBody->fskey,
            'remark' => $dtoWordBody->remark,
        ];

        UserExtcreditsLog::create($log);

        CacheHelper::forgetFresnsUser($userId, $dtoWordBody->uid);

        return $this->success();
    }

    // check closing amount
    public static function checkClosingAmount(UserStat $userStat, int $extcreditsId): bool
    {
        $log = UserExtcreditsLog::where('user_id', $userStat->user_id)->where('extcredits_id', $extcreditsId)->latest()->first();

        $columnName = 'extcredits'.$extcreditsId;

        $amount = $userStat->$columnName;
        $closingAmount = $log?->closing_amount ?? 0;

        return $amount == $closingAmount;
    }

    // setUserExpiryDatetime
    public function setUserExpiryDatetime($wordBody)
    {
        $dtoWordBody = new SetUserExpiryDatetimeDTO($wordBody);

        $user = UserModel::where('uid', $dtoWordBody->uid)->first();

        $user->update([
            'expired_at' => $dtoWordBody->clearDatetime ? null : $dtoWordBody->datetime,
        ]);

        CacheHelper::forgetFresnsUser($user->id, $user->uid);

        return $this->success();
    }

    // setUserGroupExpiryDatetime
    public function setUserGroupExpiryDatetime($wordBody)
    {
        $dtoWordBody = new SetUserGroupExpiryDatetimeDTO($wordBody);

        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($dtoWordBody->uid);
        $groupId = PrimaryHelper::fresnsGroupIdByGid($dtoWordBody->gid);

        $userFollow = UserFollow::where('user_id', $userId)->type(UserFollow::TYPE_GROUP)->where('follow_id', $groupId)->first();

        if (empty($userFollow)) {
            InteractionUtility::markUserFollow($userId, UserFollow::TYPE_GROUP, $groupId);

            $userFollow = UserFollow::where('user_id', $userId)->type(UserFollow::TYPE_GROUP)->where('follow_id', $groupId)->first();
        }

        $userFollow->update([
            'expired_at' => $dtoWordBody->clearDatetime ? null : $dtoWordBody->datetime,
        ]);

        $cacheKey = "fresns_model_follow_group_{$groupId}_by_{$userId}";
        $cacheTags = ['fresnsModels', 'fresnsGroups'];
        CacheHelper::forgetFresnsKey($cacheKey, $cacheTags);

        return $this->success();
    }
}
