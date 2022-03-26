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
use App\Models\AccountConnect;
use App\Models\AccountWallet;
use App\Models\SessionToken;
use App\Models\User;
use App\Models\VerifyCode;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;

class Account
{
    /**
     * @param $wordBody
     * @return array|string
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
        $accountId = \App\Models\Account::insertGetId($inputArr);
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
        $accountId = \App\Models\Account::where('aid', '=', $dtoWordBody->aid)->value('id');
        $langTag = request()->header('langTag');
        $service = new AccountService();
        $data = $service->getUserDetail($accountId, $langTag);

        return ['message'=>'success', 'code'=>0, 'data'=>$data];
    }

    /**
     * @param  CreateSessionTokenDTO  $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function createSessionToken($wordBody)
    {
        $dtoWordBody = new CreateSessionTokenDTO($wordBody);
        if ($dtoWordBody->aid) {
            $accountId = \App\Models\Account::where('id', '=', $dtoWordBody->aid)->value('id');
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
        $token = \Str::random(12);
        $condition['token'] = $token;
        $condition['expired_at'] = $dtoWordBody->expiredTime ?? null;
        SessionToken::insert($condition);

        return ['code' => 0, 'message' => 'success', 'data' => ['token' => $token]];
    }

    public function verifySessionToken($wordBody)
    {
        $dtoWordBody = new VerifySessionTokenDTO($wordBody);
        if ($dtoWordBody->aid) {
            $accountId = \App\Models\Account::where('aid', '=', $dtoWordBody->aid)->value('id');
        }
        if ($dtoWordBody->uid) {
            $userId = User::where('uid', '=', $dtoWordBody->uid)->value('id');
        }
        $condition = [
            'user_id' => $userId ?? null,
            'account_id' => $accountId,
            'platform_id' => $dtoWordBody->platform,
        ];
        $session = SessionToken::where($condition)->first();
        if (empty($session) || $session->token != $dtoWordBody->token || ($session->expired_at < date('Y-m-d H:i:s', time()))) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_DATA_ERROR)::throw();
        }

        return ['message'=>'success', 'code'=>0, 'data'=>[]];
    }

    public function logicalDeletionAccount($wordBody)
    {
        $dtoWordBody = new LogicalDeletionAccountDTO($wordBody);
        \App\Models\User::where('account_id', $dtoWordBody->accountId)->update(['deleted_at'=>now()]);

        return ['code'=>0, 'message'=>'success', 'data'=>[]];
    }
}
