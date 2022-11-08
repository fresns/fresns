<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateRoleRequest;
use App\Helpers\DateHelper;
use App\Helpers\PrimaryHelper;
use App\Models\File;
use App\Models\FileUsage;
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
        $role->type = $request->type;
        $role->rating = $request->rating;
        $role->is_display_icon = $request->is_display_icon ?? 0;
        $role->is_display_name = $request->is_display_name ?? 0;
        $role->nickname_color = $request->nickname_color;
        $role->is_enable = $request->is_enable;

        $role->permissions = json_decode(config('FsConfig.role_default_permissions'), true);
        if ($request->no_color) {
            $role->nickname_color = null;
        }
        $role->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
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
        $role->type = $request->type;
        $role->rating = $request->rating;
        $role->is_display_icon = $request->is_display_icon ?? 0;
        $role->is_display_name = $request->is_display_name ?? 0;
        $role->nickname_color = $request->nickname_color;
        $role->is_enable = $request->is_enable;

        if ($request->no_color) {
            $role->nickname_color = null;
        }
        $role->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');

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
        $formPermissions['post_email_verify'] = $formPermissions['post_email_verify'] ?? 0;
        $formPermissions['post_phone_verify'] = $formPermissions['post_phone_verify'] ?? 0;
        $formPermissions['post_real_name_verify'] = $formPermissions['post_real_name_verify'] ?? 0;
        $formPermissions['comment_email_verify'] = $formPermissions['comment_email_verify'] ?? 0;
        $formPermissions['comment_phone_verify'] = $formPermissions['comment_phone_verify'] ?? 0;
        $formPermissions['comment_real_name_verify'] = $formPermissions['comment_real_name_verify'] ?? 0;
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
                'post_publish', 'post_email_verify', 'post_phone_verify', 'post_real_name_verify', 'post_review', 'post_limit_status',
                'comment_publish', 'comment_email_verify', 'comment_phone_verify', 'comment_review', 'comment_real_name_verify',
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
        });

        $customPermissions = collect($request->custom_permissions['permKey'] ?? [])->filter()->map(function ($value, $key) use ($request) {
            return [
                'permKey' => $value,
                'permValue' => $request->custom_permissions['permValue'][$key] ?? '',
                'isCustom' => true,
            ];
        });

        $role->permissions = $permissions->merge($customPermissions)->values()->toArray();
        $role->save();

        return $this->updateSuccess();
    }
}
