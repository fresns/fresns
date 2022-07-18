<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Web\Http\Middleware;

use App\Fresns\Web\Helpers\ApiHelper;
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
                'errorMessage' => Browser::isMobile() ? '<p>'.__('FsWeb::tips.errorMobileTheme').'</p><p>'.__('FsWeb::tips.settingThemeTip').'</p>' : '<p>'.__('FsWeb::tips.errorPcTheme').'</p><p>'.__('FsWeb::tips.settingThemeTip').'</p>',
                'errorCode' => 500,
            ], 500);
        }

        if (fs_db_config('engine_api_type') == 'local') {
            if (! fs_db_config('engine_key_id')) {
                return Response::view('error', [
                    'errorMessage' => '<p>'.__('FsWeb::tips.errorKey').'</p><p>'.__('FsWeb::tips.settingApiTip').'</p>',
                    'errorCode' => 500,
                ], 500);
            }

            $keyInfo = SessionKey::find(fs_db_config('engine_key_id'));

            if (! $keyInfo) {
                return Response::view('error', [
                    'errorMessage' => '<p>'.__('FsWeb::tips.errorKey').'</p><p>'.__('FsWeb::tips.settingApiTip').'</p>',
                    'errorCode' => 500,
                ], 500);
            }
        }

        if (fs_db_config('engine_api_type') == 'remote') {
            if (! fs_db_config('engine_api_host') || ! fs_db_config('engine_api_app_id') || ! fs_db_config('engine_api_app_secret')) {
                return Response::view('error', [
                    'errorMessage' => '<p>'.__('FsWeb::tips.errorApi').'</p><p>'.__('FsWeb::tips.settingApiTip').'</p>',
                    'errorCode' => 500,
                ], 500);
            }
        }

        $this->loadLanguages();
        $finder = app('view')->getFinder();
        $finder->prependLocation(base_path("extensions/themes/{$path}"));
        $this->userPanel();

        $timezone = fs_user('detail.timezone') ?: ConfigHelper::fresnsConfigByItemKey('default_timezone');
        Cookie::queue('timezone', $timezone);

        return $next($request);
    }

    private function userPanel(): void
    {
        if (fs_user()->check()) {
            $uid = fs_user('detail.uid');

            $result = ApiHelper::make()->get("/api/v2/user/{$uid}/detail");

            View::share('userPanel', $result['data']);
        }
    }

    public function loadLanguages()
    {
        $menus = fs_api_config('language_menus', fs_db_config('default_language'));

        $supportedLocales = [];
        foreach ($menus as $menu) {
            $supportedLocales[$menu['langTag']] = ['name' => $menu['langName']];
        }

        app()->get('laravellocalization')->setSupportedLocales($supportedLocales);

        fs_api_config('language_status') ? Cache::put('supportedLocales', $supportedLocales) : Cache::forget('supportedLocales');
    }
}
