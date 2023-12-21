<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\PrimaryHelper;
use App\Models\App;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\Group;
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
            1 => __('FsLang::panel.option_public'),
            2 => __('FsLang::panel.option_private'),
        ];

        $this->permissionLabels = [
            1 => __('FsLang::panel.group_publish_option_all'),
            2 => __('FsLang::panel.group_publish_option_members'),
            3 => __('FsLang::panel.group_publish_option_roles'),
            4 => __('FsLang::panel.group_publish_option_admins'),
        ];
    }

    public function index(Request $request)
    {
        $this->initOptions();
        extract(get_object_vars($this));

        $groupQuery = Group::with('parentGroup', 'followByApp', 'admins');

        $parentGroup = null;

        switch ($request->type) {
            case 'deactivate':
                $groupQuery->where('is_enabled', false)->orderBy('updated_at');
                break;

            case 'recommend':
                $groupQuery->where('is_recommend', true)->orderBy('recommend_sort_order')->isEnabled();
                break;

            default:
                if ($request->parentId) {
                    $parentGroup = Group::with('parentGroup')->where('id', $request->parentId)->first();

                    $groupQuery->where('parent_id', $request->parentId);
                } else {
                    $groupQuery->where('parent_id', 0);
                }

                $groupQuery->orderBy('sort_order')->isEnabled();
        }

        $groups = $groupQuery->paginate(25);

        $plugins = App::whereIn('type', [App::TYPE_PLUGIN, App::TYPE_APP_REMOTE])->get();
        $plugins = $plugins->filter(function ($plugin) {
            return in_array('followGroup', $plugin->panel_usages);
        });

        $roles = Role::get();
        $firstGroups = Group::where('parent_id', 0)->orderBy('sort_order')->isEnabled()->get();

        return view('FsView::operations.groups', compact('groups', 'firstGroups', 'parentGroup', 'typeModeLabels', 'permissionLabels', 'plugins', 'roles'));
    }

    public function store(Group $group, Request $request)
    {
        $group->parent_id = $request->parent_id ?? 0;
        $group->sort_order = $request->sort_order;
        $group->name = $request->names;
        $group->description = $request->descriptions;
        $group->cover_file_url = $request->cover_file_url;
        $group->banner_file_url = $request->banner_file_url;
        $group->privacy = $request->privacy;
        $group->visibility = $request->visibility;
        $group->follow_type = $request->follow_type;
        $group->follow_plugin_fskey = $request->follow_plugin_fskey;
        $group->is_recommend = $request->is_recommend;

        $requestPerms = $request->permissions;

        $permissions['private_whitelist_roles'] = $requestPerms['private_whitelist_roles'] ?? [];
        $permissions['can_publish'] = (bool) ($requestPerms['can_publish'] ?? 0);
        $permissions['publish_post'] = $requestPerms['publish_post'];
        $permissions['publish_post_roles'] = $requestPerms['publish_post_roles'] ?? [];
        $permissions['publish_post_review'] = (bool) ($requestPerms['publish_post_review'] ?? 0);
        $permissions['publish_comment'] = $requestPerms['publish_comment'];
        $permissions['publish_comment_roles'] = $requestPerms['publish_comment_roles'] ?? [];
        $permissions['publish_comment_review'] = (bool) ($requestPerms['publish_comment_review'] ?? 0);

        $group->permissions = $permissions;

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

        return $this->createSuccess();
    }

    public function update(Group $group, Request $request)
    {
        $group->parent_id = $request->parent_id ?? 0;
        $group->sort_order = $request->sort_order;
        $group->name = $request->names;
        $group->description = $request->descriptions;
        $group->cover_file_url = $request->cover_file_url;
        $group->banner_file_url = $request->banner_file_url;
        $group->privacy = $request->privacy;
        $group->visibility = $request->visibility;
        $group->follow_type = $request->follow_type;
        $group->follow_plugin_fskey = $request->follow_plugin_fskey;
        $group->is_recommend = $request->is_recommend;

        $requestPerms = $request->permissions;

        $permissions = $group->permissions;
        $permissions['private_whitelist_roles'] = $requestPerms['private_whitelist_roles'] ?? [];
        $permissions['can_publish'] = (bool) ($requestPerms['can_publish'] ?? 0);
        $permissions['publish_post'] = $requestPerms['publish_post'];
        $permissions['publish_post_roles'] = $requestPerms['publish_post_roles'] ?? [];
        $permissions['publish_post_review'] = (bool) ($requestPerms['publish_post_review'] ?? 0);
        $permissions['publish_comment'] = $requestPerms['publish_comment'];
        $permissions['publish_comment_roles'] = $requestPerms['publish_comment_roles'] ?? [];
        $permissions['publish_comment_review'] = (bool) ($requestPerms['publish_comment_review'] ?? 0);

        $group->permissions = $permissions;

        $group->admins()->sync($request->admin_ids);

        $group->save();

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

        return $this->updateSuccess();
    }

    public function updateStatus(Group $group)
    {
        $type = $group->is_enabled ? 'decrement' : 'increment';

        $group->is_enabled = ! $group->is_enabled;
        $group->save();

        static::subgroupCount($type, $group->parent_id);

        return $this->updateSuccess();
    }

    public static function subgroupCount(string $type, ?int $parentId = null): void
    {
        if (! $parentId) {
            return;
        }

        $group = Group::where('id', $parentId)->first();

        if ($type == 'increment') {
            $group?->increment('subgroup_count');
        } else {
            $group?->decrement('subgroup_count');
        }

        // parent group
        if ($group?->parent_id) {
            static::subgroupCount($type, $group->parent_id);
        }
    }

    public function mergeGroup(Group $group, Request $request)
    {
        $newGroupId = $request->group_id;

        if (! $newGroupId) {
            return back()->with('failure', __('FsLang::tips.select_box_tip_group'));
        }

        $postCount = $group->post_count;
        $commentCount = $group->comment_count;
        $postDigestCount = $group->post_digest_count;
        $commentDigestCount = $group->comment_digest_count;

        if ($postCount) {
            Group::where('id', $newGroupId)->increment('post_count', $postCount);
        }
        if ($commentCount) {
            Group::where('id', $newGroupId)->increment('comment_count', $commentCount);
        }
        if ($postDigestCount) {
            Group::where('id', $newGroupId)->increment('post_digest_count', $postDigestCount);
        }
        if ($commentDigestCount) {
            Group::where('id', $newGroupId)->increment('comment_digest_count', $commentDigestCount);
        }

        Post::where('group_id', $group->id)->update(['group_id' => $newGroupId]);
        PostLog::where('group_id', $group->id)->update(['group_id' => $newGroupId]);

        $group->delete();

        return $this->updateSuccess();
    }

    public function updateSortOrder(Group $group, Request $request)
    {
        $group->sort_order = $request->order;
        $group->save();

        return $this->updateSuccess();
    }

    public function updateRecommendSortOrder(Group $group, Request $request)
    {
        $group->recommend_sort_order = $request->order;
        $group->save();

        return $this->updateSuccess();
    }
}
