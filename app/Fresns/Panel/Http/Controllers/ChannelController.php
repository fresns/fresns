<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\StrHelper;
use App\Models\Config;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function index()
    {
        // config keys
        $configKeys = [
            'default_homepage',

            'website_portal_path',
            'website_user_path',
            'website_group_path',
            'website_hashtag_path',
            'website_geotag_path',
            'website_post_path',
            'website_comment_path',
            'website_group_detail_path',
            'website_hashtag_detail_path',
            'website_geotag_detail_path',

            'channel_portal_name',
            'channel_portal_seo',

            'channel_user_name',
            'channel_user_seo',
            'channel_user_query_state',
            'channel_user_query_config',
            'channel_user_list_name',
            'channel_user_list_seo',
            'channel_user_list_query_state',
            'channel_user_list_query_config',
            'channel_likes_users_name',
            'channel_dislikes_users_name',
            'channel_following_users_name',
            'channel_blocking_users_name',

            'channel_group_name',
            'channel_group_seo',
            'channel_group_type',
            'channel_group_query_state',
            'channel_group_query_config',
            'channel_group_list_name',
            'channel_group_list_seo',
            'channel_group_list_query_state',
            'channel_group_list_query_config',
            'channel_group_detail_type',
            'channel_likes_groups_name',
            'channel_dislikes_groups_name',
            'channel_following_groups_name',
            'channel_blocking_groups_name',

            'channel_hashtag_name',
            'channel_hashtag_seo',
            'channel_hashtag_query_state',
            'channel_hashtag_query_config',
            'channel_hashtag_list_name',
            'channel_hashtag_list_seo',
            'channel_hashtag_list_query_state',
            'channel_hashtag_list_query_config',
            'channel_hashtag_detail_type',
            'channel_likes_hashtags_name',
            'channel_dislikes_hashtags_name',
            'channel_following_hashtags_name',
            'channel_blocking_hashtags_name',

            'channel_geotag_name',
            'channel_geotag_seo',
            'channel_geotag_query_state',
            'channel_geotag_query_config',
            'channel_geotag_list_name',
            'channel_geotag_list_seo',
            'channel_geotag_list_query_state',
            'channel_geotag_list_query_config',
            'channel_geotag_detail_type',
            'channel_likes_geotags_name',
            'channel_dislikes_geotags_name',
            'channel_following_geotags_name',
            'channel_blocking_geotags_name',

            'channel_post_name',
            'channel_post_seo',
            'channel_post_query_state',
            'channel_post_query_config',
            'channel_post_list_name',
            'channel_post_list_seo',
            'channel_post_list_query_state',
            'channel_post_list_query_config',
            'channel_likes_posts_name',
            'channel_dislikes_posts_name',
            'channel_following_posts_name',
            'channel_blocking_posts_name',

            'channel_comment_name',
            'channel_comment_seo',
            'channel_comment_query_state',
            'channel_comment_query_config',
            'channel_comment_list_name',
            'channel_comment_list_seo',
            'channel_comment_list_query_state',
            'channel_comment_list_query_config',
            'channel_likes_comments_name',
            'channel_dislikes_comments_name',
            'channel_following_comments_name',
            'channel_blocking_comments_name',

            'channel_timeline_name',
            'channel_timeline_type',
            'channel_timeline_posts_name',
            'channel_timeline_user_posts_name',
            'channel_timeline_group_posts_name',
            'channel_timeline_comments_name',
            'channel_timeline_user_comments_name',
            'channel_timeline_group_comments_name',

            'channel_nearby_name',
            'channel_nearby_type',
            'channel_nearby_posts_name',
            'channel_nearby_comments_name',

            'channel_me_name',
            'channel_me_wallet_name',
            'channel_me_extcredits_name',
            'channel_me_drafts_name',
            'channel_me_users_name',
            'channel_me_settings_name',

            'channel_conversations_name',
            'channel_notifications_name',
            'channel_notifications_all_name',
            'channel_notifications_systems_name',
            'channel_notifications_recommends_name',
            'channel_notifications_likes_name',
            'channel_notifications_dislikes_name',
            'channel_notifications_follows_name',
            'channel_notifications_blocks_name',
            'channel_notifications_mentions_name',
            'channel_notifications_comments_name',
            'channel_notifications_quotes_name',

            'channel_search_name',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $params = [];
        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        // language keys
        $langKeys = [
            'channel_portal_name',
            'channel_portal_seo',

            'channel_user_name',
            'channel_user_seo',
            'channel_user_list_name',
            'channel_user_list_seo',
            'channel_likes_users_name',
            'channel_dislikes_users_name',
            'channel_following_users_name',
            'channel_blocking_users_name',

            'channel_group_name',
            'channel_group_seo',
            'channel_group_list_name',
            'channel_group_list_seo',
            'channel_likes_groups_name',
            'channel_dislikes_groups_name',
            'channel_following_groups_name',
            'channel_blocking_groups_name',

            'channel_hashtag_name',
            'channel_hashtag_seo',
            'channel_hashtag_list_name',
            'channel_hashtag_list_seo',
            'channel_likes_hashtags_name',
            'channel_dislikes_hashtags_name',
            'channel_following_hashtags_name',
            'channel_blocking_hashtags_name',

            'channel_geotag_name',
            'channel_geotag_seo',
            'channel_geotag_list_name',
            'channel_geotag_list_seo',
            'channel_likes_geotags_name',
            'channel_dislikes_geotags_name',
            'channel_following_geotags_name',
            'channel_blocking_geotags_name',

            'channel_post_name',
            'channel_post_seo',
            'channel_post_list_name',
            'channel_post_list_seo',
            'channel_likes_posts_name',
            'channel_dislikes_posts_name',
            'channel_following_posts_name',
            'channel_blocking_posts_name',

            'channel_comment_name',
            'channel_comment_seo',
            'channel_comment_list_name',
            'channel_comment_list_seo',
            'channel_likes_comments_name',
            'channel_dislikes_comments_name',
            'channel_following_comments_name',
            'channel_blocking_comments_name',

            'channel_timeline_name',
            'channel_timeline_posts_name',
            'channel_timeline_user_posts_name',
            'channel_timeline_group_posts_name',
            'channel_timeline_comments_name',
            'channel_timeline_user_comments_name',
            'channel_timeline_group_comments_name',

            'channel_nearby_name',
            'channel_nearby_posts_name',
            'channel_nearby_comments_name',

            'channel_me_name',
            'channel_me_wallet_name',
            'channel_me_extcredits_name',
            'channel_me_drafts_name',
            'channel_me_users_name',
            'channel_me_settings_name',

            'channel_conversations_name',
            'channel_notifications_name',
            'channel_notifications_all_name',
            'channel_notifications_systems_name',
            'channel_notifications_recommends_name',
            'channel_notifications_likes_name',
            'channel_notifications_dislikes_name',
            'channel_notifications_follows_name',
            'channel_notifications_blocks_name',
            'channel_notifications_mentions_name',
            'channel_notifications_comments_name',
            'channel_notifications_quotes_name',

            'channel_search_name',
        ];

        $defaultLangParams = [];
        foreach ($langKeys as $langKey) {
            $defaultLangParams[$langKey] = StrHelper::languageContent($params[$langKey]);
        }

        return view('FsView::clients.channels', compact('params', 'defaultLangParams'));
    }

    // update
    public function update(string $type, Request $request)
    {
        $configKeys = [
            "channel_{$type}_type",
            "channel_{$type}_query_state",
            "channel_{$type}_query_config",
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
                continue;
            }

            $value = match ($configKey) {
                "channel_{$type}_type" => $request->index_type,
                "channel_{$type}_query_state" => $request->query_state,
                "channel_{$type}_query_config" => $request->query_config,
                default => null,
            };

            $config->item_value = $value;
            $config->save();
        }

        return $this->updateSuccess();
    }
}
