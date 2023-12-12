<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Config;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Language;
use App\Models\Plugin;
use App\Models\PluginUsage;
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

    public function rechargeIndex()
    {
        $plugins = Plugin::all();

        $plugins = $plugins->filter(function ($plugin) {
            return in_array('walletRecharge', $plugin->panel_usages);
        });

        $pluginUsages = PluginUsage::type(PluginUsage::TYPE_WALLET_RECHARGE)
            ->orderBy('sort_order')
            ->with('plugin', 'names')
            ->get();

        return view('FsView::systems.wallet-recharge', compact('pluginUsages', 'plugins'));
    }

    public function rechargeStore(Request $request)
    {
        $pluginUsage = new PluginUsage;
        $pluginUsage->usage_type = PluginUsage::TYPE_WALLET_RECHARGE;
        $pluginUsage->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $pluginUsage->plugin_fskey = $request->plugin_fskey;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enabled = $request->is_enabled;
        $pluginUsage->sort_order = $request->sort_order;
        $pluginUsage->icon_file_url = $request->icon_file_url;
        $pluginUsage->save();

        if ($request->file('icon_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'plugin_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $pluginUsage->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $pluginUsage->icon_file_id = $fileId;
            $pluginUsage->icon_file_url = null;
            $pluginUsage->save();
        }

        if ($request->update_name) {
            foreach ($request->names as $langTag => $content) {
                $language = Language::tableName('plugin_usages')
                    ->where('table_id', $pluginUsage->id)
                    ->where('lang_tag', $langTag)
                    ->first();

                if (! $language) {
                    // create but no content
                    if (! $content) {
                        continue;
                    }
                    $language = new Language();
                    $language->fill([
                        'table_name' => 'plugin_usages',
                        'table_column' => 'name',
                        'table_id' => $pluginUsage->id,
                        'lang_tag' => $langTag,
                    ]);
                }

                $language->lang_content = $content;
                $language->save();
            }
        }

        return $this->createSuccess();
    }

    public function rechargeUpdate(PluginUsage $pluginUsage, Request $request)
    {
        $pluginUsage->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $pluginUsage->plugin_fskey = $request->plugin_fskey;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enabled = $request->is_enabled;
        $pluginUsage->sort_order = $request->sort_order;

        if ($request->file('icon_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'plugin_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $pluginUsage->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $pluginUsage->icon_file_id = $fileId;
            $pluginUsage->icon_file_url = null;
        } elseif ($pluginUsage->icon_file_url != $request->icon_file_url) {
            $pluginUsage->icon_file_id = null;
            $pluginUsage->icon_file_url = $request->icon_file_url;
        }

        $pluginUsage->save();

        if ($request->update_name) {
            foreach ($request->names as $langTag => $content) {
                $language = Language::tableName('plugin_usages')
                    ->where('table_id', $pluginUsage->id)
                    ->where('lang_tag', $langTag)
                    ->first();

                if (! $language) {
                    // create but no content
                    if (! $content) {
                        continue;
                    }
                    $language = new Language();
                    $language->fill([
                        'table_name' => 'plugin_usages',
                        'table_column' => 'name',
                        'table_id' => $pluginUsage->id,
                        'lang_tag' => $langTag,
                    ]);
                }

                $language->lang_content = $content;
                $language->save();
            }
        }

        return $this->updateSuccess();
    }

    public function withdrawIndex()
    {
        $pluginUsages = PluginUsage::type(PluginUsage::TYPE_WALLET_WITHDRAW)
            ->orderBy('sort_order')
            ->with('plugin')
            ->get();

        $plugins = Plugin::all();
        $plugins = $plugins->filter(function ($plugin) {
            return in_array('walletWithdraw', $plugin->panel_usages);
        });

        return view('FsView::systems.wallet-withdraw', compact('pluginUsages', 'plugins'));
    }

    public function withdrawStore(Request $request)
    {
        $pluginUsage = new PluginUsage;
        $pluginUsage->usage_type = PluginUsage::TYPE_WALLET_WITHDRAW;
        $pluginUsage->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $pluginUsage->plugin_fskey = $request->plugin_fskey;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enabled = $request->is_enabled;
        $pluginUsage->sort_order = $request->sort_order;
        $pluginUsage->icon_file_url = $request->icon_file_url;
        $pluginUsage->save();

        if ($request->file('icon_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'plugin_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $pluginUsage->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $pluginUsage->icon_file_id = $fileId;
            $pluginUsage->icon_file_url = null;
            $pluginUsage->save();
        }

        if ($request->update_name) {
            foreach ($request->names as $langTag => $content) {
                $language = Language::tableName('plugin_usages')
                    ->where('table_id', $pluginUsage->id)
                    ->where('lang_tag', $langTag)
                    ->first();

                if (! $language) {
                    // create but no content
                    if (! $content) {
                        continue;
                    }
                    $language = new Language();
                    $language->fill([
                        'table_name' => 'plugin_usages',
                        'table_column' => 'name',
                        'table_id' => $pluginUsage->id,
                        'lang_tag' => $langTag,
                    ]);
                }

                $language->lang_content = $content;
                $language->save();
            }
        }

        return $this->createSuccess();
    }

    public function withdrawUpdate(PluginUsage $pluginUsage, Request $request)
    {
        $pluginUsage->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $pluginUsage->plugin_fskey = $request->plugin_fskey;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enabled = $request->is_enabled;
        $pluginUsage->sort_order = $request->sort_order;

        if ($request->file('icon_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'plugin_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $pluginUsage->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $pluginUsage->icon_file_id = $fileId;
            $pluginUsage->icon_file_url = null;
        } elseif ($pluginUsage->icon_file_url != $request->icon_file_url) {
            $pluginUsage->icon_file_id = null;
            $pluginUsage->icon_file_url = $request->icon_file_url;
        }

        $pluginUsage->save();

        if ($request->update_name) {
            foreach ($request->names as $langTag => $content) {
                $language = Language::tableName('plugin_usages')
                    ->where('table_id', $pluginUsage->id)
                    ->where('lang_tag', $langTag)
                    ->first();

                if (! $language) {
                    // create but no content
                    if (! $content) {
                        continue;
                    }
                    $language = new Language();
                    $language->fill([
                        'table_name' => 'plugin_usages',
                        'table_column' => 'name',
                        'table_id' => $pluginUsage->id,
                        'lang_tag' => $langTag,
                    ]);
                }

                $language->lang_content = $content;
                $language->save();
            }
        }

        return $this->updateSuccess();
    }
}
