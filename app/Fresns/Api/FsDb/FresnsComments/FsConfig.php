<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsComments;

use App\Fresns\Api\Base\Config\BaseConfig;

class FsConfig extends BaseConfig
{
    // Main Table
    const CFG_TABLE = 'comments';

    // Configs item_key
    const IT_COMMENTS = 'it_comments';
    const COMMENT_EDITOR_BRIEF_COUNT = 'comment_editor_brief_count';
    const COMMENT_EDITOR_WORD_COUNT = 'comment_editor_word_count';
    const HASHTAG_SHOW = 'hashtag_show';
    const COMMENTS_COUNT = 'comments_count';
    const HASHTAGS_COUNT = 'hashtags_count';

    // Additional search columns in the main table
    const ADDED_SEARCHABLE_FIELDS = [
        'cid' => ['field' => 'cid', 'op' => '='],
        'ids' => ['field' => 'id', 'op' => 'IN'],
    ];

    // Tree Search Rule
    protected $treeSearchRule = [
        'id' => ['field' => 'id', 'op' => '='],
    ];

    // Model Usage - Form Mapping
    const FORM_FIELDS_MAP = [
        'id' => 'id',
        'cid' => 'cid',
        'post_id' => 'post_id',
        'parent_id' => 'parent_id',
        'user_id' => 'user_id',
        'types' => 'types',
        'content' => 'content',
        'is_brief' => 'is_brief',
        'is_anonymous' => 'is_anonymous',
        'is_lbs' => 'is_lbs',
        'is_sticky' => 'is_sticky',
        'more_json' => 'more_json',
        'like_count' => 'like_count',
        'follow_count' => 'follow_count',
        'block_count' => 'block_count',
        'comment_count' => 'comment_count',
        'comment_like_count' => 'comment_like_count',
        'latest_edit_at' => 'latest_edit_at',
        'latest_comment_at' => 'latest_comment_at',
        'is_enable' => 'is_enable',
    ];
}
