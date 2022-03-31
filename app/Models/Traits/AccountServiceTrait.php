<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\PluginHelper;
use App\Helpers\StrHelper;
use App\Models\AccountConnect;
use App\Models\AccountWallet;

trait AccountServiceTrait
{
    public function getAccountInfo(string $timezone = '')
    {
        $accountData = $this;

        $proveSupportUnikey = ConfigHelper::fresnsConfigByItemKey('account_prove_service');

        $info['aid'] = $accountData['aid'];
        $info['countryCode'] = $accountData['country_code'];
        $info['purePhone'] = ! empty($accountData['pure_phone']) ? StrHelper::encryptNumber($accountData['pure_phone']) : null;
        $info['phone'] = ! empty($accountData['phone']) ? StrHelper::encryptNumber($accountData['phone']) : null;
        $info['email'] = ! empty($accountData['email']) ? StrHelper::encryptEmail($accountData['email']) : null;
        $info['hasPassword'] = ! empty($accountData['password']) ? true : false;
        $info['proveSupport'] = $proveSupportUrl ?? null;
        $info['proveSupport'] = ! empty($proveSupportUnikey) ? PluginHelper::fresnsPluginUrlByUnikey($proveSupportUnikey) : null;
        $info['proveStatus'] = $accountData['prove_verify'];
        $info['proveRealName'] = ! empty($accountData['prove_realname']) ? StrHelper::encryptName($accountData['prove_realname']) : null;
        $info['proveGender'] = $accountData['prove_gender'];
        $info['proveType'] = $accountData['prove_type'];
        $info['proveNumber'] = ! empty($accountData['prove_number']) ? StrHelper::encryptName($accountData['prove_number']) : null;
        $info['verifyType'] = $accountData['verify_type'];
        $info['verifyDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['verify_at'], $timezone);
        $info['registerDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['created_at'], $timezone);
        $info['status'] = $accountData['is_enable'];
        $info['deactivate'] = ! empty($accountData['deleted_at']) ? true : false;
        $info['deactivateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData['deleted_at'], $timezone);

        return $info;
    }

    public function getAccountConnects()
    {
        $accountData = $this;

        $connectsArr = AccountConnect::where('account_id', $accountData->id)->get()->toArray();

        $connectsItemArr = [];
        foreach ($connectsArr as $connect) {
            $item['id'] = $connect['connect_id'];
            $item['name'] = $connect['connect_name'];
            $item['nickname'] = $connect['connect_nickname'];
            $item['avatar'] = $connect['connect_avatar'];
            $item['status'] = $connect['is_enable'];
            $connectsItemArr[] = $item;
        }

        return $connectsItemArr;
    }

    public function getAccountWallet(string $langTag = '')
    {
        $accountData = $this;

        $walletData = AccountWallet::where('account_id', $accountData)->first();

        $wallet['status'] = $walletData['is_enable'] ?? null;
        $wallet['hasPassword'] = ! empty($walletData['password']) ? true : false;
        $wallet['currencyCode'] = ConfigHelper::fresnsConfigByItemKey('wallet_currency_code');
        $wallet['currencyName'] = ConfigHelper::fresnsConfigByItemKey('wallet_currency_name', $langTag);
        $wallet['currencyUnit'] = ConfigHelper::fresnsConfigByItemKey('wallet_currency_unit', $langTag);
        $wallet['currencyPrecision'] = ConfigHelper::fresnsConfigByItemKey('wallet_currency_precision');
        $wallet['balance'] = $walletData['balance'];
        $wallet['freezeAmount'] = $walletData['freeze_amount'];
        $wallet['bankName'] = $walletData['bank_name'];
        $wallet['swiftCode'] = $walletData['swift_code'];
        $wallet['bankAddress'] = $walletData['bank_address'];
        $wallet['bankAccount'] = ! empty($walletData['bank_account']) ? \Str::mask($walletData['bank_account'], '*', -8, 4) : null;
        $wallet['bankStatus'] = $walletData['bank_status'];

        return $wallet;
    }
}
