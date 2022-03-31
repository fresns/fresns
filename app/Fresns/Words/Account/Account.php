<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account;

use App\Fresns\Words\Account\DTO\AddAccountDTO;
use App\Fresns\Words\Account\DTO\CreateSessionTokenDTO;
use App\Fresns\Words\Account\DTO\GetAccountDetailDTO;
use App\Fresns\Words\Account\DTO\LogicalDeletionAccountDTO;
use App\Fresns\Words\Account\DTO\VerifyAccountDTO;
use App\Fresns\Words\Account\DTO\VerifySessionTokenDTO;
use App\Fresns\Words\Service\AccountService;
use App\Helpers\ConfigHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\UserHelper;
use App\Models\Account as AccountModel;
use App\Models\AccountConnect;
use App\Models\AccountWallet;
use App\Models\SessionToken;
use App\Models\User;
use App\Models\VerifyCode;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;
use Illuminate\Support\Facades\DB;

class Account
{
    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function addAccount($wordBody)
    {
        $dtoWordBody = new AddAccountDTO($wordBody);
        $connectInfoArr = [];
        // Whether the same token exists
        if (isset($dtoWordBody->connectInfo)) {
            $connectInfoArr = json_decode($dtoWordBody->connectInfo, true);
            $connectTokenArr = [];
            foreach ($connectInfoArr as $v) {
                $connectTokenArr[] = $v['connectToken'];
            }
            $count = AccountConnect::whereIn('connect_token', $connectTokenArr)->count();
            if ($count > 0) {
                ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_DATA_ERROR)::throw();
            }
        }
        $inputArr = [];

        $inputArr = match ($dtoWordBody->type) {
            1 => ['email' => $dtoWordBody->account],
            2 => [
                'country_code' => $dtoWordBody->countryCode,
                'pure_phone' => $dtoWordBody->account,
                'phone' => $dtoWordBody->countryCode.$dtoWordBody->account,
            ],
            default => [],
        };
        $inputArr['aid'] = \Str::random(12);
        $inputArr['last_login_at'] = date('Y-m-d H:i:s');
        if ($dtoWordBody->password) {
            $inputArr['password'] = password_hash($dtoWordBody->password, PASSWORD_BCRYPT);
        }
        $accountId = AccountModel::insertGetId($inputArr);
        // Account Wallet Table
        $accountWalletsInput = [
            'account_id' => $accountId,
            'balance' => 0,
        ];
        AccountWallet::insert($accountWalletsInput);

        ConfigHelper::fresnsCountAdd('accounts_count');
        // If the connectInfo parameter is passed, add it to the user_connects table
        if ($connectInfoArr) {
            $itemArr = [];
            foreach ($connectInfoArr as $info) {
                $item = [];
                $item['account_id'] = $accountId;
                $item['connect_id'] = $info['connectId'];
                $item['connect_token'] = $info['connectToken'];
                $item['connect_name'] = $info['connectName'];
                $item['connect_nickname'] = $info['connectNickname'];
                $item['connect_avatar'] = $info['connectAvatar'];
                $item['plugin_unikey'] = 'fresns_cmd_user_register';
                $itemArr[] = $item;
            }

            AccountConnect::insert($itemArr);
        }

        return ['data'=>['aid'=>$inputArr['aid'], 'type'=>$dtoWordBody->type], 'message'=>'success', 'code'=>0];
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function verifyAccount($wordBody)
    {
        $dtoWordBody = new VerifyAccountDTO($wordBody);
        $where = [
            'type' => $dtoWordBody->type,
            'account' => $dtoWordBody->type == 1 ? $dtoWordBody->type : $dtoWordBody->countryCode.$dtoWordBody->account,
            'code' => $dtoWordBody->verifyCode,
            'is_enable' => 1,
        ];
        $verifyInfo = VerifyCode::where($where)->where('expired_at', '>', date('Y-m-d H:i:s'))->first();
        if ($verifyInfo) {
            VerifyCode::where('id', $verifyInfo['id'])->update(['is_enable' => 0]);

            return ['message' => 'success', 'code'=>0];
        } else {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_DATA_ERROR)::throw();
        }
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function getAccountDetail($wordBody)
    {
        $dtoWordBody = new GetAccountDetailDTO($wordBody);
        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        if (empty($dtoWordBody->langTag)) {
            $dtoWordBody->langTag = ConfigHelper::fresnsConfigByItemKey('default_language');
        }
        if (empty($dtoWordBody->timezone)) {
            $dtoWordBody->timezone = ConfigHelper::fresnsConfigByItemKey('default_timezone');
        }
        $service = new AccountService();
        $data = $service->getAccountDetail($accountId, $dtoWordBody->langTag, $dtoWordBody->timezone);

        return ['message' => 'success', 'code' => 0, 'data' => $data];
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function createSessionToken($wordBody)
    {
        $dtoWordBody = new CreateSessionTokenDTO($wordBody);
        if ($dtoWordBody->aid) {
            $accountId = AccountModel::where('id', '=', $dtoWordBody->aid)->value('id');
        }
        if ($dtoWordBody->uid) {
            $userId = User::where('uid', '=', $dtoWordBody->uid)->value('id');
        }
        $condition = [
            'account_id' => $accountId,
            'user_id' => $userId ?? null,
            'platform_id' => $dtoWordBody->platform,
        ];
        $tokenCount = SessionToken::where($condition)->first();
        if ($tokenCount) {
            SessionToken::where($condition)->delete();
        }
        $token = \Str::random(32);
        $condition['token'] = $token;
        $condition['expired_at'] = $dtoWordBody->expiredTime ?? null;
        SessionToken::insert($condition);

        return ['code' => 0, 'message' => 'success', 'data' => ['token' => $token]];
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function verifySessionToken($wordBody)
    {
        $dtoWordBody = new VerifySessionTokenDTO($wordBody);

        if (!empty($dtoWordBody->uid)) {
            $userAffiliation = UserHelper::fresnsUserAffiliation($dtoWordBody->uid, $dtoWordBody->aid);
            if ($userAffiliation == false) {
                return ['message'=>'Current user not exist or not belong to the current user', 'code'=>30300, 'data'=>[]];
            }
        }

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $userId = PrimaryHelper::fresnsUserIdByUid($dtoWordBody->uid);

        $condition = [
            'platform_id' => $dtoWordBody->platform,
            'account_id' => $accountId,
            'user_id' => $userId ?? null,
        ];
        $session = SessionToken::where($condition)->first();

        if ($session->token != $dtoWordBody->token || ($session->expired_at < date('Y-m-d H:i:s', time()))) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_DATA_ERROR)::throw();
        }

        return ['message'=>'success', 'code'=>0];
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function logicalDeletionAccount($wordBody)
    {
        $dtoWordBody = new LogicalDeletionAccountDTO($wordBody);

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid);
        $dateTime = 'deleted#'.date('YmdHis').'#';
        AccountModel::where('id', $accountId)->update([
            'phone' => DB::raw("concat('$dateTime','phone')"),
            'email' => DB::raw("concat('$dateTime','email')"),
            'deleted_at' => now()]
        );
        AccountConnect::where('account_id', $accountId)->forceDelete();

        return ['code' => 0, 'message' => 'success', 'data' => []];
    }
}
