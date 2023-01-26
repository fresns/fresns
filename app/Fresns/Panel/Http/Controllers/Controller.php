<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Config;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $defaultLanguage;

    protected $optionalLanguages;

    public function __construct()
    {
        View::share('langs', config('FsConfig.langs'));

        $configKeys = [
            'default_language',
            'language_status',
            'language_menus',
            'area_codes',
            'site_url',
            'check_version_datetime',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $defaultLanguage = $configs->where('item_key', 'default_language')->first()?->item_value ?? config('app.locale');
        $languageStatus = $configs->where('item_key', 'language_status')->first()?->item_value ?? false;
        $languageMenus = $configs->where('item_key', 'language_menus')->first()?->item_value ?? [];
        $areaCodes = $configs->where('item_key', 'area_codes')->first()?->item_value ?? [];
        $siteUrl = $configs->where('item_key', 'site_url')->first()?->item_value ?? '/';
        $checkVersionDatetime = $configs->where('item_key', 'check_version_datetime')->first()?->item_value ?? now();

        try {
            // default language
            $this->defaultLanguage = $defaultLanguage;
            View::share('defaultLanguage', $defaultLanguage);

            // language menus
            $optionalLanguages = $languageMenus;
            if (! $languageStatus) {
                $optionalLanguages = collect($languageMenus)->where('langTag', $defaultLanguage)->all();
            }
            $this->optionalLanguages = $optionalLanguages;
            View::share('optionalLanguages', collect($optionalLanguages));

            // area codes
            View::share('areaCodes', collect($areaCodes));

            // site home url
            View::share('siteUrl', $siteUrl);

            // Check Extensions Version
            if (Carbon::parse($checkVersionDatetime)->diffInMinutes(now()) > 10) {
                \FresnsCmdWord::plugin('Fresns')->checkExtensionsVersion();
            }

            // md5 16bit
            View::share('versionMd5', AppHelper::VERSION_MD5_16BIT);
        } catch (\Exception $e) {
        }
    }

    public function requestSuccess()
    {
        return $this->successResponse('request');
    }

    public function createSuccess()
    {
        return $this->successResponse('create');
    }

    public function updateSuccess()
    {
        return $this->successResponse('update');
    }

    public function deleteSuccess()
    {
        return $this->successResponse('delete');
    }

    public function installSuccess()
    {
        return $this->successResponse('install');
    }

    public function uninstallSuccess()
    {
        return $this->successResponse('uninstall');
    }

    public function successResponse($action)
    {
        return request()->ajax()
            ? response()->json(['message' => __('FsLang::tips.'.$action.'Success')], 200)
            : back()->with('success', __('FsLang::tips.'.$action.'Success'));
    }
}
