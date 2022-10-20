<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Middleware;

use App\Fresns\Web\Helpers\ApiHelper;
use App\Helpers\CacheHelper;
use App\Helpers\ConfigHelper;
use App\Models\SessionKey;
use Browser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class WebConfiguration
{
    public function handle(Request $request, Closure $next)
    {
        $path = Browser::isMobile() ? fs_db_config('FresnsEngine_Mobile') : fs_db_config('FresnsEngine_Pc');

        if (! $path) {
            return Response::view('error', [
                'message' => Browser::isMobile() ? '<p>'.__('FsWeb::tips.errorMobileTheme').'</p><p>'.__('FsWeb::tips.settingThemeTip').'</p>' : '<p>'.__('FsWeb::tips.errorPcTheme').'</p><p>'.__('FsWeb::tips.settingThemeTip').'</p>',
                'code' => 500,
            ], 500);
        }

        if (fs_db_config('engine_api_type') == 'local') {
            if (! fs_db_config('engine_key_id')) {
                return Response::view('error', [
                    'message' => '<p>'.__('FsWeb::tips.errorKey').'</p><p>'.__('FsWeb::tips.settingApiTip').'</p>',
                    'code' => 500,
                ], 500);
            }

            $keyInfo = SessionKey::find(fs_db_config('engine_key_id'));

            if (! $keyInfo) {
                return Response::view('error', [
                    'message' => '<p>'.__('FsWeb::tips.errorKey').'</p><p>'.__('FsWeb::tips.settingApiTip').'</p>',
                    'code' => 500,
                ], 500);
            }
        }

        if (fs_db_config('engine_api_type') == 'remote') {
            if (! fs_db_config('engine_api_host') || ! fs_db_config('engine_api_app_id') || ! fs_db_config('engine_api_app_secret')) {
                return Response::view('error', [
                    'message' => '<p>'.__('FsWeb::tips.errorApi').'</p><p>'.__('FsWeb::tips.settingApiTip').'</p>',
                    'code' => 500,
                ], 500);
            }
        }

        $this->loadLanguages();
        $finder = app('view')->getFinder();
        $finder->prependLocation(base_path("extensions/themes/{$path}"));
        $this->userPanel();
        $this->groupCategories();

        $timezone = fs_user('detail.timezone') ?: ConfigHelper::fresnsConfigByItemKey('default_timezone');
        Cookie::queue('timezone', $timezone);

        return $next($request);
    }

    private function userPanel(): void
    {
        if (fs_user()->check()) {
            $result = ApiHelper::make()->get('/api/v2/user/panel');

            View::share('userPanel', $result['data']);
        }
    }

    private function groupCategories(): void
    {
        if (fs_user()->check()) {
            $langTag = current_lang_tag();

            $cacheKey = 'fresns_web_group_categories_'.$langTag;
            $cacheTime = CacheHelper::fresnsCacheTimeByFileType(1);

            $groupCategories = Cache::remember($cacheKey, $cacheTime, function () {
                $result = ApiHelper::make()->get('/api/v2/group/categories');

                return data_get($result->toArray(), 'data.list', null);
            });

            if (is_null($groupCategories)) {
                Cache::forget($cacheKey);

                $groupCategories = [];
            }

            View::share('groupCategories', $groupCategories);
        } else {
            View::share('groupCategories', []);
        }
    }

    public function loadLanguages()
    {
        $menus = fs_api_config('language_menus');

        $supportedLocales = [];
        foreach ($menus as $menu) {
            $supportedLocales[$menu['langTag']] = ['name' => $menu['langName']];
        }

        app()->get('laravellocalization')->setSupportedLocales($supportedLocales);

        fs_api_config('language_status') ? Cache::put('supportedLocales', $supportedLocales) : Cache::forget('supportedLocales');
    }
}
