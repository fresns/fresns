<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\PluginHelper;
use App\Helpers\StrHelper;
use App\Models\AccountConnect;
use Illuminate\Support\Str;

trait AccountServiceTrait
{
    public function getAccountInfo(): array
    {
        $accountData = $this;

        $info['aid'] = $accountData->aid;
        $info['countryCode'] = (int) $accountData->country_code;
        $info['purePhone'] = $accountData->pure_phone ? StrHelper::maskNumber($accountData->pure_phone) : null;
        $info['phone'] = $accountData->phone ? StrHelper::maskNumber($accountData->phone) : null;
        $info['email'] = $accountData->email ? StrHelper::maskEmail($accountData->email) : null;
        $info['hasPassword'] = (bool) $accountData->password;
        $info['verifyStatus'] = (bool) $accountData->is_verify;
        $info['verifyRealName'] = $accountData->verify_real_name ? StrHelper::maskName($accountData->verify_real_name) : null;
        $info['verifyGender'] = $accountData->verify_gender;
        $info['verifyCertType'] = $accountData->verify_cert_type;
        $info['verifyCertNumber'] = $accountData->verify_cert_number ? StrHelper::maskName($accountData->verify_cert_number) : null;
        $info['verifyIdentityType'] = $accountData->verify_identity_type;
        $info['verifyDateTime'] = $accountData->verify_at;
        $info['registerDateTime'] = $accountData->created_at;
        $info['status'] = (bool) $accountData->is_enabled;
        $info['waitDelete'] = (bool) $accountData->wait_delete;
        $info['waitDeleteDateTime'] = $accountData->wait_delete_at;

        return $info;
    }

    public function getAccountConnects(): array
    {
        $connectsArr = $this->connects;

        $connects = ConfigHelper::fresnsConfigByItemKey('connects');
        $connectServices = ConfigHelper::fresnsConfigByItemKey('account_connect_services') ?? [];

        $excludeConnectIds = [
            AccountConnect::CONNECT_WECHAT_OPEN_PLATFORM,
            AccountConnect::CONNECT_QQ_OPEN_PLATFORM,
        ];

        $connectsItemArr = [];
        foreach ($connectsArr as $connect) {
            if (in_array($connect->connect_platform_id, $excludeConnectIds)) {
                continue;
            }

            // connect key
            $connectKey = array_search($connect->connect_platform_id, array_column($connects, 'id'));

            $connectName = null;
            if ($connectKey) {
                $connectName = $connects[$connectKey]['name'];
            }

            $pluginUrl = PluginHelper::fresnsPluginUrlByFskey($connect->app_fskey);
            if (empty($pluginUrl)) {
                // service fskey
                $fskey = null;
                foreach ($connectServices as $service) {
                    $code = (int) $service['code'];

                    if ($code != $connect->connect_platform_id) {
                        continue;
                    }

                    $fskey = $service['fskey'];
                }

                $pluginUrl = PluginHelper::fresnsPluginUrlByFskey($fskey);
            }

            $item['connectPlatformId'] = $connect->connect_platform_id;
            $item['connectName'] = $connectName;
            $item['connected'] = true;
            $item['service'] = $pluginUrl;
            $item['username'] = $connect->connect_username;
            $item['nickname'] = $connect->connect_nickname;
            $item['avatar'] = $connect->connect_avatar;
            $item['status'] = (bool) $connect->is_enabled;

            $connectsItemArr[] = $item;
        }

        $connectPlatformIdArr = $connectsArr->pluck('connect_platform_id')->toArray();

        $combinedArray = array_merge($connectPlatformIdArr, $excludeConnectIds);

        foreach ($connectServices as $service) {
            $connectPlatformId = (int) $service['code'];

            if (in_array($connectPlatformId, $combinedArray)) {
                continue;
            }

            // connect key
            $connectKey = array_search($connectPlatformId, array_column($connects, 'id'));

            $connectName = null;
            if ($connectKey) {
                $connectName = $connects[$connectKey]['name'];
            }

            $item['connectPlatformId'] = $connectPlatformId;
            $item['connectName'] = $connectName;
            $item['connected'] = false;
            $item['service'] = PluginHelper::fresnsPluginUrlByFskey($service['fskey']) ?? $service['fskey'];
            $item['username'] = null;
            $item['nickname'] = null;
            $item['avatar'] = null;
            $item['status'] = false;

            $connectsItemArr[] = $item;
        }

        usort($connectsItemArr, function ($a, $b) {
            return $a['connectPlatformId'] <=> $b['connectPlatformId'];
        });

        return $connectsItemArr;
    }

    public function getAccountWallet(?string $langTag = null): array
    {
        $walletData = $this->wallet;

        $currencyConfig = ConfigHelper::fresnsConfigByItemKeys([
            'wallet_currency_code',
            'wallet_currency_name',
            'wallet_currency_unit',
            'wallet_currency_precision',
        ], $langTag);

        $wallet['status'] = (bool) $walletData->is_enabled;
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
        $wallet['bankAccount'] = $walletData->bank_account ? Str::mask($walletData->bank_account, '*', -8, 4) : null;
        $wallet['bankStatus'] = (bool) $walletData->bank_status;

        return $wallet;
    }
}
