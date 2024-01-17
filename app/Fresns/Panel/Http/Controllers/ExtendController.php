<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\App;
use App\Models\Config;
use Illuminate\Http\Request;

class ExtendController extends Controller
{
    public function contentHandlerIndex()
    {
        // config keys
        $configKeys = [
            'ip_service',
            'map_service',
            'notifications_service',
            'content_review_service',
            'post_list_service',
            'post_timelines_service',
            'post_nearby_service',
            'comment_list_service',
            'comment_timelines_service',
            'comment_nearby_service',
            'post_detail_service',
            'comment_detail_service',
            'search_users_service',
            'search_groups_service',
            'search_hashtags_service',
            'search_geotags_service',
            'search_posts_service',
            'search_comments_service',
        ];
        $configs = Config::whereIn('item_key', $configKeys)->get();

        foreach ($configs as $config) {
            $params[$config->item_key] = $config->item_value;
        }

        $panelUsages = [
            'extendIp',
            'extendMap',
            'extendReview',
            'extendData',
            'extendNotification',
            'extendSearch',
        ];
        $plugins = App::type(App::TYPE_PLUGIN)->get();
        $pluginParams = [];
        foreach ($panelUsages as $usage) {
            $pluginParams[$usage] = $plugins->filter(function ($plugin) use ($usage) {
                return in_array($usage, $plugin->panel_usages);
            });
        }

        return view('FsView::extends.content-handler', compact('pluginParams', 'params'));
    }

    public function contentHandlerUpdate(Request $request)
    {
        // config keys
        $configKeys = [
            'ip_service',
            'map_service',
            'notifications_service',
            'content_review_service',
            'post_list_service',
            'post_timelines_service',
            'post_nearby_service',
            'comment_list_service',
            'comment_timelines_service',
            'comment_nearby_service',
            'post_detail_service',
            'comment_detail_service',
            'search_users_service',
            'search_groups_service',
            'search_hashtags_service',
            'search_geotags_service',
            'search_posts_service',
            'search_comments_service',
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

    public function commandWordsIndex()
    {
        $config = Config::where('item_key', 'interface_command_words')->first();

        $commandWords = $config?->item_value ?? [];

        $plugins = App::type(App::TYPE_PLUGIN)->get();

        return view('FsView::extends.command-words', compact('commandWords', 'plugins'));
    }

    public function commandWordsStore(Request $request)
    {
        $config = Config::where('item_key', 'interface_command_words')->first();

        $itemArr = $config?->item_value ?? [];

        $found = false;
        foreach ($itemArr as $item) {
            if ($item['fskey'] == $request->fskey && $item['cmdWord'] == $request->cmdWord) {
                $found = true;
                break;
            }
        }

        if (! $found) {
            $itemArr[] = [
                'fskey' => $request->fskey,
                'cmdWord' => $request->cmdWord,
            ];
        }

        $config->update([
            'item_value' => $itemArr,
        ]);

        return $this->createSuccess();
    }

    public function commandWordsDestroy(Request $request)
    {
        $config = Config::where('item_key', 'interface_command_words')->first();

        $itemArr = $config?->item_value ?? [];

        $fskey = $request->fskey;
        $cmdWord = $request->cmdWord;

        $newItemArr = array_filter($itemArr, function ($item) use ($fskey, $cmdWord) {
            return ! ($item['fskey'] == $fskey && $item['cmdWord'] == $cmdWord);
        });

        $newItemArr = array_values($newItemArr);

        $config->update([
            'item_value' => $newItemArr,
        ]);

        return $this->deleteSuccess();
    }
}
