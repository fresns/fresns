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

trait AccountServiceTrait
{
    public function getAccountInfo(?string $langTag = null, ?string $timezone = null)
    {
        $accountData = $this;

        $verifySupportUrl = ConfigHelper::fresnsConfigByItemKey('account_real_name_service');

        $info['aid'] = $accountData->aid;
        $info['countryCode'] = $accountData->country_code;
        $info['purePhone'] = ! empty($accountData->pure_phone) ? StrHelper::maskNumber($accountData->pure_phone) : null;
        $info['phone'] = ! empty($accountData->phone) ? StrHelper::maskNumber($accountData->phone) : null;
        $info['email'] = ! empty($accountData->email) ? StrHelper::maskEmail($accountData->email) : null;
        $info['hasPassword'] = (bool) $accountData->password;
        $info['verifyStatus'] = (bool) $accountData->is_verify;
        $info['verifySupport'] = ! empty($verifySupportUrl) ? PluginHelper::fresnsPluginUrlByUnikey($verifySupportUrl) : null;
        $info['verifyRealName'] = ! empty($accountData->verify_real_name) ? StrHelper::maskName($accountData->verify_real_name) : null;
        $info['verifyGender'] = $accountData->verify_gender;
        $info['verifyCertType'] = $accountData->verify_cert_type;
        $info['verifyCertNumber'] = ! empty($accountData->verify_cert_number) ? StrHelper::maskName($accountData->verify_cert_number) : null;
        $info['verifyIdentityType'] = $accountData->verify_identity_type;
        $info['verifyDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData->verify_at, $timezone, $langTag);
        $info['registerDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData->created_at, $timezone, $langTag);
        $info['status'] = (bool) $accountData->is_enable;
        $info['waitDelete'] = (bool) $accountData->wait_delete;
        $info['waitDeleteDateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData->wait_delete_at, $timezone, $langTag);
        $info['deactivate'] = (bool) $accountData->deleted_at;
        $info['deactivateTime'] = DateHelper::fresnsDateTimeByTimezone($accountData->deleted_at, $timezone, $langTag);

        return $info;
    }

    public function getAccountConnects()
    {
        $connectsArr = $this->connects;

        $connectsItemArr = [];
        foreach ($connectsArr as $connect) {
            $item['connectId'] = $connect->connect_id;
            $item['username'] = $connect->connect_username;
            $item['nickname'] = $connect->connect_nickname;
            $item['avatar'] = $connect->connect_avatar;
            $item['status'] = (bool) $connect->is_enable;
            $connectsItemArr[] = $item;
        }

        return $connectsItemArr;
    }

    public function getAccountWallet(?string $langTag = null)
    {
        $walletData = $this->wallet;

        $currencyConfig = ConfigHelper::fresnsConfigByItemKeys([
            'wallet_currency_code',
            'wallet_currency_name',
            'wallet_currency_unit',
            'wallet_currency_precision',
        ], $langTag);

        $wallet['status'] = (bool) $walletData->is_enable;
        $wallet['hasPassword'] = (bool) $walletData->password;
        $wallet['currencyCode'] = $currencyConfig['wallet_currency_code'];
        $wallet['currencyName'] = $currencyConfig['wallet_currency_name'];
        $wallet['currencyUnit'] = $currencyConfig['wallet_currency_unit'];
        $wallet['currencyPrecision'] = $currencyConfig['wallet_currency_precision'];
        $wallet['balance'] = $walletData->balance;
        $wallet['freezeAmount'] = $walletData->freeze_amount;
        $wallet['bankName'] = $walletData->bank_name;
        $wallet['swiftCode'] = $walletData->swift_code;
        $wallet['bankAddress'] = $walletData->bank_address;
        $wallet['bankAccount'] = ! empty($walletData->bank_account) ? \Str::mask($walletData->bank_account, '*', -8, 4) : null;
        $wallet['bankStatus'] = $walletData->bank_status;

        return $wallet;
    }
}
