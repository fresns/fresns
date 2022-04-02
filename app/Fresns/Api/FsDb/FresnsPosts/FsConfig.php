<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsPosts;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'posts';

    // Configs item_key
    const IT_POSTS = 'it_posts';
    const CHECK_CONTENT = 'check_content';
    const POST_EDITOR_WORD_COUNT = 'post_editor_word_count';
    const POST_EDITOR_BRIEF_COUNT = 'post_editor_brief_count';
    const HASHTAG_SHOW = 'hashtag_show';
    const POSTS_COUNT = 'posts_count';
    const HASHTAGS_COUNT = 'hashtag_counts';

    // Whether you have permission to read
    const IS_ALLOW_1 = 1;

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'ids' => ['field' => 'id', 'op' => 'IN'],
        'pid' => ['field' => 'pid', 'op' => '='],
        'searchKey' => ['field' => 'title', 'op' => 'LIKE'],
        'searchKey' => ['field' => 'content', 'op' => 'LIKE'],
        'searchType' => ['field' => 'types', 'op' => 'LIKE'],
        'searchDigestType' => ['field' => 'digest_state', 'op' => 'in'],
        'searchStickyType' => ['field' => 'sticky_state', 'op' => 'in'],
        'searchUid' => ['field' => 'user_id', 'op' => '='],
        'searchGid' => ['field' => 'group_id', 'op' => '='],
        'mapId' => ['field' => 'map_id', 'op' => '='],
        'viewCountGt' => ['field' => 'view_count', 'op' => '>='],
        'viewCountLt' => ['field' => 'view_count', 'op' => '<='],
        'likeCountGt' => ['field' => 'like_count', 'op' => '>='],
        'likeCountLt' => ['field' => 'like_count', 'op' => '<='],
        'followCountGt' => ['field' => 'follow_count', 'op' => '>='],
        'followCountLt' => ['field' => 'follow_count', 'op' => '<='],
        'blockCountGt' => ['field' => 'block_count', 'op' => '>='],
        'blockCountLt' => ['field' => 'block_count', 'op' => '<='],
        'commentCountGt' => ['field' => 'comment_count ', 'op' => '>='],
        'commentCountLt' => ['field' => 'comment_count ', 'op' => '<='],
        'publishTimeGt' => ['field' => 'created_at', 'op' => '>='],
        'publishTimeLt' => ['field' => 'created_at', 'op' => '<='],
        'expired_at' => ['field' => 'created_at', 'op' => '<='],
    ];

    const APPEND_SEARCHABLE_FIELDS = [
        'searchKey' => ['field' => 'content', 'op' => 'LIKE'],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'pid' => 'pid',
        'user_id' => 'user_id',
        'group_id' => 'group_id',
        'types' => 'types',
        'title' => 'title',
        'content' => 'content',
        'is_brief' => 'is_brief',
        'sticky_state' => 'sticky_state',
        'digest_state' => 'digest_state',
        'is_anonymous' => 'is_anonymous',
        'is_allow' => 'is_allow',
        'more_json' => 'more_json',
        'map_service' => 'map_service',
        'map_latitude' => 'map_latitude',
        'map_longitude' => 'map_longitude',
        'map_scale' => 'map_scale',
        'map_poi' => 'map_poi',
        'view_count' => 'view_count',
        'like_count' => 'like_count',
        'follow_count' => 'follow_count',
        'block_count' => 'block_count',
        'comment_count' => 'comment_count',
        'comment_like_count' => 'comment_like_count',
        'latest_comment_at' => 'latest_comment_at',
        'is_enable' => 'is_enable',
    ];
}
