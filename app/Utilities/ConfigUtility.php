<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Models\CodeMessage;
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

    /**
     * > Get the message of the specified code in the specified language.
     *
     * @param int code The code of the message you want to get.
     * @param string unikey The unique key of the plugin, which is the same as the plugin name.
     * @param string langTag The language tag, such as en-US, zh-CN, etc.
     * @return The message associated with the code.
     */
    public static function getCodeMessage(int $code, string $unikey = '', string $langTag = '')
    {
        $unikey = $unikey ?: 'Fresns';

        if (empty($langTag)) {
            $langTag = Config::where('item_key', 'default_language')->value('item_value');
        }

        $message = CodeMessage::where('plugin_unikey', $unikey)->where('code', $code)->where('lang_tag', $langTag)->value('message');

        return $message ?? 'Unknown Error';
    }
}
