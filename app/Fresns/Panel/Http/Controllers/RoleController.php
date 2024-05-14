<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateRoleRequest;
use App\Helpers\DateHelper;
use App\Helpers\PrimaryHelper;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('sort_order')->get();

        return view('FsView::operations.roles', compact('roles'));
    }

    public function store(Role $role, UpdateRoleRequest $request)
    {
        $role->sort_order = $request->sort_order;
        $role->name = $request->names;
        $role->is_display_icon = $request->is_display_icon ?? 0;
        $role->is_display_name = $request->is_display_name ?? 0;
        $role->nickname_color = $request->nickname_color;
        $role->is_enabled = $request->is_enabled;

        $role->permissions = json_decode(config('FsConfig.role_default_permissions'), true);
        if ($request->no_color) {
            $role->nickname_color = null;
        }
        $role->save();

        if ($request->file('icon_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'roles',
                'tableColumn' => 'icon_file_id',
                'tableId' => $role->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsPrimaryId('file', $fresnsResp->getData('fid'));

            $role->icon_file_id = $fileId;
            $role->icon_file_url = null;
            $role->save();
        }

        return $this->createSuccess();
    }

    public function update(Role $role, UpdateRoleRequest $request)
    {
        $role->sort_order = $request->sort_order;
        $role->name = $request->names;
        $role->is_display_icon = $request->is_display_icon ?? 0;
        $role->is_display_name = $request->is_display_name ?? 0;
        $role->nickname_color = $request->nickname_color;
        $role->is_enabled = $request->is_enabled;

        if ($request->no_color) {
            $role->nickname_color = null;
        }

        if ($request->file('icon_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'roles',
                'tableColumn' => 'icon_file_id',
                'tableId' => $role->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsPrimaryId('file', $fresnsResp->getData('fid'));

            $role->icon_file_id = $fileId;
            $role->icon_file_url = null;
        } elseif ($role->icon_file_url != $request->icon_file_url) {
            $role->icon_file_id = null;
            $role->icon_file_url = $request->icon_file_url;
        }

        $role->save();

        return $this->updateSuccess();
    }

    public function destroy(Role $role, Request $request)
    {
        if ($request->role_id) {
            UserRole::where('role_id', $role->id)->update(['role_id' => $request->role_id]);
        }

        $role->delete();

        return $this->deleteSuccess();
    }

    public function updateSortOrder($id, Request $request)
    {
        $role = Role::findOrFail($id);
        $role->sort_order = $request->order;
        $role->save();

        return $this->updateSuccess();
    }

    public function showPermissions(Role $role)
    {
        $permissions = collect($role->permissions)->mapWithKeys(function ($perm) {
            return [$perm['permKey'] => $perm];
        })->toArray();

        $customPermissions = collect($role->permissions)->filter(function ($perm) {
            return $perm['isCustom'] ?? false;
        })->mapWithKeys(function ($perm) {
            return [$perm['permKey'] => $perm];
        })->toArray();

        $ruleTimezone = 'UTC'.DateHelper::fresnsDatabaseTimezone();

        return view('FsView::operations.role-permissions', compact('permissions', 'ruleTimezone', 'role', 'customPermissions'));
    }

    public function updatePermissions(Role $role, Request $request)
    {
        $formPermissions = $request->permissions;
        $formPermissions['post_required_email'] = $formPermissions['post_required_email'] ?? 0;
        $formPermissions['post_required_phone'] = $formPermissions['post_required_phone'] ?? 0;
        $formPermissions['post_required_kyc'] = $formPermissions['post_required_kyc'] ?? 0;
        $formPermissions['comment_required_email'] = $formPermissions['comment_required_email'] ?? 0;
        $formPermissions['comment_required_phone'] = $formPermissions['comment_required_phone'] ?? 0;
        $formPermissions['comment_required_kyc'] = $formPermissions['comment_required_kyc'] ?? 0;
        $formPermissions['post_editor_image'] = $formPermissions['post_editor_image'] ?? 0;
        $formPermissions['post_editor_video'] = $formPermissions['post_editor_video'] ?? 0;
        $formPermissions['post_editor_audio'] = $formPermissions['post_editor_audio'] ?? 0;
        $formPermissions['post_editor_document'] = $formPermissions['post_editor_document'] ?? 0;
        $formPermissions['comment_editor_image'] = $formPermissions['comment_editor_image'] ?? 0;
        $formPermissions['comment_editor_video'] = $formPermissions['comment_editor_video'] ?? 0;
        $formPermissions['comment_editor_audio'] = $formPermissions['comment_editor_audio'] ?? 0;
        $formPermissions['comment_editor_document'] = $formPermissions['comment_editor_document'] ?? 0;

        $permissions = collect($formPermissions)->map(function ($value, $key) {
            $boolPerms = [
                'content_view', 'conversation',
                'post_publish', 'post_required_email', 'post_required_phone', 'post_required_kyc', 'post_review', 'post_limit_status',
                'comment_publish', 'comment_required_email', 'comment_required_phone', 'comment_required_kyc', 'comment_review', 'comment_limit_status',
                'post_editor_image', 'post_editor_video', 'post_editor_audio', 'post_editor_document',
                'comment_editor_image', 'comment_editor_video', 'comment_editor_audio', 'comment_editor_document',
            ];
            if (in_array($key, $boolPerms)) {
                $value = (bool) $value;
            }

            return [
                'permKey' => $key,
                'permValue' => $value,
                'isCustom' => false,
            ];
        })->values()->toArray();

        $customPermissions = collect($request->custom_permissions['permKey'] ?? [])->filter()->map(function ($value, $key) use ($request) {
            return [
                'permKey' => $value,
                'permValue' => $request->custom_permissions['permValue'][$key] ?? '',
                'isCustom' => true,
            ];
        })->values()->toArray();

        $role->permissions = array_merge($permissions, $customPermissions);
        $role->save();

        return $this->updateSuccess();
    }
}
