<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateGeneralRequest;
use App\Models\Config;
use App\Models\Language;

class RenameController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'user_name',
            'user_uid_name',
            'user_username_name',
            'user_nickname_name',
            'user_role_name',
            'user_bio_name',
            'group_name',
            'hashtag_name',
            'post_name',
            'comment_name',
            'publish_post_name',
            'publish_comment_name',
            'like_user_name',
            'like_group_name',
            'like_hashtag_name',
            'like_post_name',
            'like_comment_name',
            'dislike_user_name',
            'dislike_group_name',
            'dislike_hashtag_name',
            'dislike_post_name',
            'dislike_comment_name',
            'follow_user_name',
            'follow_group_name',
            'follow_hashtag_name',
            'follow_post_name',
            'follow_comment_name',
            'block_user_name',
            'block_group_name',
            'block_hashtag_name',
            'block_post_name',
            'block_comment_name',
        ];

        $configs = Config::whereIn('item_key', $configKeys)
            ->with('languages')
            ->get();

        $configs = $configs->mapWithKeys(function ($config) {
            return [$config->item_key => $config];
        });

        $langKeys = $configKeys;

        $defaultLangParams = Language::ofConfig()
            ->whereIn('table_key', $langKeys)
            ->where('lang_tag', $this->defaultLanguage)
            ->pluck('lang_content', 'table_key');

        return view('FsView::operations.rename', compact('configs', 'defaultLangParams'));
    }

    public function update(UpdateGeneralRequest $request)
    {
        $configKeys = [
            'user_name',
            'user_uid_name',
            'user_username_name',
            'user_nickname_name',
            'user_role_name',
            'user_bio_name',
            'group_name',
            'hashtag_name',
            'post_name',
            'comment_name',
            'publish_post_name',
            'publish_comment_name',
            'like_user_name',
            'like_group_name',
            'like_hashtag_name',
            'like_post_name',
            'like_comment_name',
            'dislike_user_name',
            'dislike_group_name',
            'dislike_hashtag_name',
            'dislike_post_name',
            'dislike_comment_name',
            'follow_user_name',
            'follow_group_name',
            'follow_hashtag_name',
            'follow_post_name',
            'follow_comment_name',
            'block_user_name',
            'block_group_name',
            'block_hashtag_name',
            'block_post_name',
            'block_comment_name',
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
