<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\PrimaryHelper;
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
            'wallet_status',
            'wallet_currency_code',
            'wallet_withdraw_status',
            'wallet_withdraw_review',
            'wallet_withdraw_verify',
            'wallet_withdraw_interval_time',
            'wallet_withdraw_rate',
            'wallet_withdraw_min_sum',
            'wallet_withdraw_max_sum',
            'wallet_withdraw_sum_limit',
            'currency_codes',
            'wallet_currency_name',
            'wallet_currency_unit',
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

        $languages = Language::ofConfig()->whereIn('table_key', $langKeys)->get();

        $langParams = [];
        $defaultLangParams = [];
        foreach ($langKeys as $langKey) {
            $langParams[$langKey] = $languages->where('table_key', $langKey)->pluck('lang_content', 'lang_tag')->toArray();
            $defaultLangParams[$langKey] = $languages->where('table_key', $langKey)->where('lang_tag', $this->defaultLanguage)->first()['lang_content'] ?? '';
        }

        return view('FsView::systems.wallet', compact('params', 'defaultLangParams', 'langParams'));
    }

    public function update(Request $request)
    {
        $configKeys = [
            'wallet_status',
            'wallet_currency_code',
            'wallet_withdraw_status',
            'wallet_withdraw_review',
            'wallet_withdraw_verify',
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
            }

            if (! $request->has($configKey)) {
                $config->setDefaultValue();
            } else {
                $config->item_value = $request->$configKey;
            }

            $config->save();
        }

        foreach ($request->wallet_currency_name as $langTag => $content) {
            $language = Language::ofConfig()
                ->where('table_key', 'wallet_currency_name')
                ->where('lang_tag', $langTag)
                ->first();
            if (! $language) {
                // create but no content
                if (! $content) {
                    continue;
                }
                $language = new Language();
                $language->fill([
                    'table_name' => 'configs',
                    'table_column' => 'item_value',
                    'table_key' => 'wallet_currency_name',
                    'lang_tag' => $langTag,
                ]);
            }

            $language->lang_content = $content;
            $language->save();
        }

        foreach ($request->wallet_currency_unit as $langTag => $content) {
            $language = Language::ofConfig()
                ->where('table_key', 'wallet_currency_unit')
                ->where('lang_tag', $langTag)
                ->first();
            if (! $language) {
                // create but no content
                if (! $content) {
                    continue;
                }
                $language = new Language();
                $language->fill([
                    'table_name' => 'configs',
                    'table_column' => 'item_value',
                    'table_key' => 'wallet_currency_unit',
                    'lang_tag' => $langTag,
                ]);
            }

            $language->lang_content = $content;
            $language->save();
        }

        return $this->updateSuccess();
    }

    public function rechargeIndex()
    {
        $plugins = Plugin::all();

        $plugins = $plugins->filter(function ($plugin) {
            return in_array('recharge', $plugin->scene);
        });

        $pluginUsages = PluginUsage::type(PluginUsage::TYPE_WALLET_RECHARGE)
            ->orderBy('rating')
            ->with('plugin', 'names')
            ->get();

        return view('FsView::systems.wallet-recharge', compact('pluginUsages', 'plugins'));
    }

    public function rechargeStore(Request $request)
    {
        $pluginUsage = new PluginUsage;
        $pluginUsage->usage_type = PluginUsage::TYPE_WALLET_RECHARGE;
        $pluginUsage->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $pluginUsage->plugin_unikey = $request->plugin_unikey;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enable = $request->is_enable;
        $pluginUsage->rating = $request->rating;
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
        $pluginUsage->plugin_unikey = $request->plugin_unikey;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enable = $request->is_enable;
        $pluginUsage->rating = $request->rating;

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
            ->orderBy('rating')
            ->with('plugin')
            ->get();

        $plugins = Plugin::all();
        $plugins = $plugins->filter(function ($plugin) {
            return in_array('withdraw', $plugin->scene);
        });

        return view('FsView::systems.wallet-withdraw', compact('pluginUsages', 'plugins'));
    }

    public function withdrawStore(Request $request)
    {
        $pluginUsage = new PluginUsage;
        $pluginUsage->usage_type = PluginUsage::TYPE_WALLET_WITHDRAW;
        $pluginUsage->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $pluginUsage->plugin_unikey = $request->plugin_unikey;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enable = $request->is_enable;
        $pluginUsage->rating = $request->rating;
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
        $pluginUsage->plugin_unikey = $request->plugin_unikey;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enable = $request->is_enable;
        $pluginUsage->rating = $request->rating;

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
