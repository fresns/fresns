<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateRoleRequest;
use App\Helpers\PrimaryHelper;
use App\Models\Language;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::orderBy('rating')->with('names')->get();

        $typeLabels = [
            1 => __('FsLang::panel.role_type_admin'),
            2 => __('FsLang::panel.role_type_system'),
            3 => __('FsLang::panel.role_type_user'),
        ];

        return view('FsView::operations.roles', compact(
            'roles',
            'typeLabels'
        ));
    }

    public function store(Role $role, UpdateRoleRequest $request)
    {
        $role->fill($request->all());
        $role->permission = json_decode(config('FsConfig.role_default_permission'), true);
        if ($request->no_color) {
            $role->nickname_color = null;
        }
        $role->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $role->save();

        if ($request->file('icon_file')) {
            $wordBody = [
                'platformId' => 4,
                'useType' => 2,
                'tableName' => 'roles',
                'tableColumn' => 'icon_file_id',
                'tableId' => $role->id,
                'type' => 1,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $role->icon_file_id = $fileId;
            $role->icon_file_url = null;
            $role->save();
        }

        foreach ($request->names as $langTag => $content) {
            $language = Language::tableName('roles')
                ->where('table_id', $role->id)
                ->where('lang_tag', $langTag)
                ->first();

            if (! $language) {
                // create but no content
                if (! $content) {
                    continue;
                }
                $language = new Language();
                $language->fill([
                    'table_name' => 'roles',
                    'table_column' => 'name',
                    'table_id' => $role->id,
                    'lang_tag' => $langTag,
                ]);
            }

            $language->lang_content = $content;
            $language->save();
        }

        return $this->createSuccess();
    }

    public function update(Role $role, UpdateRoleRequest $request)
    {
        $role->update($request->except('icon_file_url'));
        if ($request->no_color) {
            $role->nickname_color = null;
        }
        $role->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');

        if ($request->file('icon_file')) {
            $wordBody = [
                'platformId' => 4,
                'useType' => 2,
                'tableName' => 'roles',
                'tableColumn' => 'icon_file_id',
                'tableId' => $map->id,
                'type' => 1,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $role->icon_file_id = $fileId;
            $role->icon_file_url = null;
        } elseif ($role->icon_file_url != $request->icon_file_url) {
            $role->icon_file_id = null;
            $role->icon_file_url = $request->icon_file_url;
        }

        $role->save();

        foreach ($request->names as $langTag => $content) {
            $language = Language::tableName('roles')
                ->where('table_id', $role->id)
                ->where('lang_tag', $langTag)
                ->first();

            if (! $language) {
                // create but no content
                if (! $content) {
                    continue;
                }
                $language = new Language();
                $language->fill([
                    'table_name' => 'roles',
                    'table_column' => 'name',
                    'table_id' => $role->id,
                    'lang_tag' => $langTag,
                ]);
            }

            $language->lang_content = $content;
            $language->save();
        }

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

    public function updateRating($id, Request $request)
    {
        $role = Role::findOrFail($id);
        $role->rating = $request->rating;
        $role->save();

        return $this->updateSuccess();
    }

    public function showPermissions(Role $role)
    {
        $permission = collect($role->permission)->mapWithKeys(function ($perm) {
            return [$perm['permKey'] => $perm];
        })->toArray();

        $customPermission = collect($role->permission)->filter(function ($perm) {
            return $perm['isCustom'] ?? false;
        })->mapWithKeys(function ($perm) {
            return [$perm['permKey'] => $perm];
        })->toArray();

        return view('FsView::operations.role-permission', compact('permission', 'role', 'customPermission'));
    }

    public function updatePermissions(Role $role, Request $request)
    {
        $permission = collect($request->permission)->map(function ($value, $key) {
            $boolPerms = [
                'content_view', 'dialog', 'post_publish', 'post_review',
                'post_email_verify', 'post_phone_verify', 'post_prove_verify', 'post_limit_status',
                'comment_publish', 'comment_review', 'comment_email_verify', 'comment_phone_verify',
                'comment_prove_verify', 'post_editor_image', 'post_editor_video', 'post_editor_audio',
                'post_editor_document', 'comment_editor_image', 'comment_editor_video', 'comment_editor_audio',
                'comment_editor_document',
            ];
            if (in_array($key, $boolPerms)) {
                $value = (bool) $value;
            }

            return [
                'permKey' => $key,
                'permValue' => $value,
                'permStatus' => '',
                'isCustom' => false,
            ];
        });
        $customPermission = collect($request->custom_permissions['permKey'] ?? [])->filter()->map(function ($value, $key) use ($request) {
            return [
                'permKey' => $value,
                'permValue' => $request->custom_permissions['permValue'][$key] ?? '',
                'permStatus' => '',
                'isCustom' => true,
            ];
        });
        $role->permission = $permission->merge($customPermission)->values()->toArray();
        $role->save();

        return $this->updateSuccess();
    }
}
