<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Models\Config;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Helpers\PluginHelper;
use App\Helpers\LanguageHelper;
use App\Fresns\Web\Helpers\ApiHelper;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// current langTag
if (! function_exists('current_lang_tag')) {
    function current_lang_tag()
    {
        return \App::getLocale() ?? ConfigHelper::fresnsConfigByItemKey('default_language');
    }
}

// fresns api config
if (! function_exists('fs_api_config')) {
    function fs_api_config(string $itemKey)
    {
        $langTag = current_lang_tag();

        $cacheKey = 'fresns_web_api_config_'.$itemKey.'_'.$langTag;
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $apiConfig = Cache::remember($cacheKey, $cacheTime, function () use ($itemKey) {
            $result = ApiHelper::make()->get('/api/v2/global/configs', [
                'query' => [
                    'keys' => $itemKey,
                ],
            ]);

            $item = $result["data.list.{$itemKey}"];

            if (is_object($item) && method_exists($item, 'toArray')) {
                return $item->toArray();
            }

            return $item;
        });

        if (! $apiConfig) {
            Cache::forget($cacheKey);
        }

        return $apiConfig;
    }
}

// fresns db config
if (! function_exists('fs_db_config')) {
    function fs_db_config(string $itemKey)
    {
        $langTag = current_lang_tag();

        $cacheKey = 'fresns_web_db_config_'.$itemKey.'_'.$langTag;
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $dbConfig = Cache::remember($cacheKey, $cacheTime, function () use ($itemKey, $langTag) {
            $config = Config::where('item_key', $itemKey)->first();

            if (! $config) {
                return null;
            }

            $itemValue = $config->item_value;

            if ($config->is_multilingual == 1) {
                $itemValue = LanguageHelper::fresnsLanguageByTableKey($config->item_key, $config->item_type, $langTag);
            } elseif ($config->item_type == 'file' && is_int($config->item_value)) {
                $itemValue = ConfigHelper::fresnsConfigFileUrlByItemKey($config->item_value);
            } elseif ($config->item_type == 'plugin') {
                $itemValue = PluginHelper::fresnsPluginUrlByUnikey($config->item_value);
            } elseif ($config->item_type == 'plugins') {
                if ($config->item_value) {
                    foreach ($config->item_value as $plugin) {
                        $item['code'] = $plugin['code'];
                        $item['url'] = PluginHelper::fresnsPluginUrlByUnikey($plugin['unikey']);
                        $itemArr[] = $item;
                    }
                    $itemValue = $itemArr;
                }
            }

            return $itemValue;
        });

        if (! $dbConfig) {
            Cache::forget($cacheKey);
        }

        return $dbConfig;
    }
}

// fs_lang
if (! function_exists('fs_lang')) {
    function fs_lang(string $langKey): ?string
    {
        $langArr = fs_api_config('language_pack_contents');
        $result = $langArr[$langKey] ?? null;

        return $result;
    }
}

if (! function_exists('fs_route')) {
    /**
     * @param  string|null  $url
     * @param  string|bool|null  $locale
     * @return string
     */
    function fs_route(string $url = null, string|bool $locale = null): string
    {
        return LaravelLocalization::localizeUrl($url, $locale);
    }
}

if (! function_exists('fs_account')) {
    /**
     * @return AccountGuard|mixin
     */
    function fs_account(?string $detailKey = null)
    {
        if ($detailKey) {
            return app('fresns.account')->get($detailKey);
        }

        return app('fresns.account');
    }
}

if (! function_exists('fs_user')) {
    /**
     * @return UserGuard|mixin
     */
    function fs_user(?string $detailKey = null)
    {
        if ($detailKey) {
            return app('fresns.user')->get($detailKey);
        }

        return app('fresns.user');
    }
}
