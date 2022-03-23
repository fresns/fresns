<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsGroups;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'groups';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'ids' => ['field' => 'id', 'op' => 'in'],
        'gid' => ['field' => 'gid', 'op' => '='],
        'parentId' => ['field' => 'parent_id', 'op' => '='],
        'recommend' => ['field' => 'is_recommend', 'op' => '='],
        'viewCountGt' => ['field' => 'view_count', 'op' => '>='],
        'viewCountLt' => ['field' => 'view_count', 'op' => '<='],
        'likeCountGt' => ['field' => 'like_count', 'op' => '>='],
        'likeCountLt' => ['field' => 'like_count', 'op' => '<='],
        'followCountGt' => ['field' => 'follow_count', 'op' => '>='],
        'followCountLt' => ['field' => 'follow_count', 'op' => '<='],
        'blockCountGt' => ['field' => 'block_count', 'op' => '>='],
        'blockCountLt' => ['field' => 'block_count', 'op' => '<='],
        'postCountGt' => ['field' => 'post_count', 'op' => '>='],
        'postCountLt' => ['field' => 'post_count', 'op' => '<='],
        'digestCountGt' => ['field' => 'digest_count', 'op' => '>='],
        'digestCountLt' => ['field' => 'digest_count', 'op' => '<='],
        'createdTimeGt' => ['field' => 'created_at', 'op' => '>='],
        'createdTimeLt' => ['field' => 'created_at', 'op' => '<='],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'gid' => 'gid',
        'parent_id' => 'parent_id',
        'name' => 'name',
        'description' => 'description',
        'type' => 'type',
        'type_mode' => 'type_mode',
        'type_find' => 'type_find',
        'type_follow' => 'type_follow',
        'plugin_unikey' => 'plugin_unikey',
        'cover_file_id' => 'cover_file_id',
        'cover_file_url' => 'cover_file_url',
        'banner_file_id' => 'banner_file_id',
        'banner_file_url' => 'banner_file_url',
        'rank_num' => 'rank_num',
        'is_recommend' => 'is_recommend',
        'recom_rank_num' => 'recom_rank_num',
        'allow_view' => 'allow_view',
        'allow_post' => 'allow_post',
        'allow_comment' => 'allow_comment',
        'admin_users' => 'admin_users',
        'permission' => 'permission',
        'view_count' => 'view_count',
        'like_count' => 'like_count',
        'follow_count' => 'follow_count',
        'block_count' => 'block_count',
        'post_count' => 'post_count',
        'digest_count' => 'digest_count',
        'is_enable' => 'is_enable',
    ];

    // Operation Config
    const RECOMMEND_OPTION = [
        ['key' => 1, 'text' => 'Not recommended'],
        ['key' => 2, 'text' => 'Recommend'],
    ];

    // Choose Privacy
    // 1.Public: Anyone can see who's in the group and what they post.
    // 2.Private: Only users can see who's in the group and what they post.
    const TYPE_MODE = [
        ['key' => 1, 'text' => 'Public'],
        ['key' => 2, 'text' => 'Private'],
    ];

    // Follow the way
    // 1.Fresns: Main program API operable follow
    // 2.Plugin: Can only be operated via plugin follow
    const TYPE_FOLLOW = [
        ['key' => 1, 'text' => 'Fresns'],
        ['key' => 2, 'text' => 'Plugin'],
    ];

    // Hide Group
    // 1.Visible: Anyone can find this group.
    // 2.Hidden: Only users can find this group.
    const TYPE_FIND = [
        ['key' => 1, 'text' => 'Visible'],
        ['key' => 2, 'text' => 'Hidden'],
    ];

    // Who Can Post
    const PUBLISH_POST = [
        ['key' => 1, 'text' => 'All Users'],
        ['key' => 2, 'text' => 'Anyone in the group'],
        ['key' => 3, 'text' => 'Specified role users only'],
    ];
}
