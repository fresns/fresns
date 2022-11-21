<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

use App\Fresns\Web\Auth\UserGuard;
use App\Fresns\Web\Helpers\ApiHelper;
use App\Fresns\Web\Helpers\DataHelper;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Models\File;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

// current_lang_tag
if (! function_exists('current_lang_tag')) {
    function current_lang_tag()
    {
        return App::getLocale() ?? ConfigHelper::fresnsConfigByItemKey('default_language');
    }
}

// fs_api_config
if (! function_exists('fs_api_config')) {
    function fs_api_config(string $itemKey, mixed $default = null)
    {
        $langTag = current_lang_tag();

        $cacheKey = "fresns_web_api_config_all_{$langTag}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType(File::TYPE_ALL);

        $apiConfig = Cache::remember($cacheKey, $cacheTime, function () {
            $result = ApiHelper::make()->get('/api/v2/global/configs', [
                'query' => [
                    'isAll' => true,
                ],
            ]);

            return $result;
        });

        if (! $apiConfig) {
            Cache::forget($cacheKey);
        }

        return data_get($apiConfig, "data.list.{$itemKey}") ?? $default;
    }
}

// fs_db_config
if (! function_exists('fs_db_config')) {
    function fs_db_config(string $itemKey, mixed $default = null)
    {
        return DataHelper::getConfigByItemKey($itemKey) ?? $default;
    }
}

// fs_lang
if (! function_exists('fs_lang')) {
    function fs_lang(string $langKey, ?string $default = null): ?string
    {
        $langArr = fs_api_config('language_pack_contents');
        $result = $langArr[$langKey] ?? $default;

        return $result;
    }
}

// fs_code_message
if (! function_exists('fs_code_message')) {
    function fs_code_message(int $code, ?string $unikey = 'Fresns', ?string $default = null): ?string
    {
        $langTag = current_lang_tag();

        $cacheKey = "fresns_web_code_message_all_{$unikey}_{$langTag}";
        $cacheTime = CacheHelper::fresnsCacheTimeByFileType();

        $codeMessages = Cache::remember($cacheKey, $cacheTime, function () use ($unikey) {
            $result = ApiHelper::make()->get('/api/v2/global/code-messages', [
                'query' => [
                    'unikey' => $unikey,
                    'isAll' => true,
                ],
            ]);

            return $result;
        });

        if (! $codeMessages) {
            Cache::forget($cacheKey);
        }

        return data_get($codeMessages, "data.{$code}") ?? $default;
    }
}

// fs_route
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

// fs_account
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

// fs_user
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

// fs_user_panel
if (! function_exists('fs_user_panel')) {
    /**
     * @param  string|null  $key
     * @return array
     */
    function fs_user_panel(?string $key = null)
    {
        return DataHelper::getFresnsUserPanel($key);
    }
}

// fs_groups
if (! function_exists('fs_groups')) {
    /**
     * @param  string|null  $listKey
     * @return array
     */
    function fs_groups(?string $listKey = null)
    {
        return DataHelper::getFresnsGroups($listKey);
    }
}

// fs_index_list
if (! function_exists('fs_index_list')) {
    /**
     * @param  string|null  $listKey
     * @return array
     */
    function fs_index_list(?string $listKey = null)
    {
        return DataHelper::getFresnsIndexList($listKey);
    }
}

// fs_list
if (! function_exists('fs_list')) {
    /**
     * @param  string|null  $listKey
     * @return array
     */
    function fs_list(?string $listKey = null)
    {
        return DataHelper::getFresnsList($listKey);
    }
}

// fs_stickies
if (! function_exists('fs_stickies')) {
    /**
     * @param  string|null  $listKey
     * @return array
     */
    function fs_stickies()
    {
        return DataHelper::getFresnsStickies();
    }
}
