<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Content;

class FsConfig
{
    // Avatar Substitute
    const DEFAULT_AVATAR = 'default_avatar';
    const ANONYMOUS_AVATAR = 'anonymous_avatar';
    const DEACTIVATE_AVATAR = 'deactivate_avatar';

    // Content Naming
    const GROUP_NAME = 'group_name';
    const HASHTAG_NAME = 'hashtag_name';
    const POST_NAME = 'post_name';
    const COMMENT_NAME = 'comment_name';

    // Behavior Naming
    const LIKE_GROUP_NAME = 'like_group_name';
    const FOLLOW_GROUP_NAME = 'follow_group_name';
    const SHIELD_GROUP_NAME = 'shield_group_name';

    const LIKE_HASHTAG_NAME = 'like_hashtag_name';
    const FOLLOW_HASHTAG_NAME = 'follow_hashtag_name';
    const SHIELD_HASHTAG_NAME = 'shield_hashtag_name';

    const LIKE_POST_NAME = 'like_post_name';
    const FOLLOW_POST_NAME = 'follow_post_name';
    const SHIELD_POST_NAME = 'shield_post_name';

    const LIKE_COMMENT_NAME = 'like_comment_name';
    const FOLLOW_COMMENT_NAME = 'follow_comment_name';
    const SHIELD_COMMENT_NAME = 'shield_comment_name';

    // Behavior Settings
    const LIKE_GROUP_SETTING = 'like_group_setting';
    const FOLLOW_GROUP_SETTING = 'follow_group_setting';
    const SHIELD_GROUP_SETTING = 'shield_group_setting';

    const LIKE_HASHTAG_SETTING = 'like_hashtag_setting';
    const FOLLOW_HASHTAG_SETTING = 'follow_hashtag_setting';
    const SHIELD_HASHTAG_SETTING = 'shield_hashtag_setting';

    const LIKE_POST_SETTING = 'like_post_setting';
    const FOLLOW_POST_SETTING = 'follow_post_setting';
    const SHIELD_POST_SETTING = 'shield_post_setting';

    const LIKE_COMMENT_SETTING = 'like_comment_setting';
    const FOLLOW_COMMENT_SETTING = 'follow_comment_setting';
    const SHIELD_COMMENT_SETTING = 'shield_comment_setting';

    // Interaction
    const POST_HOT = 'post_hot';
    const COMMENT_PREVIEW = 'comment_preview';
    const POST_DETAIL_SERVICE = 'post_detail_service';

    // Content Edit
    const POST_EDIT = 'post_edit';
    const POST_EDIT_TIMELIMIT = 'post_edit_timelimit';
    const POST_EDIT_STICKY = 'post_edit_sticky';
    const POST_EDIT_ESSENCE = 'post_edit_essence';
    const COMMENT_EDIT = 'comment_edit';
    const COMMENT_EDIT_TIMELIMIT = 'comment_edit_timelimit';
    const COMMENT_EDIT_STICKY = 'comment_edit_sticky';

    // Site Config
    const SITE_MODEL = 'site_mode';
    const SITE_DOMAIN = 'site_domain';
    const PRIVATE = 'private';

    // Query Mode
    const QUERY_TYPE_DB_QUERY = 'db_query';  // Queries with join config support
    const QUERY_TYPE_SQL_QUERY = 'sql_query'; // Native SQL Queries
}
