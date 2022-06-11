<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class InteractiveController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'hashtag_show',
            'post_hot',
            'comment_preview',
            'nearby_length_km',
            'nearby_length_mi',
            'dialog_status',
            'dialog_files',
            'like_user_setting',
            'like_group_setting',
            'like_hashtag_setting',
            'like_post_setting',
            'like_comment_setting',
            'dislike_user_setting',
            'dislike_group_setting',
            'dislike_hashtag_setting',
            'dislike_post_setting',
            'dislike_comment_setting',
            'follow_user_setting',
            'follow_group_setting',
            'follow_hashtag_setting',
            'follow_post_setting',
            'follow_comment_setting',
            'block_user_setting',
            'block_group_setting',
            'block_hashtag_setting',
            'block_post_setting',
            'block_comment_setting',
            'it_posts',
            'it_comments',
            'it_likers',
            'it_dislikers',
            'it_followers',
            'it_blockers',
            'it_like_users',
            'it_like_groups',
            'it_like_hashtags',
            'it_like_posts',
            'it_like_comments',
            'it_dislike_users',
            'it_dislike_groups',
            'it_dislike_hashtags',
            'it_dislike_posts',
            'it_dislike_comments',
            'it_follow_users',
            'it_follow_groups',
            'it_follow_hashtags',
            'it_follow_posts',
            'it_follow_comments',
            'it_block_users',
            'it_block_groups',
            'it_block_hashtags',
            'it_block_posts',
            'it_block_comments',
            'it_home_list',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        return view('FsView::operations.interactive', compact('params'));
    }

    public function update(Request $request)
    {
        $configKeys = [
            'hashtag_show',
            'post_hot',
            'comment_preview',
            'nearby_length_km',
            'nearby_length_mi',
            'dialog_status',
            'dialog_files',
            'like_user_setting',
            'like_group_setting',
            'like_hashtag_setting',
            'like_post_setting',
            'like_comment_setting',
            'dislike_user_setting',
            'dislike_group_setting',
            'dislike_hashtag_setting',
            'dislike_post_setting',
            'dislike_comment_setting',
            'follow_user_setting',
            'follow_group_setting',
            'follow_hashtag_setting',
            'follow_post_setting',
            'follow_comment_setting',
            'block_user_setting',
            'block_group_setting',
            'block_hashtag_setting',
            'block_post_setting',
            'block_comment_setting',
            'it_posts',
            'it_comments',
            'it_likers',
            'it_dislikers',
            'it_followers',
            'it_blockers',
            'it_like_users',
            'it_like_groups',
            'it_like_hashtags',
            'it_like_posts',
            'it_like_comments',
            'it_dislike_users',
            'it_dislike_groups',
            'it_dislike_hashtags',
            'it_dislike_posts',
            'it_dislike_comments',
            'it_follow_users',
            'it_follow_groups',
            'it_follow_hashtags',
            'it_follow_posts',
            'it_follow_comments',
            'it_block_users',
            'it_block_groups',
            'it_block_hashtags',
            'it_block_posts',
            'it_block_comments',
            'it_home_list',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
            }

            if (! $request->has($configKey)) {
                $config->setDefaultValue();
                $config->save();
                continue;
            }

            $config->item_value = $request->$configKey;
            $config->save();
        }

        return $this->updateSuccess();
    }
}
