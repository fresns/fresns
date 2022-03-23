<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Account;

use App\Fresns\Api\Base\Resources\BaseAdminResource;
use App\Fresns\Api\FsDb\FresnsLanguages\FresnsLanguagesService;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesConfig;
use App\Fresns\Api\Helpers\DateHelper;

/**
 * List resource config handle.
 */
class FresnsWalletLogsResource extends BaseAdminResource
{
    public function toArray($request)
    {
        // account_wallet_logs > object_name >> plugin_usages(type=1 or 2) > name
        // If the plugin association has been deleted, the object_name field value is output as is
        $aid = request()->input('aid');
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
