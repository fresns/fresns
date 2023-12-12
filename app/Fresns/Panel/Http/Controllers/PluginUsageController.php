<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\PrimaryHelper;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Plugin;
use App\Models\PluginUsage;
use App\Models\Role;
use Illuminate\Http\Request;

class PluginUsageController extends Controller
{
    public function show(string $usageType, Request $request)
    {
        $type = match ($usageType) {
            'wallet-recharge' => PluginUsage::TYPE_WALLET_RECHARGE,
            'wallet-withdraw' => PluginUsage::TYPE_WALLET_WITHDRAW,
            'content-type' => PluginUsage::TYPE_CONTENT,
            'editor' => PluginUsage::TYPE_EDITOR,
            'manage' => PluginUsage::TYPE_MANAGE,
            'group' => PluginUsage::TYPE_GROUP,
            'user-feature' => PluginUsage::TYPE_FEATURE,
            'user-profile' => PluginUsage::TYPE_PROFILE,
            'channel' => PluginUsage::TYPE_CHANNEL,
        };

        $pluginUsages = PluginUsage::with('plugin')->type($type)->orderBy('sort_order')->paginate();

        $panelUsageName = match ($usageType) {
            'wallet-recharge' => 'walletRecharge',
            'wallet-withdraw' => 'walletWithdraw',
            'content-type' => 'extendContentType',
            'editor' => 'extendEditor',
            'manage' => 'extendManage',
            'group' => 'extendGroup',
            'user-feature' => 'extendUser',
            'user-profile' => 'extendUser',
            'channel' => 'extendChannel',
        };

        $plugins = Plugin::all();
        $plugins = $plugins->filter(function ($plugin) use($panelUsageName) {
            return in_array($panelUsageName, $plugin->panel_usages);
        });

        $roles = Role::all();

        $groups = [];

        $viewName = match ($usageType) {
            'wallet-recharge' => 'systems.wallet-recharge',
            'wallet-withdraw' => 'systems.wallet-withdraw',
            'content-type' => 'extends.content-type',
            'editor' => 'extends.editor',
            'manage' => 'extends.manage',
            'group' => 'extends.group',
            'user-feature' => 'extends.user-feature',
            'user-profile' => 'extends.user-profile',
            'channel' => 'extends.channel',
        };

        return view("FsView::{$viewName}", compact('pluginUsages', 'plugins', 'roles', 'groups'));
    }

    public function store(string $usageType, Request $request)
    {
        $type = match ($usageType) {
            'wallet-recharge' => PluginUsage::TYPE_WALLET_RECHARGE,
            'wallet-withdraw' => PluginUsage::TYPE_WALLET_WITHDRAW,
            'content-type' => PluginUsage::TYPE_CONTENT,
            'editor' => PluginUsage::TYPE_EDITOR,
            'manage' => PluginUsage::TYPE_MANAGE,
            'group' => PluginUsage::TYPE_GROUP,
            'user-feature' => PluginUsage::TYPE_FEATURE,
            'user-profile' => PluginUsage::TYPE_PROFILE,
            'channel' => PluginUsage::TYPE_CHANNEL,
        };

        $pluginUsage = new PluginUsage;
        $pluginUsage->usage_type = $type;
        $pluginUsage->plugin_fskey = $request->plugin_fskey;
        $pluginUsage->name = $request->names;
        $pluginUsage->scene = $request->scene ? implode(',', $request->scene) : null;
        $pluginUsage->roles = $request->roles ? implode(',', $request->roles) : null;
        $pluginUsage->editor_toolbar = $request->editor_toolbar ?? 0;
        $pluginUsage->editor_number = $request->editor_number ?? null;
        $pluginUsage->is_group_admin = $request->is_group_admin ?? 0;
        $pluginUsage->group_id = $request->group_id ?? null;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enabled = $request->is_enabled;
        $pluginUsage->sort_order = $request->sort_order;
        $pluginUsage->icon_file_url = $request->icon_file_url;
        if ($request->is_group_admin) {
            $pluginUsage->roles = null;
        }
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

        return $this->createSuccess();
    }

    public function update(int $id, Request $request)
    {
        $pluginUsage = PluginUsage::findOrFail($id);
        $pluginUsage->plugin_fskey = $request->plugin_fskey;
        $pluginUsage->name = $request->names;
        $pluginUsage->scene = $request->scene ? implode(',', $request->scene) : null;
        $pluginUsage->roles = $request->roles ? implode(',', $request->roles) : null;
        $pluginUsage->editor_toolbar = $request->editor_toolbar ?? 0;
        $pluginUsage->editor_number = $request->editor_number ?? null;
        $pluginUsage->is_group_admin = $request->is_group_admin ?? 0;
        $pluginUsage->group_id = $request->group_id ?? null;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->sort_order = $request->sort_order;
        $pluginUsage->is_enabled = $request->is_enabled;
        if ($request->is_group_admin) {
            $pluginUsage->roles = null;
        }

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

        return $this->updateSuccess();
    }

    public function destroy(int $id)
    {
        $pluginUsage = PluginUsage::findOrFail($id);
        $pluginUsage->delete();

        return $this->deleteSuccess();
    }

    public function updateOrder(int $id, Request $request)
    {
        $pluginUsage = PluginUsage::findOrFail($id);
        $pluginUsage->sort_order = $request->order;
        $pluginUsage->save();

        return $this->updateSuccess();
    }
}
