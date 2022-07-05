<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\PrimaryHelper;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Group;
use App\Models\Language;
use App\Models\Plugin;
use App\Models\Post;
use App\Models\PostLog;
use App\Models\Role;
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
            ->orderBy('rating')
            ->with('names', 'descriptions')
            ->get();

        $parentId = $request->parent_id ?: (optional($categories->first())->id ?: null);

        $groups = [];

        if ($parentId) {
            $groups = Group::typeGroup()
                ->orderBy('rating')
                ->where('parent_id', $parentId)
                ->isEnable()
                ->with('creator', 'followByPlugin', 'names', 'descriptions', 'admins')
                ->paginate();
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
            ->isEnable()
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
            ->orderBy('recommend_rating')
            ->with('creator', 'followByPlugin', 'category', 'admins')
            ->where('is_recommend', 1)
            ->isEnable()
            ->paginate();

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

        $categories = Group::typeCategory()
            ->with('names', 'descriptions')
            ->get();

        $groups = Group::typeGroup()
            ->orderBy('rating')
            ->isEnable(false)
            ->with('creator', 'followByPlugin', 'category')
            ->paginate();

        $plugins = Plugin::all();
        $plugins = $plugins->filter(function ($plugin) {
            return in_array('followGroup', $plugin->scene);
        });

        $roles = Role::with('names')->get();

        extract(get_object_vars($this));

        return view('FsView::operations.groups-inactive', compact(
            'categories',
            'groups',
            'typeModeLabels',
            'permissionLabels',
            'plugins',
            'roles',
        ));
    }

    public function store(Group $group, Request $request)
    {
        $group->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $group->description = $request->descriptions[$this->defaultLanguage] ?? (current(array_filter($request->descriptions)) ?: '');
        $group->rating = $request->rating;
        $group->cover_file_url = $request->cover_file_url;
        $group->banner_file_url = $request->banner_file_url;
        // group category
        if ($request->is_category) {
            $group->parent_id = null;
            $group->type = 1;
            $group->permissions = [];
            if ($request->has('is_enable')) {
                $group->is_enable = $request->is_enable;
            }
        } else {
            $group->parent_id = $request->parent_id;
            $group->type = 2;
            $group->type_mode = $request->type_mode;
            $group->type_find = $request->type_find;
            $group->type_follow = $request->type_follow;
            $group->is_recommend = $request->is_recommend;
            $group->plugin_unikey = $request->plugin_unikey;

            $permissions = $request->permissions;
            $permissions['publish_post_review'] = (bool) ($permissions['publish_post_review'] ?? 0);
            $permissions['publish_comment_review'] = (bool) ($permissions['publish_comment_review'] ?? 0);

            $group->permissions = $permissions;
        }
        $group->save();

        if ($request->admin_ids) {
            $group->admins()->sync($request->admin_ids);
        }

        if ($request->file('cover_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'groups',
                'tableColumn' => 'cover_file_id',
                'tableId' => $group->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('cover_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $group->cover_file_id = $fileId;
            $group->cover_file_url = null;
            $group->save();
        }

        if ($request->file('banner_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'groups',
                'tableColumn' => 'banner_file_id',
                'tableId' => $group->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('banner_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $group->banner_file_id = $fileId;
            $group->banner_file_url = null;
            $group->save();
        }

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
        $group->rating = $request->rating;

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

            $permissions = $request->permissions;
            $permissions['publish_post_review'] = (bool) ($permissions['publish_post_review'] ?? 0);
            $permissions['publish_comment_review'] = (bool) ($permissions['publish_comment_review'] ?? 0);

            $group->permissions = $permissions;

            $group->admins()->sync($request->admin_ids);
        }

        if ($request->file('cover_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'groups',
                'tableColumn' => 'cover_file_id',
                'tableId' => $group->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('cover_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $group->cover_file_id = $fileId;
            $group->cover_file_url = null;
        } elseif ($group->cover_file_url != $request->cover_file_url) {
            $group->cover_file_id = null;
            $group->cover_file_url = $request->cover_file_url;
        }

        if ($request->file('banner_file')) {
            $wordBody = [
                'usageType' => FileUsage::TYPE_SYSTEM,
                'platformId' => 4,
                'tableName' => 'groups',
                'tableColumn' => 'banner_file_id',
                'tableId' => $group->id,
                'type' => File::TYPE_IMAGE,
                'file' => $request->file('banner_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $group->banner_file_id = $fileId;
            $group->banner_file_url = null;
        } elseif ($group->banner_file_url != $request->banner_file_url) {
            $group->banner_file_id = null;
            $group->banner_file_url = $request->banner_file_url;
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

    public function updateRating(Group $group, Request $request)
    {
        $group->rating = $request->rating;
        $group->save();

        return $this->updateSuccess();
    }

    public function updateRecommendRank(Group $group, Request $request)
    {
        $group->recommend_rating = $request->rating;
        $group->save();

        return $this->updateSuccess();
    }
}
