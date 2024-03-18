<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\User;

use App\Fresns\Words\User\DTO\ClearUserAllBadgesDTO;
use App\Fresns\Words\User\DTO\ClearUserBadgeDTO;
use App\Fresns\Words\User\DTO\CreateUserDTO;
use App\Fresns\Words\User\DTO\CreateUserTokenDTO;
use App\Fresns\Words\User\DTO\DeletionUserDTO;
use App\Fresns\Words\User\DTO\GetUserDeviceTokenDTO;
use App\Fresns\Words\User\DTO\SetUserBadgeDTO;
use App\Fresns\Words\User\DTO\SetUserExpiryDatetimeDTO;
use App\Fresns\Words\User\DTO\SetUserExtcreditsDTO;
use App\Fresns\Words\User\DTO\SetUserGroupExpiryDatetimeDTO;
use App\Fresns\Words\User\DTO\VerifyUserDTO;
use App\Fresns\Words\User\DTO\VerifyUserTokenDTO;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Models\Account;
use App\Models\AppBadge;
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
                return $verifyAccountToken->getErrorResponse();
            }
        }

        $account = Account::where('aid', $dtoWordBody->aid)->first();
        if (empty($account)) {
            return $this->failure(
                34301,
                ConfigUtility::getCodeMessage(34301)
            );
        }

        $userArr = [
            'account_id' => $account->id,
            'username' => $dtoWordBody->username,
            'nickname' => $dtoWordBody->nickname ?? Str::random(8),
            'pin' => isset($dtoWordBody->pin) ? Hash::make($dtoWordBody->pin) : null,
            'avatar_file_id' => isset($dtoWordBody->avatarFid) ? File::where('fid', $dtoWordBody->avatarFid)->value('id') : null,
            'avatar_file_url' => $dtoWordBody->avatarUrl ?? null,
            'banner_file_id' => isset($dtoWordBody->bannerFid) ? File::where('fid', $dtoWordBody->bannerFid)->value('id') : null,
            'banner_file_url' => $dtoWordBody->bannerUrl ?? null,
            'gender' => $dtoWordBody->gender ?? UserModel::GENDER_UNKNOWN,
            'genderPronoun' => $dtoWordBody->genderPronoun ?? null,
            'genderCustom' => $dtoWordBody->genderCustom ?? null,
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
            return $verifyAccountToken->getErrorResponse();
        }

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);
        $user = UserModel::where('uid', $dtoWordBody->uid)->first();

        if (empty($user) || $user?->account_id != $accountId) {
            return $this->failure(
                35201,
                ConfigUtility::getCodeMessage(35201)
            );
        }

        $loginErrorCount = ConfigUtility::getLoginErrorCount($user->account->id, $user->id);

        if ($loginErrorCount >= 5) {
            return $this->failure(
                34306,
                ConfigUtility::getCodeMessage(34306),
            );
        }

        if ($user->pin) {
            if (empty($dtoWordBody->pin)) {
                return $this->failure(
                    34111,
                    ConfigUtility::getCodeMessage(34111),
                );
            }

            if (! Hash::check($dtoWordBody->pin, $user->pin)) {
                return $this->failure(
                    35204,
                    ConfigUtility::getCodeMessage(35204),
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
            return $verifyAccountToken->getErrorResponse();
        }

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);
        $user = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody->uid);

        if (empty($user) || $user->account_id != $accountId) {
            return $this->failure(
                31602,
                ConfigUtility::getCodeMessage(31602)
            );
        }

        $userTokenModel = SessionToken::where('platform_id', $dtoWordBody->platformId)
            ->where('app_id', $dtoWordBody->appId)
            ->where('account_id', $accountId)
            ->where('account_token', $dtoWordBody->aidToken)
            ->where('user_id', $user->id)
            ->first();

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

        if ($userTokenModel) {
            $userTokenModel->update([
                'version' => $dtoWordBody->version,
                'expired_at' => $expiredDateTime,
            ]);

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

        $token = Str::random(64);

        $condition = [
            'platform_id' => $dtoWordBody->platformId,
            'version' => $dtoWordBody->version,
            'app_id' => $dtoWordBody->appId,
            'account_id' => $accountId,
            'account_token' => $dtoWordBody->aidToken,
            'user_id' => $user->id,
            'user_token' => $token,
            'device_token' => $dtoWordBody->deviceToken ?? null,
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

        // $verifyAccountToken = \FresnsCmdWord::plugin()->verifyAccountToken($wordBody);

        // if ($verifyAccountToken->isErrorResponse()) {
        //     return $verifyAccountToken->getErrorResponse();
        // }

        $accountId = PrimaryHelper::fresnsPrimaryId('account', $dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $uidToken = $dtoWordBody->uidToken;

        $cacheKey = "fresns_token_user_{$userId}_{$uidToken}";
        $cacheTag = 'fresnsUsers';

        $userToken = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($userToken)) {
            $userToken = SessionToken::where('account_id', $accountId)
                ->where('user_id', $userId)
                ->where('user_token', $uidToken)
                ->first();

            if (empty($userToken)) {
                return $this->failure(
                    31603,
                    ConfigUtility::getCodeMessage(31603)
                );
            }

            CacheHelper::put($userToken, $cacheKey, $cacheTag);
        }

        if ($userToken->platform_id != $dtoWordBody->platformId) {
            return $this->failure(
                31103,
                ConfigUtility::getCodeMessage(31103)
            );
        }

        if ($userToken->expired_at && $userToken->expired_at < now()) {
            return $this->failure(
                31504,
                ConfigUtility::getCodeMessage(31504)
            );
        }

        return $this->success();
    }

    // getUserDeviceToken
    public function getUserDeviceToken($wordBody)
    {
        $dtoWordBody = new GetUserDeviceTokenDTO($wordBody);

        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);

        $tokenQuery = SessionToken::where('user_id', $userId)->whereNotNull('device_token');

        $tokenQuery->when($dtoWordBody->platformId, function ($query, $value) {
            $query->where('platform_id', $value);
        });

        $tokens = $tokenQuery->latest()->get();

        $tokenArr = [];
        foreach ($tokens as $token) {
            $item['platformId'] = $token->platform_id;
            $item['deviceToken'] = $token->device_token;
            $item['datetime'] = $token->created_at;

            $tokenArr[] = $item;
        }

        return $this->success($tokenArr);
    }

    // logicalDeletionUser
    public function logicalDeletionUser($wordBody)
    {
        $dtoWordBody = new DeletionUserDTO($wordBody);

        $user = UserModel::where('uid', $dtoWordBody->uid)->first();

        $user->delete();

        return $this->success();
    }

    // physicalDeletionUser
    public function physicalDeletionUser($wordBody)
    {
        $dtoWordBody = new DeletionUserDTO($wordBody);

        if (config('queue.default') == 'sync') {
            return $this->failure(21011);
        }

        // waiting for development

        return $this->failure(21010);
    }

    // setUserExtcredits
    public function setUserExtcredits($wordBody)
    {
        $dtoWordBody = new SetUserExtcreditsDTO($wordBody);

        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $userStat = UserStat::where('user_id', $userId)->first();
        $extcreditsId = 'extcredits'.$dtoWordBody->extcreditsId;

        $openingAmount = $userStat->$extcreditsId;

        $checkClosingAmount = static::checkClosingAmount($userStat, $dtoWordBody->extcreditsId);
        if (! $checkClosingAmount) {
            return $this->failure(21006, 'Error closing amount');
        }

        $amount = $dtoWordBody->amount ?? 1;

        switch ($dtoWordBody->operation) {
            case 'increment':
                $type = 1;
                $operationStat = $userStat->increment($extcreditsId, $amount);
                $closingAmount = $openingAmount + $amount;
                break;

            case 'decrement':
                if ($openingAmount == 0) {
                    return $this->failure(21006, 'User value is 0 and cannot be decrement');
                }

                if ($openingAmount < $amount) {
                    return $this->failure(21006, 'The user current value is less than the decremented value and cannot be manipulated.');
                }

                $type = 2;
                $operationStat = $userStat->decrement($extcreditsId, $amount);
                $closingAmount = $openingAmount - $amount;
                break;
        }

        if (! $operationStat) {
            return $this->failure(
                21006,
                ConfigUtility::getCodeMessage(21006)
            );
        }

        $log = [
            'user_id' => $userId,
            'extcredits_id' => $dtoWordBody->extcreditsId,
            'type' => $type,
            'amount' => $amount,
            'opening_amount' => $openingAmount,
            'closing_amount' => $closingAmount,
            'app_fskey' => $dtoWordBody->fskey,
            'remark' => $dtoWordBody->remark,
        ];

        UserExtcreditsLog::create($log);

        CacheHelper::forgetFresnsUser($userId, $dtoWordBody->uid);

        return $this->success();
    }

    // check closing amount
    public static function checkClosingAmount(UserStat $userStat, int $extcreditsId): bool
    {
        $log = UserExtcreditsLog::where('user_id', $userStat->user_id)->where('extcredits_id', $extcreditsId)->latest('id')->first();

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

        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $groupId = PrimaryHelper::fresnsPrimaryId('group', $dtoWordBody->gid);

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

    // setUserBadge
    public function setUserBadge($wordBody)
    {
        $dtoWordBody = new SetUserBadgeDTO($wordBody);

        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $fskey = $dtoWordBody->fskey;

        $cacheKey = "fresns_plugin_{$fskey}_badge_{$userId}";
        $cacheTag = 'fresnsUsers';

        $userBadge = AppBadge::where('user_id', $userId)->where('app_fskey', $fskey)->first();

        if (! $userBadge) {
            $badge = [
                'app_fskey' => $fskey,
                'user_id' => $userId,
                'display_type' => $dtoWordBody->type,
                'value_number' => $dtoWordBody->badgeNumber,
                'value_text' => $dtoWordBody->badgeText,
            ];

            AppBadge::create($badge);

            CacheHelper::forgetFresnsKey($cacheKey, $cacheTag);

            return $this->success();
        }

        $badgeNumber = $dtoWordBody->badgeNumber;
        if ($dtoWordBody->type == AppBadge::TYPE_NUMBER) {
            $badgeNumber = $userBadge->value_number + $dtoWordBody->badgeNumber;
        }

        $userBadge->update([
            'display_type' => $dtoWordBody->type,
            'value_number' => $badgeNumber,
            'value_text' => $dtoWordBody->badgeText,
        ]);

        CacheHelper::forgetFresnsKey($cacheKey, $cacheTag);

        return $this->success();
    }

    // clearUserBadge
    public function clearUserBadge($wordBody)
    {
        $dtoWordBody = new ClearUserBadgeDTO($wordBody);

        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $fskey = $dtoWordBody->fskey;

        AppBadge::where('user_id', $userId)->where('app_fskey', $fskey)->forceDelete();

        $cacheKey = "fresns_plugin_{$fskey}_badge_{$userId}";
        $cacheTag = 'fresnsUsers';

        CacheHelper::forgetFresnsKey($cacheKey, $cacheTag);

        return $this->success();
    }

    // clearUserAllBadges
    public function clearUserAllBadges($wordBody)
    {
        $dtoWordBody = new ClearUserAllBadgesDTO($wordBody);

        $userId = PrimaryHelper::fresnsPrimaryId('user', $dtoWordBody->uid);
        $cacheTag = 'fresnsUsers';

        $userBadges = AppBadge::where('user_id', $userId)->get();

        foreach ($userBadges as $badge) {
            $fskey = $badge->app_fskey;

            $badge->forceDelete();

            $cacheKey = "fresns_plugin_{$fskey}_badge_{$userId}";
            CacheHelper::forgetFresnsKey($cacheKey, $cacheTag);
        }

        return $this->success();
    }
}
