<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\CacheHelper;
use App\Helpers\StrHelper;
use App\Models\Config;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    // update batch languages
    public function updateLanguages(string $itemKey, Request $request)
    {
        $config = Config::where('item_key', $itemKey)->first();

        if (! $config) {
            $config = new Config();

            $config->fill([
                'item_key' => $itemKey,
                'item_type' => 'object',
            ]);
        }

        $itemValue = $config->item_value;

        foreach ($request->languages as $langTag => $langContent) {
            $itemValue[$langTag] = $langContent;
        }

        $config->item_value = $itemValue;
        $config->save();

        CacheHelper::forgetFresnsConfigs($itemKey);

        return $this->updateSuccess();
    }

    // update language
    public function updateLanguage(string $itemKey, string $langTag, Request $request)
    {
        $config = Config::where('item_key', $itemKey)->first();

        if (! $config) {
            $config = new Config();

            $config->fill([
                'item_key' => $itemKey,
                'item_type' => 'object',
            ]);
        }

        $itemValue = $config->item_value;
        $itemValue[$langTag] = $request->langContent;

        $config->item_value = $itemValue;
        $config->save();

        CacheHelper::forgetFresnsConfigs($itemKey);

        return $this->updateSuccess();
    }

    // update config item
    public function updateItem(string $itemKey, Request $request)
    {
        $config = Config::where('item_key', $itemKey)->first();

        if (! $config) {
            $config = new Config();

            $config->fill([
                'item_key' => $itemKey,
                'item_type' => $request->itemType ?? 'string',
            ]);
        }

        $config->item_value = $request->itemValue;
        $config->item_type = $request->itemType ?? 'string';
        $config->save();

        CacheHelper::forgetFresnsConfigs($itemKey);

        if ($itemKey == 'website_engine_status') {
            CacheHelper::clearConfigCache('fresnsRoute');
        }

        return $this->updateSuccess();
    }

    // search users
    public function searchUsers(Request $request)
    {
        $keyword = $request->keyword;

        $users = [];
        if ($keyword) {
            $users = User::whereAny(['username', 'nickname'], 'LIKE', "%$keyword%")->paginate();
        }

        return response()->json($users);
    }

    // search groups
    public function searchGroups(Request $request)
    {
        $id = $request->groupId;

        $groups = [];
        if ($id) {
            $allGroups = Group::where('parent_id', $id)->orderBy('sort_order')->isEnabled()->get();

            foreach ($allGroups as $group) {
                $item['id'] = $group->id;
                $item['name'] = StrHelper::languageContent($group->name);

                $groups[] = $item;
            }
        }

        return response()->json($groups);
    }
}
