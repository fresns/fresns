<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Models\CodeMessage;
use App\Models\Config;
use App\Models\Language;
use App\Models\SessionLog;

class ConfigUtility
{
    // add config items
    public static function addFresnsConfigItems(array $fresnsConfigItems)
    {
        foreach ($fresnsConfigItems as $item) {
            $config = Config::where('item_key', '=', $item['item_key'])->first();
            if (empty($config)) {
                Config::insert($item);

                if ($item['is_multilingual'] ?? null) {
                    $fresnsLangItems = [
                        'table_name' => 'configs',
                        'table_column' => 'item_value',
                        'table_id' => null,
                        'table_key' => $item['item_key'],
                        'language_values' => $item['language_values'],
                    ];
                    ConfigUtility::changeFresnsLanguageItems($fresnsLangItems);
                }
            }
        }
    }

    // remove config items
    public static function removeFresnsConfigItems(array $fresnsConfigKeys)
    {
        foreach ($fresnsConfigKeys as $item) {
            Config::where('item_key', '=', $item)->where('is_custom', 1)->forceDelete();
        }
    }

    // change config items
    public static function changeFresnsConfigItems(array $fresnsConfigItems)
    {
        foreach($fresnsConfigItems as $item) {
            Config::updateOrCreate([
                    'item_key' => $item['item_key']
                ],
                collect($item)->only('item_key', 'item_value', 'item_type', 'item_tag', 'is_multilingual', 'is_api')->toArray()
            );

            if ($item['is_multilingual'] ?? null) {
                $fresnsLangItems = [
                    'table_name' => 'configs',
                    'table_column' => 'item_value',
                    'table_id' => null,
                    'table_key' => $item['item_key'],
                    'language_values' => $item['language_values'],
                ];
                ConfigUtility::changeFresnsLanguageItems($fresnsLangItems);
            }
        }
    }

    // change language items
    public static function changeFresnsLanguageItems($fresnsLangItems)
    {
        foreach($fresnsLangItems['language_values'] ?? [] as $key => $value) {
            $item = $fresnsLangItems;
            $item['lang_tag'] = $key;
            $item['lang_content'] = $value;

            unset($item['language_values']);

            Language::updateOrCreate($item);
        }
    }

    // get code message
    public static function getCodeMessage(int $code, ?string $unikey = null, ?string $langTag = null)
    {
        $unikey = $unikey ?: 'Fresns';

        if (empty($langTag)) {
            $langTag = Config::where('item_key', 'default_language')->value('item_value');
        }

        $message = CodeMessage::where('plugin_unikey', $unikey)->where('code', $code)->where('lang_tag', $langTag)->value('message');

        return $message ?? 'Unknown Error';
    }

    // get login error count
    public static function getLoginErrorCount(int $accountId, ?int $userId = null): int
    {
        $sessionLog = SessionLog::whereIn('type', [2, 5, 8])
            ->whereIn('object_result', [1 ,2])
            ->where('account_id', $accountId)
            ->where('created_at', '>=', now()->subHour());

        if (! empty($userId)) {
            $sessionLog->where('user_id', $userId);
        }

        $errorCount = $sessionLog->count();

        return $errorCount;
    }
}
