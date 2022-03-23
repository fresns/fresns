<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Group;
use App\Models\Language;
use App\Models\Plugin;
use App\Models\Post;
use App\Models\PostLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    protected $typeModeLabels;

    protected $permissionLabels;

    public function initOptions()
    {
        $this->typeModeLabels = [
            1 => __('FsLang::panel.group_table_mode_public'),
            2 => __('FsLang::panel.group_table_mode_private'),
        ];

        $this->permissionLabels = [
            1 => __('FsLang::panel.group_option_publish_all'),
            2 => __('FsLang::panel.group_option_publish_follow'),
            3 => __('FsLang::panel.group_option_publish_role'),
        ];
    }

    public function index(Request $request)
    {
        $this->initOptions();

        $categories = Group::typeCategory()
            ->orderBy('rank_num')
            ->with('names', 'descriptions')
            ->get();

        $parentId = $request->parent_id ?: (optional($categories->first())->id ?: 0);

        $groups = [];

        if ($parentId) {
            $groups = Group::typeGroup()
                ->orderBy('rank_num')
                ->where('parent_id', $parentId)
                ->where('is_enable', 1)
                ->with('user', 'plugin', 'names', 'descriptions')
                ->paginate();

            $groups->map(function ($group) {
                $userIds = $group->permission['admin_users'] ?? [];
                $group->admin_users = User::whereIn('id', $userIds)->get();
            });
        }

        extract(get_object_vars($this));

        $plugins = Plugin::all();
        $plugins = $plugins->filter(function ($plugin) {
            return in_array('followGroup', $plugin->scene);
        });

        $roles = Role::with('names')->get();

        return view('FsView::operations.groups', compact(
            'categories',
            'groups',
            'typeModeLabels',
            'parentId',
            'permissionLabels',
            'plugins',
            'roles',
        ));
    }

    public function groupIndex(Request $request)
    {
        $groups = Group::typeGroup()
            ->where('parent_id', $request->category_id)
            ->where('is_enable', 1)
            ->get();

        return response()->json($groups);
    }

    public function recommendIndex()
    {
        $this->initOptions();

        $categories = Group::typeCategory()
            ->with('names', 'descriptions')
            ->get();

        $groups = Group::typeGroup()
            ->orderBy('recom_rank_num')
            ->with('user', 'plugin', 'category')
            ->where('is_recommend', 1)
            ->where('is_enable', 1)
            ->paginate();

        $groups->map(function ($group) {
            $userIds = $group->permission['admin_users'] ?? [];
            $group->admin_users = User::whereIn('id', $userIds)->get();
        });

        $plugins = Plugin::all();
        $plugins = $plugins->filter(function ($plugin) {
            return in_array('followGroup', $plugin->scene);
        });

        $roles = Role::with('names')->get();

        extract(get_object_vars($this));

        return view('FsView::operations.groups-recommend', compact(
            'categories',
            'groups',
            'typeModeLabels',
            'permissionLabels',
            'plugins',
            'roles',
        ));
    }

    public function disableIndex()
    {
        $this->initOptions();

        $groups = Group::typeGroup()
            ->orderBy('rank_num')
            ->where('is_enable', 0)
            ->with('user', 'plugin', 'category')
            ->paginate();

        extract(get_object_vars($this));

        return view('FsView::operations.groups-inactive', compact(
            'groups',
            'typeModeLabels',
            'permissionLabels'
        ));
    }

    public function store(Group $group, Request $request)
    {
        $group->gid = \Str::random(12);
        $group->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $group->description = $request->descriptions[$this->defaultLanguage] ?? (current(array_filter($request->descriptions)) ?: '');
        $group->rank_num = $request->rank_num;
        $group->cover_file_url = $request->cover_file_url;
        $group->banner_file_url = $request->banner_file_url;
        // group category
        if ($request->is_category) {
            $group->permission = [];
            $group->parent_id = 0;
            $group->type = 1;
            if ($request->has('is_enable')) {
                $group->is_enable = $request->is_enable;
            }
        } else {
            $group->parent_id = $request->parent_id;
            $group->type_mode = $request->type_mode;
            $group->type_find = $request->type_find;
            $group->type_follow = $request->type_follow;
            $group->is_recommend = $request->is_recommend;
            $group->plugin_unikey = $request->plugin_unikey;
            $permission = $request->permission;
            $permission['publish_post_review'] = (bool) ($permission['publish_post_review'] ?? 0);
            $permission['publish_comment_review'] = (bool) ($permission['publish_comment_review'] ?? 0);
            $group->permission = $permission;
            $group->type = 2;
        }
        $group->save();

        if ($request->update_name) {
            foreach ($request->names as $langTag => $content) {
                $language = Language::tableName('groups')
                    ->where('table_id', $group->id)
                    ->where('table_column', 'name')
                    ->where('lang_tag', $langTag)
                    ->first();

                if (! $language) {
                    // create but no content
                    if (! $content) {
                        continue;
                    }
                    $language = new Language();
                    $language->fill([
                        'table_name' => 'groups',
                        'table_column' => 'name',
                        'table_id' => $group->id,
                        'lang_tag' => $langTag,
                    ]);
                }

                $language->lang_content = $content;
                $language->save();
            }
        }

        if ($request->update_description) {
            foreach ($request->descriptions as $langTag => $content) {
                $language = Language::tableName('groups')
                    ->where('table_id', $group->id)
                    ->where('table_column', 'description')
                    ->where('lang_tag', $langTag)
                    ->first();

                if (! $language) {
                    // create but no content
                    if (! $content) {
                        continue;
                    }
                    $language = new Language();
                    $language->fill([
                        'table_name' => 'groups',
                        'table_column' => 'description',
                        'table_id' => $group->id,
                        'lang_tag' => $langTag,
                    ]);
                }

                $language->lang_content = $content;
                $language->save();
            }
        }

        return $this->createSuccess();
    }

    public function update(Group $group, Request $request)
    {
        $group->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $group->description = $request->descriptions[$this->defaultLanguage] ?? (current(array_filter($request->descriptions)) ?: '');
        $group->rank_num = $request->rank_num;
        $group->cover_file_url = $request->cover_file_url;
        $group->banner_file_url = $request->banner_file_url;
        // group category
        if ($request->is_category) {
            $group->permission = [];
            if ($request->has('is_enable')) {
                $group->is_enable = $request->is_enable;
            }
        } else {
            $group->parent_id = $request->parent_id;
            $group->type_mode = $request->type_mode;
            $group->type_find = $request->type_find;
            $group->type_follow = $request->type_follow;
            $group->is_recommend = $request->is_recommend;
            $group->plugin_unikey = $request->plugin_unikey;
            $permission = $request->permission;
            $permission['publish_post_review'] = (bool) ($permission['publish_post_review'] ?? 0);
            $permission['publish_comment_review'] = (bool) ($permission['publish_comment_review'] ?? 0);
            $group->permission = $permission;
        }
        $group->save();

        if ($request->update_name) {
            foreach ($request->names as $langTag => $content) {
                $language = Language::tableName('groups')
                    ->where('table_id', $group->id)
                    ->where('table_column', 'name')
                    ->where('lang_tag', $langTag)
                    ->first();

                if (! $language) {
                    // create but no content
                    if (! $content) {
                        continue;
                    }
                    $language = new Language();
                    $language->fill([
                        'table_name' => 'groups',
                        'table_column' => 'name',
                        'table_id' => $group->id,
                        'lang_tag' => $langTag,
                    ]);
                }

                $language->lang_content = $content;
                $language->save();
            }
        }

        if ($request->update_description) {
            foreach ($request->descriptions as $langTag => $content) {
                $language = Language::tableName('groups')
                    ->where('table_id', $group->id)
                    ->where('table_column', 'description')
                    ->where('lang_tag', $langTag)
                    ->first();

                if (! $language) {
                    // create but no content
                    if (! $content) {
                        continue;
                    }
                    $language = new Language();
                    $language->fill([
                        'table_name' => 'groups',
                        'table_column' => 'description',
                        'table_id' => $group->id,
                        'lang_tag' => $langTag,
                    ]);
                }

                $language->lang_content = $content;
                $language->save();
            }
        }

        return $this->updateSuccess();
    }

    public function updateEnable(Group $group, Request $request)
    {
        $group->is_enable = $request->is_enable ?: 0;
        $group->save();

        return $this->updateSuccess();
    }

    public function destroy(Group $group)
    {
        // Group Category
        if ($group->type == 1 && $group->groups()->count()) {
            abort(403, __('FsLang::tips.delete_group_category_error'));
        }
        $group->delete();

        return $this->deleteSuccess();
    }

    public function mergeGroup(Group $group, Request $request)
    {
        if ($request->group_id) {
            Post::where('group_id', $group->id)->update(['group_id' => $request->group_id]);
            PostLog::where('group_id', $group->id)->update(['group_id' => $request->group_id]);

            $group->delete();
        }

        return $this->updateSuccess();
    }

    public function updateRank(Group $group, Request $request)
    {
        $group->rank_num = $request->rank_num;
        $group->save();

        return $this->updateSuccess();
    }

    public function updateRecomRank(Group $group, Request $request)
    {
        $group->recom_rank_num = $request->rank_num;
        $group->save();

        return $this->updateSuccess();
    }
}
