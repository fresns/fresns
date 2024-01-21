<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Controllers;

use App\Exceptions\ApiException;
use App\Fresns\Api\Http\DTO\AccountLoginDTO;
use App\Fresns\Api\Http\DTO\AccountWalletLogsDTO;
use App\Helpers\CacheHelper;
use App\Helpers\DateHelper;
use App\Models\Account;
use App\Models\AccountWalletLog;
use App\Models\AppUsage;
use App\Models\SessionLog;
use App\Models\SessionToken;
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

        $loginToken = SessionLog::where('type', SessionLog::TYPE_ACCOUNT_LOGIN)
            ->where('platform_id', $platformId)
            ->where('version', $version)
            ->where('app_id', $appId)
            ->where('action_result', SessionLog::STATE_SUCCESS)
            ->where('login_token', $dtoRequest->loginToken)
            ->first();

        if (! $loginToken) {
            throw new ApiException(31506);
        }

        if (empty($loginToken->account_id)) {
            throw new ApiException(31502);
        }

        $timeDifference = time() - strtotime($loginToken->created_at);

        if ($loginToken->action_id || $timeDifference > 300) {
            throw new ApiException(31507);
        }

        $account = Account::where('id', $loginToken->account_id)->first();

        if (! $account) {
            throw new ApiException(31502);
        }

        if (! $account->is_enabled) {
            throw new ApiException(34307);
        }

        // create token
        $wordBody = [
            'platformId' => $this->platformId(),
            'version' => $this->version(),
            'appId' => $this->appId(),
            'aid' => $account->aid,
            'deviceToken' => $dtoRequest->deviceToken,
            'expiredTime' => null,
        ];
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->createAccountToken($wordBody);

        if ($fresnsResp->isErrorResponse()) {
            return $fresnsResp->errorResponse();
        }

        $loginToken->update([
            'action_id' => $fresnsResp->getData('aidTokenId'),
        ]);

        $data = [
            'authToken' => [
                'aid' => $fresnsResp->getData('aid'),
                'token' => $fresnsResp->getData('aidToken'),
                'expiredHours' => $fresnsResp->getData('expiredHours'),
                'expiredDays' => $fresnsResp->getData('expiredDays'),
                'expiredDateTime' => $fresnsResp->getData('expiredDateTime'),
            ],
            'items' => [
                'walletRecharges' => ExtendUtility::getAppExtendsByEveryone(AppUsage::TYPE_WALLET_RECHARGE, null, null, $this->langTag()),
                'walletWithdraws' => ExtendUtility::getAppExtendsByEveryone(AppUsage::TYPE_WALLET_WITHDRAW, null, null, $this->langTag()),
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
        $authUser = $this->user();
        $aidToken = \request()->header('X-Fresns-Aid-Token');

        if (empty($authAccount)) {
            throw new ApiException(31502);
        }

        if (empty($aidToken)) {
            throw new ApiException(31505);
        }

        SessionToken::where('account_id', $authAccount->id)->where('account_token', $aidToken)->delete();

        CacheHelper::forgetFresnsAccount($authAccount->aid);
        CacheHelper::forgetFresnsUser($authUser?->id, $authUser?->uid);

        return $this->success();
    }

    // detail
    public function detail()
    {
        $authAccount = $this->account();

        if (empty($authAccount)) {
            throw new ApiException(31502);
        }

        $data = [
            'items' => [
                'walletRecharges' => ExtendUtility::getAppExtendsByEveryone(AppUsage::TYPE_WALLET_RECHARGE, null, null, $this->langTag()),
                'walletWithdraws' => ExtendUtility::getAppExtendsByEveryone(AppUsage::TYPE_WALLET_WITHDRAW, null, null, $this->langTag()),
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
            $item['user'] = $log?->user ? DetailUtility::userDetail($log?->user, $langTag, $timezone, $authUser?->id, $userOptions) : null;
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
