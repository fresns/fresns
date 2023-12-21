<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\PrimaryHelper;
use App\Models\App;
use App\Models\AppUsage;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Group;
use App\Models\Role;
use Illuminate\Http\Request;

class AppUsageController extends Controller
{
    public function show(string $usageType, Request $request)
    {
        $type = match ($usageType) {
            'wallet-recharge' => AppUsage::TYPE_WALLET_RECHARGE,
            'wallet-withdraw' => AppUsage::TYPE_WALLET_WITHDRAW,
            'content-type' => AppUsage::TYPE_CONTENT,
            'editor' => AppUsage::TYPE_EDITOR,
            'manage' => AppUsage::TYPE_MANAGE,
            'group' => AppUsage::TYPE_GROUP,
            'user-feature' => AppUsage::TYPE_FEATURE,
            'user-profile' => AppUsage::TYPE_PROFILE,
            'channel' => AppUsage::TYPE_CHANNEL,
        };

        $usageQuery = AppUsage::with('app')->type($type);

        if ($usageType == 'group' && $request->groupId) {
            $usageQuery->where('group_id', $request->groupId);
        }

        $appUsages = $usageQuery->orderBy('sort_order')->paginate(30);

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

        $plugins = App::whereIn('type', [App::TYPE_PLUGIN, App::TYPE_APP_REMOTE])->get();
        $plugins = $plugins->filter(function ($plugin) use ($panelUsageName) {
            return in_array($panelUsageName, $plugin->panel_usages);
        });

        $roles = Role::all();

        $firstGroups = [];
        if ($usageType == 'group') {
            $firstGroups = Group::where('parent_id', 0)->orderBy('sort_order')->isEnabled()->get();
        }

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

        return view("FsView::{$viewName}", compact('appUsages', 'plugins', 'roles', 'firstGroups'));
    }

    public function store(string $usageType, Request $request)
    {
        $type = match ($usageType) {
            'wallet-recharge' => AppUsage::TYPE_WALLET_RECHARGE,
            'wallet-withdraw' => AppUsage::TYPE_WALLET_WITHDRAW,
            'content-type' => AppUsage::TYPE_CONTENT,
            'editor' => AppUsage::TYPE_EDITOR,
            'manage' => AppUsage::TYPE_MANAGE,
            'group' => AppUsage::TYPE_GROUP,
            'user-feature' => AppUsage::TYPE_FEATURE,
            'user-profile' => AppUsage::TYPE_PROFILE,
            'channel' => AppUsage::TYPE_CHANNEL,
        };

        $appUsage = new AppUsage;
        $appUsage->usage_type = $type;
        $appUsage->plugin_fskey = $request->plugin_fskey;
        $appUsage->name = $request->names;
        $appUsage->scene = $request->scene ? implode(',', $request->scene) : null;
        $appUsage->roles = $request->roles ? implode(',', $request->roles) : null;
        $appUsage->editor_toolbar = $request->editor_toolbar ?? 0;
        $appUsage->editor_number = $request->editor_number ?? null;
        $appUsage->is_group_admin = $request->is_group_admin ?? 0;
        $appUsage->group_id = $request->group_id ?? null;
        $appUsage->parameter = $request->parameter;
        $appUsage->is_enabled = $request->is_enabled;
        $appUsage->sort_order = $request->sort_order;
        $appUsage->icon_file_url = $request->icon_file_url;
        if ($request->is_group_admin) {
            $appUsage->roles = null;
        }
        $appUsage->save();

        if ($request->file('icon_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'app_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $appUsage->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $appUsage->icon_file_id = $fileId;
            $appUsage->icon_file_url = null;
            $appUsage->save();
        }

        return $this->createSuccess();
    }

    public function update(int $id, Request $request)
    {
        $appUsage = AppUsage::findOrFail($id);
        $appUsage->plugin_fskey = $request->plugin_fskey;
        $appUsage->name = $request->names;
        $appUsage->scene = $request->scene ? implode(',', $request->scene) : null;
        $appUsage->roles = $request->roles ? implode(',', $request->roles) : null;
        $appUsage->editor_toolbar = $request->editor_toolbar ?? 0;
        $appUsage->editor_number = $request->editor_number ?? null;
        $appUsage->is_group_admin = $request->is_group_admin ?? 0;
        $appUsage->group_id = $request->group_id ?? null;
        $appUsage->parameter = $request->parameter;
        $appUsage->sort_order = $request->sort_order;
        $appUsage->is_enabled = $request->is_enabled;
        if ($request->is_group_admin) {
            $appUsage->roles = null;
        }

        if ($request->file('icon_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'app_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $appUsage->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $appUsage->icon_file_id = $fileId;
            $appUsage->icon_file_url = null;
        } elseif ($appUsage->icon_file_url != $request->icon_file_url) {
            $appUsage->icon_file_id = null;
            $appUsage->icon_file_url = $request->icon_file_url;
        }

        $appUsage->save();

        return $this->updateSuccess();
    }

    public function destroy(int $id)
    {
        $appUsage = AppUsage::findOrFail($id);
        $appUsage->delete();

        return $this->deleteSuccess();
    }

    public function updateOrder(int $id, Request $request)
    {
        $appUsage = AppUsage::findOrFail($id);
        $appUsage->sort_order = $request->order;
        $appUsage->save();

        return $this->updateSuccess();
    }
}
