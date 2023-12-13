<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class SocialController extends Controller
{
    public function show()
    {
        // config keys
        $configKeys = [
            'mention_status',
            'hashtag_status',
            'hashtag_format',
            'hashtag_length',
            'hashtag_regexp',
            'conversation_status',
            'conversation_files',
            'conversation_file_upload_type',
            'view_posts_by_timelines',
            'view_comments_by_timelines',
            'view_posts_by_nearby',
            'view_comments_by_nearby',
            'nearby_length_km',
            'nearby_length_mi',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        return view('FsView::systems.social', compact('params'));
    }

    public function update(Request $request)
    {
        $configKeys = [
            'mention_status',
            'hashtag_status',
            'hashtag_format',
            'hashtag_length',
            'hashtag_regexp',
            'conversation_status',
            'conversation_files',
            'conversation_file_upload_type',
            'view_posts_by_timelines',
            'view_comments_by_timelines',
            'view_posts_by_nearby',
            'view_comments_by_nearby',
            'nearby_length_km',
            'nearby_length_mi',
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

    public function updateHashtagRegexp(Request $request)
    {
        $hashtagRegexp = Config::where('item_key', 'hashtag_regexp')->first();

        $hashtagRegexp->item_value = $request->hashtagRegexp;
        $hashtagRegexp->item_type = 'object';
        $hashtagRegexp->save();

        return $this->updateSuccess();
    }
}
