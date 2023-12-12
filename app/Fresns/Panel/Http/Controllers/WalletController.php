<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\StrHelper;
use App\Models\Config;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'currency_codes',
            'wallet_status',
            'wallet_currency_code',
            'wallet_currency_name',
            'wallet_currency_unit',
            'wallet_withdraw_status',
            'wallet_withdraw_review',
            'wallet_withdraw_check_kyc',
            'wallet_withdraw_interval_time',
            'wallet_withdraw_rate',
            'wallet_withdraw_min_sum',
            'wallet_withdraw_max_sum',
            'wallet_withdraw_sum_limit',
            'wallet_currency_precision',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $langKeys = [
            'wallet_currency_name',
            'wallet_currency_unit',
        ];

        $defaultLangParams = [];
        foreach ($langKeys as $langKey) {
            $defaultLangParams[$langKey] = StrHelper::languageContent($params[$langKey]);
        }

        return view('FsView::systems.wallet', compact('params', 'defaultLangParams'));
    }

    public function update(Request $request)
    {
        $configKeys = [
            'wallet_status',
            'wallet_currency_code',
            'wallet_withdraw_status',
            'wallet_withdraw_review',
            'wallet_withdraw_check_kyc',
            'wallet_withdraw_interval_time',
            'wallet_withdraw_rate',
            'wallet_withdraw_min_sum',
            'wallet_withdraw_max_sum',
            'wallet_withdraw_sum_limit',
            'wallet_currency_precision',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
                continue;
            }

            if (! $request->has($configKey)) {
                $config->setDefaultValue();
            } else {
                $config->item_value = $request->$configKey;
            }

            $config->save();
        }

        return $this->updateSuccess();
    }
}
