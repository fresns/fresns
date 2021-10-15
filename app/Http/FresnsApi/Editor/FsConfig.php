<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Editor;

class FsConfig
{
    const SITE_MODEL = 'site_mode';
    const PRIVATE = 'private';
    const COMMENT_EDITOR_BRIEF_COUNT = 'comment_editor_brief_count';
    const COMMENT_EDITOR_WORD_COUNT = 'comment_editor_word_count';
    const POST_EDITOR_WORD_COUNT = 'post_editor_word_count';
    const POST_EDITOR_BRIEF_COUNT = 'post_editor_brief_count';

    // Query Mode
    const QUERY_TYPE_DB_QUERY = 'db_query';  // Queries with join config support
    const QUERY_TYPE_SQL_QUERY = 'sql_query'; // Native SQL queries

    const OBJECT_DEFAIL = 1;
    const OBJECT_SUCCESS = 2;

    // api not affected by special rules
    const URI_NOT_IN_RULE = [
        '/api/fresns/editor/publish',
        '/api/fresns/editor/submit',
    ];

    // Update Log Config
    const FORM_FIELDS_UPDATE_LOGS_MAP = [
        'gid' => 'group_id',
        'types' => 'types',
        'title' => 'title',
        'content' => 'content',
        'isMarkdown' => 'is_markdown',
        'isAnonymous' => 'is_anonymous',
        'isPluginEditor' => 'is_plugin_editor',
        'editorUnikey' => 'editor_unikey',
        'memberListJson' => 'member_list_json',
        'commentSetJson' => 'comment_set_json',
        'allowJson' => 'allow_json',
        'locationJson' => 'location_json',
        'filesJson' => 'files_json',
        'extendsJson' => 'extends_json',
    ];
}
