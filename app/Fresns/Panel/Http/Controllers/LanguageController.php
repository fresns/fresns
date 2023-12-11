<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function update($itemKey, Request $request)
    {
        $config = Config::where('item_key', $itemKey)->first();

        if (! $config) {
            // create but no content
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

    public function batchUpdate($itemKey, Request $request)
    {
        Config::updateOrCreate([
            'item_key' => $itemKey,
        ], [
            'item_value' => $request->languages,
            'item_type' => 'object',
        ]);

        return $this->updateSuccess();
    }
}
