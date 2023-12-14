<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use App\Models\User;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    // search users
    public function searchUsers(Request $request)
    {
        $keyword = $request->keyword;

        $users = [];
        if ($keyword) {
            $users = User::where('username', 'like', "%$keyword%")->orWhere('nickname', 'like', "%$keyword%")->paginate();
        }

        return response()->json($users);
    }

    // update language
    public function languageUpdate($itemKey, Request $request)
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
        $itemValue[$request->langTag] = $request->langContent;

        $config->item_value = $itemValue;
        $config->save();

        return $this->updateSuccess();
    }

    // update batch language
    public function languageBatchUpdate($itemKey, Request $request)
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

        return $this->updateSuccess();
    }
}
