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

trait AccountServiceTrait
{
    public function getAccountInfo(?string $langTag = null): array
    {
        $accountData = $this;

        $birthday = null;
        if ($accountData->birthday) {
            $dateFormat = $langTag ? ConfigHelper::fresnsConfigDateFormat($langTag) : 'Y-m-d';

            $birthday = date($dateFormat, strtotime($accountData->birthday));
        }

        $info['aid'] = $accountData->aid;
        $info['hasPhone'] = (bool) $accountData->phone;
        $info['hasEmail'] = (bool) $accountData->email;
        $info['hasPassword'] = (bool) $accountData->password;
        $info['birthday'] = $birthday;
        $info['kycVerified'] = (bool) $accountData->is_verify;
        $info['status'] = (bool) $accountData->is_enabled;
        $info['waitDelete'] = (bool) $accountData->wait_delete;
        $info['waitDeleteDateTime'] = $accountData->wait_delete_at;

        return $info;
    }

    public function getAccountConnects(?string $langTag = null): array
    {
        $connectsArr = $this->connects;

        $connects = ConfigHelper::fresnsConfigByItemKey('connects');
        $connectServices = ConfigHelper::fresnsConfigByItemKey('account_connect_services') ?? [];

        // connect table
        $excludeConnectIds = [
            AccountConnect::CONNECT_WECHAT_OPEN_PLATFORM,
            AccountConnect::CONNECT_QQ_OPEN_PLATFORM,
        ];

        // connect table foreach
        $connectsItemArr = [];
        foreach ($connectsArr as $connect) {
            if (in_array($connect->connect_platform_id, $excludeConnectIds)) {
                continue;
            }

            // connect key
            $connectKey = array_search($connect->connect_platform_id, array_column($connects, 'id'));

            $platformName = null;
            if ($connectKey) {
                $platformName = $connects[$connectKey]['name'];
            }

            // connect service key
            $fskey = null;
            $nameArr = [];
            foreach ($connectServices as $service) {
                $code = (int) $service['code'];

                if ($code != $connect->connect_platform_id) {
                    continue;
                }

                $nameArr = $service['name'] ?? [];
                $fskey = $service['fskey'];
            }

            // app url
            $pluginUrl = PluginHelper::fresnsPluginUrlByFskey($connect->app_fskey);
            if (empty($pluginUrl)) {
                $pluginUrl = PluginHelper::fresnsPluginUrlByFskey($fskey);
            }

            $item['connectPlatformId'] = $connect->connect_platform_id;
            $item['connectPlatformName'] = $platformName;
            $item['connectName'] = StrHelper::languageContent($nameArr, $langTag);
            $item['connected'] = true;
            $item['service'] = $pluginUrl;
            $item['username'] = $connect->connect_username;
            $item['nickname'] = $connect->connect_nickname;
            $item['avatar'] = $connect->connect_avatar;
            $item['status'] = (bool) $connect->is_enabled;

            $connectsItemArr[] = $item;
        }

        // connect config
        $connectPlatformIdArr = $connectsArr->pluck('connect_platform_id')->toArray();
        $combinedArray = array_merge($connectPlatformIdArr, $excludeConnectIds);

        // connect config foreach
        foreach ($connectServices as $service) {
            $connectPlatformId = (int) $service['code'];
            $connectNameArr = $service['name'] ?? [];
            $pluginFskey = $service['fskey'] ?? null;

            if (in_array($connectPlatformId, $combinedArray)) {
                continue;
            }

            // connect key
            $connectKey = array_search($connectPlatformId, array_column($connects, 'id'));

            $connectPlatformName = null;
            if ($connectKey) {
                $connectPlatformName = $connects[$connectKey]['name'];
            }

            $item['connectPlatformId'] = $connectPlatformId;
            $item['connectPlatformName'] = $connectPlatformName;
            $item['connectName'] = StrHelper::languageContent($connectNameArr, $langTag);
            $item['connected'] = false;
            $item['service'] = PluginHelper::fresnsPluginUrlByFskey($pluginFskey);
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

        return $wallet;
    }
}
