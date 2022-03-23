<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Models\Config;

class ConfigUtility
{
    /**
     * This function adds the fresns config items to the database.
     *
     * @param fresnsConfigItems an array of items to be added to the config table.
     */
    public static function addFresnsConfigItems($fresnsConfigItems)
    {
        foreach ($fresnsConfigItems as $item) {
            $config = Config::where('item_key', '=', $item['item_key'])->first();
            if (empty($config)) {
                Config::insert($item);
            }
        }
    }

    /**
     * This function removes the fresns config items from the database.
     *
     * @param fresnsConfigKeys an array of the keys of the fresns config items to be removed.
     */
    public static function removeFresnsConfigItems($fresnsConfigKeys)
    {
        foreach ($fresnsConfigKeys as $item) {
            Config::where('item_key', '=', $item)->forceDelete();
        }
    }
}
