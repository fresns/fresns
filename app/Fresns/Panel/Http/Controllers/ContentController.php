<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateContentConfigRequest;
use App\Helpers\StrHelper;
use App\Models\Config;

class ContentController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'group_name',
            'hashtag_name',
            'geotag_name',
            'post_name',
            'comment_name',
            'publish_post_name',
            'publish_comment_name',
            'mention_status',
            'mention_number',
            'hashtag_status',
            'hashtag_format',
            'hashtag_length',
            'hashtag_number',
            'hashtag_regexp',
            'view_posts_by_timelines',
            'view_comments_by_timelines',
            'view_posts_by_nearby',
            'view_comments_by_nearby',
            'nearby_length_km',
            'nearby_length_mi',
            'post_brief_length',
            'comment_brief_length',
            'comment_visibility_rule',
            'preview_post_like_users',
            'preview_post_comments',
            'preview_post_comments_type',
            'preview_post_comments_threshold',
            'preview_comment_like_users',
            'preview_comment_replies',
            'preview_comment_replies_type',
            'post_edit',
            'post_edit_time_limit',
            'post_edit_sticky_limit',
            'post_edit_digest_limit',
            'post_delete',
            'post_delete_sticky_limit',
            'post_delete_digest_limit',
            'comment_edit',
            'comment_edit_time_limit',
            'comment_edit_sticky_limit',
            'comment_edit_digest_limit',
            'comment_delete',
            'comment_delete_sticky_limit',
            'comment_delete_digest_limit',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $params = [];
        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        // language keys
        $langKeys = [
            'group_name',
            'hashtag_name',
            'geotag_name',
            'post_name',
            'comment_name',
            'publish_post_name',
            'publish_comment_name',
        ];

        $defaultLangParams = [];
        foreach ($langKeys as $langKey) {
            $defaultLangParams[$langKey] = StrHelper::languageContent($params[$langKey]);
        }

        return view('FsView::operations.content', compact('params', 'defaultLangParams'));
    }

    public function update(UpdateContentConfigRequest $request)
    {
        $configKeys = [
            'mention_status',
            'mention_number',
            'hashtag_status',
            'hashtag_format',
            'hashtag_length',
            'hashtag_number',
            'view_posts_by_timelines',
            'view_comments_by_timelines',
            'view_posts_by_nearby',
            'view_comments_by_nearby',
            'nearby_length_km',
            'nearby_length_mi',
            'post_brief_length',
            'comment_brief_length',
            'comment_visibility_rule',
            'profile_default_homepage',
            'preview_post_like_users',
            'preview_post_comments',
            'preview_post_comments_type',
            'preview_post_comments_threshold',
            'preview_comment_like_users',
            'preview_comment_replies',
            'preview_comment_replies_type',
            'post_edit',
            'post_edit_time_limit',
            'post_edit_sticky_limit',
            'post_edit_digest_limit',
            'post_delete',
            'post_delete_sticky_limit',
            'post_delete_digest_limit',
            'comment_edit',
            'comment_edit_time_limit',
            'comment_edit_sticky_limit',
            'comment_edit_digest_limit',
            'comment_delete',
            'comment_delete_sticky_limit',
            'comment_delete_digest_limit',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configKeys as $configKey) {
            $config = $configs->where('item_key', $configKey)->first();
            if (! $config) {
                continue;
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
