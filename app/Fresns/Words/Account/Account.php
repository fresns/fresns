<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Account;

use App\Fresns\Words\Account\DTO\AddAccount;
use App\Fresns\Words\Account\DTO\CreateSessionToken;
use App\Fresns\Words\Account\DTO\GetAccountDetail;
use App\Fresns\Words\Account\DTO\LogicalDeletionAccount;
use App\Fresns\Words\Account\DTO\VerifyAccount;
use App\Fresns\Words\Account\DTO\VerifySessionToken;
use App\Fresns\Words\Service\AccountService;
use App\Helpers\ConfigHelper;
use App\Models\AccountConnect;
use App\Models\AccountWallet;
use App\Models\SessionLog;
use App\Models\SessionToken;
use App\Models\User;
use App\Models\VerifyCode;

class Account
{
    /**
     * @param  AddAccount  $wordBody
     * @return string | array
     */
    public function addAccount(AddAccount $wordBody)
    {
        $connectInfoArr = [];
        // Whether the same token exists
        if (isset($wordBody->connectInfo)) {
            $connectInfoArr = json_decode($wordBody->connectInfo, true);
            $connectTokenArr = [];
            foreach ($connectInfoArr as $v) {
                $connectTokenArr[] = $v['connectToken'];
            }
            $count = AccountConnect::whereIn('connect_token', $connectTokenArr)->count();
            if ($count > 0) {
                return 'error';
            }
        }
        $inputArr = [];

        $inputArr = match ($wordBody->type) {
            1 => ['email' => $wordBody->account],
            2 => [
                'country_code' => $wordBody->countryCode,
                'pure_phone' => $wordBody->account,
                'phone' => $wordBody->countryCode.$wordBody->account,
            ],
            default => [],
        };
        $inputArr['aid'] = \Str::random(12);
        $inputArr['last_login_at'] = date('Y-m-d H:i:s');
        // if ($wordBody->password) {
        //     $inputArr['password'] = StrHelper::createPassword($wordBody->password);
        // }
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
                $item['accountId'] = $accountId;
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

        return ['data'=>['aid'=>$aid, 'type'=>$wordBody->type], 'message'=>'success', 'code'=>200];
    }

    /**
     * @param  VerifyAccount  $wordBody
     */
    public function verifyAccount(VerifyAccount $wordBody)
    {
        $where = [
            'type' => $wordBody->type,
            'account' => $wordBody->type == 1 ? $wordBody->type : $wordBody->countryCode.$wordBody->account,
            'code' => $wordBody->verifyCode,
            'is_enable' => 1,
        ];
        $verifyInfo = VerifyCode::where($where)->where('expired_at', '>', date('Y-m-d H:i:s'))->first();
        if ($verifyInfo) {
            VerifyCode::where('id', $verifyInfo['id'])->update(['is_enable' => 0]);

            return ['message' => 'success', 'code'=>200];
        } else {
            return ['message' => 'error', 'code'=>200];
        }
    }

    public function getAccountDetail(GetAccountDetail $wordBody)
    {
        $aid = \App\Models\Account::where('aid', '=', $wordBody->aid)->value('id');
        $langTag = request()->header('langTag');
        $service = new AccountService();
        $data = $service->getUserDetail($aid, $langTag);

        return ['message'=>'success', 'code'=>200, 'data'=>$data];
    }

    public function createSessionToken(CreateSessionToken $wordBody)
    {
        if ($wordBody->aid) {
            $accountId = \App\Models\Account::where('id', '=', $wordBody->aid)->value('id');
        }
        if ($wordBody->uid) {
            $userId = User::where('uid', '=', $wordBody->uid)->value('id');
        }
        $condition = [
            'account_id' => $accountId,
            'user_id' => $userId ?? null,
            'platform_id' => $wordBody->platform,

        ];
        $tokenCount = SessionToken::where($condition)->first();
        if ($tokenCount) {
            SessionToken::where($condition)->delete();
        }
        $token = \Str::random(12);
        $condition['token'] = $token;
        $condition['expired_at'] = $wordBody->expiredTime ?? null;
        SessionToken::insert($condition);

        return ['code' => 200, 'message' => 'success', 'data' => ['token' => $token]];
    }

    public function verifySessionToken(VerifySessionToken $wordBody)
    {
        if ($wordBody->aid) {
            $accountId = \App\Models\Account::where('aid', '=', $wordBody->aid)->value('id');
        }
        if ($wordBody->uid) {
            $userId = User::where('uid', '=', $wordBody->uid)->value('id');
        }
        $condition = [
            'user_id' => $userId ?? null,
            'account_id' => $accountId,
            'platform_id' => $wordBody->platform,
        ];
        $session = SessionToken::where($condition)->first();
        if (empty($session) || $session->token != $wordBody->token || ($session->expired_at < date('Y-m-d H:i:s', time()))) {
            return ['message'=>'error', 'code'=>200, 'data'=>[]];
        }

        return ['message'=>'success', 'code'=>200, 'data'=>[]];
    }

    public function logicalDeletionAccount($wordBody)
    {
        $wordBody = new LogicalDeletionAccount($wordBody);
        \App\Models\User::where('account_id', $wordBody->accountId)->update(['deleted_at'=>now()]);

        return ['code'=>200, 'message'=>'success', 'data'=>[]];
    }
}
