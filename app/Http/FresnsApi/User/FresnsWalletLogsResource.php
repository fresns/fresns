<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\User;

use App\Base\Resources\BaseAdminResource;
use App\Helpers\DateHelper;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsLanguages\FresnsLanguagesService;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Http\FresnsDb\FresnsUserWalletLogs\FresnsUserWalletLogs;

/**
 * List resource config handle.
 */
class FresnsWalletLogsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // user_wallet_logs > object_name >> plugin_usages(type=1 or 2) > name
        // If the plugin association has been deleted, the object_name field value is output as is
        $uid = request()->input('uid');
        $langTag = request()->input('langTag');
        $pluginUsages = FresnsPluginUsages::whereIn('type', [1, 2])->where('plugin_unikey', $this->objuct_name)->first();

        if (empty($pluginUsages)) {
            $name = $this->object_name;
        } else {
            $name = FresnsLanguagesService::getLanguageByTableId(FresnsPluginUsagesConfig::CFG_TABLE, 'name', $pluginUsages['id'], $langTag);
        }

        // Default Field
        $default = [
            'type' => $this->object_type,
            'amount' => $this->amount,
            'transactionAmount' => $this->transaction_amount,
            'systemFee' => $this->system_fee,
            'openingBalance' => $this->opening_balance,
            'closingBalance' => $this->closing_balance,
            'name' => $name,
            'remark' => $this->remark,
            'status' => $this->is_enable,
            'date' => DateHelper::fresnsOutputTimeToTimezone($this->created_at),
        ];

        return $default;
    }
}
