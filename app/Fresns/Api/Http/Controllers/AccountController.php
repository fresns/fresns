<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Fresns\Api\Exceptions\ResponseException;
use App\Fresns\Api\Http\DTO\AccountLoginDTO;
use App\Fresns\Api\Http\DTO\AccountWalletLogsDTO;
use App\Helpers\CacheHelper;
use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\AccountWalletLog;
use App\Models\AppUsage;
use App\Models\SessionLog;
use App\Models\SessionToken;
use App\Models\User;
use App\Utilities\DetailUtility;
use App\Utilities\ExtendUtility;
use App\Utilities\SubscribeUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    // login
    public function login(Request $request)
    {
        $dtoRequest = new AccountLoginDTO($request->all());

        $platformId = $this->platformId();
        $version = $this->version();
        $appId = $this->appId();

        $loginTokenInfo = SessionLog::whereIn('type', [
            SessionLog::TYPE_ACCOUNT_REGISTER,
            SessionLog::TYPE_ACCOUNT_LOGIN,
            SessionLog::TYPE_USER_ADD,
            SessionLog::TYPE_USER_LOGIN,
        ])
            ->where('platform_id', $platformId)
            ->where('version', $version)
            ->where('app_id', $appId)
            ->where('action_state', SessionLog::STATE_SUCCESS)
            ->where('login_token', $dtoRequest->loginToken)
            ->first();

        if (! $loginTokenInfo) {
            throw new ResponseException(31506);
        }

        if (empty($loginTokenInfo->account_id)) {
            throw new ResponseException(31502);
        }

        if (empty($loginTokenInfo->user_id)) {
            throw new ResponseException(31602);
        }

        $checkTime = $loginTokenInfo->created_at->addMinutes(5);

        if ($loginTokenInfo->action_id || $checkTime->lt(now())) {
            throw new ResponseException(31507);
        }

        // account
        $account = Account::where('id', $loginTokenInfo->account_id)->first();

        if (! $account) {
            throw new ResponseException(31502);
        }

        if (! $account->is_enabled) {
            throw new ResponseException(34307);
        }

        // user
        $user = User::where('id', $loginTokenInfo->user_id)->first();

        if (! $user) {
            throw new ResponseException(31602);
        }

        if (! $user->is_enabled) {
            throw new ResponseException(35202);
        }

        // create account token
        $accountWordBody = [
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'aid' => $account->aid,
            'deviceToken' => $dtoRequest->deviceToken,
            'expiredTime' => null,
        ];
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->createAccountToken($accountWordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->getErrorResponse();
        }

        $loginTokenInfo->update([
            'action_id' => $fresnsResp->getData('aidTokenId'),
            'device_token' => $dtoRequest->deviceToken,
        ]);

        // create user token
        $userWordBody = [
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'aid' => $fresnsResp->getData('aid'),
            'aidToken' => $fresnsResp->getData('aidToken'),
            'uid' => $user->uid,
            'deviceToken' => $dtoRequest->deviceToken,
            'expiredTime' => null,
        ];
        $fresnsUserTokenResp = \FresnsCmdWord::plugin('Fresns')->createUserToken($userWordBody);

        if ($fresnsUserTokenResp->isErrorResponse()) {
            return $fresnsUserTokenResp->getErrorResponse();
        }

        $loginTokenInfo->update([
            'action_id' => $fresnsUserTokenResp->getData('aidTokenId'),
        ]);

        // login time
        $account->update([
            'last_login_at' => now(),
        ]);

        $user->update([
            'last_login_at' => now(),
        ]);

        // wallet
        $recharges = ExtendUtility::getAppExtendsByEveryone(AppUsage::TYPE_WALLET_RECHARGE, null, null, $this->langTag());
        $walletRecharges = array_map(function ($item) {
            unset($item['editorToolbar']);
            unset($item['editorNumber']);

            return $item;
        }, $recharges);

        $withdraws = ExtendUtility::getAppExtendsByEveryone(AppUsage::TYPE_WALLET_WITHDRAW, null, null, $this->langTag());
        $walletWithdraws = array_map(function ($item) {
            unset($item['editorToolbar']);
            unset($item['editorNumber']);

            return $item;
        }, $withdraws);

        // data
        $data = [
            'authToken' => [
                'aid' => $fresnsUserTokenResp->getData('aid'),
                'aidToken' => $fresnsUserTokenResp->getData('aidToken'),
                'uid' => $fresnsUserTokenResp->getData('uid'),
                'uidToken' => $fresnsUserTokenResp->getData('uidToken'),
                'expiredHours' => $fresnsUserTokenResp->getData('expiredHours'),
                'expiredDays' => $fresnsUserTokenResp->getData('expiredDays'),
                'expiredDateTime' => $fresnsUserTokenResp->getData('expiredDateTime'),
            ],
            'items' => [
                'walletRecharges' => $walletRecharges,
                'walletWithdraws' => $walletWithdraws,
            ],
            'detail' => DetailUtility::accountDetail($account, $this->langTag(), $this->timezone()),
        ];

        // notify subscribe
        SubscribeUtility::notifyAccountAndUserLogin($account->id, $data['authToken'], $data['detail']);

        return $this->success($data);
    }

    // logout
    public function logout()
    {
        $authAccount = $this->account();
        $authAccountToken = $this->accountToken();
        $authUser = $this->user();

        if (empty($authAccount)) {
            throw new ResponseException(31502);
        }

        if (empty($authAccountToken)) {
            throw new ResponseException(31505);
        }

        SessionToken::where('account_id', $authAccount->id)->where('account_token', $authAccountToken)->delete();

        CacheHelper::forgetFresnsAccount($authAccount->aid);
        CacheHelper::forgetFresnsUser($authUser?->id, $authUser?->uid);

        return $this->success();
    }

    // detail
    public function detail()
    {
        $authAccount = $this->account();

        if (empty($authAccount)) {
            throw new ResponseException(31502);
        }

        $recharges = ExtendUtility::getAppExtendsByEveryone(AppUsage::TYPE_WALLET_RECHARGE, null, null, $this->langTag());
        $walletRecharges = array_map(function ($item) {
            unset($item['editorToolbar']);
            unset($item['editorNumber']);

            return $item;
        }, $recharges);

        $withdraws = ExtendUtility::getAppExtendsByEveryone(AppUsage::TYPE_WALLET_WITHDRAW, null, null, $this->langTag());
        $walletWithdraws = array_map(function ($item) {
            unset($item['editorToolbar']);
            unset($item['editorNumber']);

            return $item;
        }, $withdraws);

        $data = [
            'items' => [
                'walletRecharges' => $walletRecharges,
                'walletWithdraws' => $walletWithdraws,
            ],
            'detail' => DetailUtility::accountDetail($authAccount, $this->langTag(), $this->timezone()),
        ];

        return $this->success($data);
    }

    // walletRecords
    public function walletRecords(Request $request)
    {
        $dtoRequest = new AccountWalletLogsDTO($request->all());

        $authAccount = $this->account();
        $authUser = $this->user();
        $langTag = $this->langTag();
        $timezone = $this->timezone();

        $walletLogQuery = AccountWalletLog::with(['user'])->where('account_id', $authAccount->id);

        $walletLogQuery->when($dtoRequest->type, function ($query, $value) {
            $typeArr = array_filter(explode(',', $value));

            $query->whereIn('type', $typeArr);
        });

        $walletLogQuery->when($dtoRequest->state, function ($query, $value) {
            $query->whereIn('state', $value);
        });

        $walletLogQuery->orderByDesc(DB::raw('COALESCE(success_at, created_at)'));

        $walletLogs = $walletLogQuery->paginate($dtoRequest->pageSize ?? 15);

        $userOptions = [
            'viewType' => 'list',
            'isLiveStats' => false,
            'filter' => [
                'type' => $dtoRequest->filterUserType,
                'keys' => $dtoRequest->filterUserKeys,
            ],
        ];

        $logList = [];
        foreach ($walletLogs as $log) {
            $datetime = $log->success_at ?? $log->created_at;

            $item['type'] = $log->type;
            $item['fskey'] = $log->app_fskey;
            $item['transactionId'] = $log->transaction_id;
            $item['transactionCode'] = $log->transaction_code;
            $item['amountTotal'] = $log->amount_total;
            $item['transactionAmount'] = $log->transaction_amount;
            $item['systemFee'] = $log->system_fee;
            $item['openingBalance'] = $log->opening_balance;
            $item['closingBalance'] = $log->closing_balance;
            $item['user'] = $log->user ? DetailUtility::userDetail($log->user, $langTag, $timezone, $authUser?->id, $userOptions) : null;
            $item['remark'] = $log->remark;
            $item['datetime'] = DateHelper::fresnsDateTimeByTimezone($datetime, $timezone, $langTag);
            $item['datetimeFormat'] = DateHelper::fresnsFormatDateTime($datetime, $timezone, $langTag);
            $item['timeAgo'] = DateHelper::fresnsHumanReadableTime($datetime, $langTag);
            $item['state'] = $log->state;

            $logList[] = $item;
        }

        return $this->fresnsPaginate($logList, $walletLogs->total(), $walletLogs->perPage());
    }
}
