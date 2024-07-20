<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Config;
use App\Utilities\AppUtility;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $languageStatus;

    protected $defaultLanguage;

    protected $optionalLanguages;

    public function __construct()
    {
        View::share('langs', config('FsConfig.langs'));

        $configKeys = [
            'default_language',
            'language_status',
            'language_menus',
            'check_version_datetime',
            'site_url',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $defaultLanguage = $configs->where('item_key', 'default_language')->first()?->item_value ?? config('app.locale');
        $languageStatus = $configs->where('item_key', 'language_status')->first()?->item_value ?? false;
        $languageMenus = $configs->where('item_key', 'language_menus')->first()?->item_value ?? [];
        $checkVersionDatetime = $configs->where('item_key', 'check_version_datetime')->first()?->item_value ?? now();

        try {
            // language status
            $this->languageStatus = $languageStatus;
            View::share('languageStatus', $languageStatus);

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

            // Check Plugins Versions
            if (Carbon::parse($checkVersionDatetime)->diffInMinutes(now()) > 10) {
                \FresnsCmdWord::plugin('Fresns')->checkAppsVersions();

                // Time of the latest check version
                Config::updateOrCreate([
                    'item_key' => 'check_version_datetime',
                ], [
                    'item_value' => now(),
                    'item_type' => 'string',
                ]);
            }
        } catch (\Exception $e) {
        }

        // lang tag
        $langTag = Cookie::get('fresns_panel_locale', config('app.locale'));

        // url
        $siteUrl = $configs->where('item_key', 'site_url')->first()?->item_value ?? '/';
        $websiteUrl = AppUtility::WEBSITE_URL_ARR[$langTag] ?? AppUtility::WEBSITE_URL_ARR['en'];
        $communityUrl = AppUtility::COMMUNITY_URL;
        $marketplaceUrl = AppUtility::MARKETPLACE_URL;

        if ($langTag != 'en') {
            $langTag = Str::lower($langTag);
            $marketplaceUrl = AppUtility::MARKETPLACE_URL.'/'.$langTag;
        }

        if (config('app.mirror_code')) {
            $mirrorCode = Str::lower(config('app.mirror_code'));
            $domainSuffix = '.'.$mirrorCode;

            $websiteUrl = str_replace('.org', $domainSuffix, AppUtility::WEBSITE_URL);
            $communityUrl = str_replace('.org', $domainSuffix, AppUtility::COMMUNITY_URL);
            $marketplaceUrl = str_replace('.com', $domainSuffix, AppUtility::MARKETPLACE_URL);
        }

        View::share('siteUrl', $siteUrl);
        View::share('websiteUrl', $websiteUrl);
        View::share('communityUrl', $communityUrl);
        View::share('marketplaceUrl', $marketplaceUrl);

        // md5 16bit
        View::share('versionMd5', AppHelper::VERSION_MD5_16BIT);
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
