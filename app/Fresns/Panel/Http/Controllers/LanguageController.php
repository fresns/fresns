<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateDefaultLanguageRequest;
use App\Fresns\Panel\Http\Requests\UpdateLanguageMenuRequest;
use App\Fresns\Panel\Http\Requests\UpdateLanguageOrderRequest;
use App\Helpers\CacheHelper;
use App\Models\Config;

class LanguageController extends Controller
{
    public function index()
    {
        $configKeys = [
            'language_menus',
            'default_language',
            'language_status',
            'language_codes',
            'continents',
            'area_codes',
        ];

        $configs = Config::whereIn('item_key', $configKeys)->get();

        $languageMenus = $configs->where('item_key', 'language_menus')->first()?->item_value;
        $languages = collect($languageMenus)->sortBy('order');

        $defaultLanguage = $configs->where('item_key', 'default_language')->first()?->item_value;
        $languageStatus = $configs->where('item_key', 'language_status')->first()?->item_value;

        $languageCodes = $configs->where('item_key', 'language_codes')->first()?->item_value;
        $continents = $configs->where('item_key', 'continents')->first()?->item_value;
        $areaCodes = $configs->where('item_key', 'area_codes')->first()?->item_value;

        return view('FsView::systems.languages', compact('languages', 'defaultLanguage', 'languageStatus', 'languageCodes', 'continents', 'areaCodes'));
    }

    public function updateDefaultLanguage(UpdateDefaultLanguageRequest $request)
    {
        $defaultLanguageConfig = Config::where('item_key', 'default_language')->firstOrFail();
        $defaultLanguageConfig->item_value = $request->default_language;
        $defaultLanguageConfig->save();

        CacheHelper::forgetFresnsConfigs('default_language');
        CacheHelper::forgetFresnsKey('fresns_default_langTag');

        return $this->updateSuccess();
    }

    public function updateOrder(UpdateLanguageOrderRequest $request, $langTag)
    {
        $languageConfig = Config::where('item_key', 'language_menus')->firstOrFail();
        $languages = $languageConfig->item_value;

        $languageKey = collect($languages)->search(function ($item) use ($langTag) {
            return $item['langTag'] == $langTag;
        });

        if (! $languageKey) {
            return back()->with('failure', __('FsLang::tips.language_not_exists'));
        }

        $language = $languages[$languageKey];
        $language['order'] = $request->order;

        $languages[$languageKey] = $language;
        $languageConfig->item_value = array_values($languages);
        $languageConfig->save();

        return $this->updateSuccess();
    }

    public function store(UpdateLanguageMenuRequest $request)
    {
        $codeConfig = Config::where('item_key', 'language_codes')->firstOrFail();
        $codes = $codeConfig->item_value;
        $code = collect($codes)->where('code', $request->lang_code)->firstOrFail();

        $languageConfig = Config::where('item_key', 'language_menus')->firstOrFail();
        $languages = $languageConfig->item_value;

        $langTag = ($request->area_code && $request->area_status) ? $request->lang_code.'-'.$request->area_code : $request->lang_code;

        if (collect($languages)->where('langTag', $langTag)->first()) {
            return back()->with('failure', __('FsLang::tips.language_exists'));
        }

        $areaName = '';
        if ($request->area_status && $request->area_code) {
            $areaCodeConfig = Config::where('item_key', 'area_codes')->firstOrFail();
            $areaCodes = $areaCodeConfig->item_value;

            $areaCode = collect($areaCodes)->where('code', $request->area_code)->firstOrFail();
            $areaName = $areaCode['name'];
        }

        $data = [
            'order' => $request->order,
            'langCode' => $request->lang_code,
            'langName' => $code['name'] ?? '',
            'langTag' => $langTag,
            'continentId' => $request->area_status ? $request->continent_id : 0,
            'areaStatus' => (bool) $request->area_status,
            'areaCode' => $request->area_status ? $request->area_code : null,
            'areaName' => $areaName,
            'writingDirection' => $code['writingDirection'],
            'lengthUnit' => $request->length_unit,
            'dateFormat' => $request->date_format,
            'timeFormatMinute' => $request->time_format_minute,
            'timeFormatHour' => $request->time_format_hour,
            'timeFormatDay' => $request->time_format_day,
            'timeFormatMonth' => $request->time_format_month,
            'timeFormatYear' => $request->time_format_year,
            'isEnabled' => (bool) $request->is_enabled,
        ];

        $languages[] = $data;
        $languageConfig->item_value = $languages;
        $languageConfig->save();

        return $this->createSuccess();
    }

    public function update(UpdateLanguageMenuRequest $request, string $langTag)
    {
        $languageConfig = Config::where('item_key', 'language_menus')->firstOrFail();
        $languages = $languageConfig->item_value;

        $languageCollection = collect($languages);
        $langConfig = $languageCollection->where('langTag', $langTag)->first();
        if (! $langConfig) {
            return back()->with('failure', __('FsLang::tips.language_not_exists'));
        }

        $languageKey = $languageCollection->search(function ($item) use ($langTag) {
            return $item['langTag'] == $langTag;
        });

        if (! $languageKey) {
            return back()->with('failure', __('FsLang::tips.language_not_exists'));
        }

        $data = [
            'order' => $request->order,
            'langCode' => $langConfig['langCode'],
            'langName' => $langConfig['langName'] ?? '',
            'langTag' => $langTag,
            'continentId' => $langConfig['continentId'] ?? 0,
            'areaStatus' => $langConfig['areaStatus'] ?? false,
            'areaCode' => $langConfig['areaStatus'] ? $langConfig['areaCode'] : null,
            'areaName' => $langConfig['areaName'],
            'writingDirection' => $langConfig['writingDirection'],
            'lengthUnit' => $request->length_unit,
            'dateFormat' => $request->date_format,
            'timeFormatMinute' => $request->time_format_minute,
            'timeFormatHour' => $request->time_format_hour,
            'timeFormatDay' => $request->time_format_day,
            'timeFormatMonth' => $request->time_format_month,
            'timeFormatYear' => $request->time_format_year,
            'isEnabled' => (bool) $request->is_enabled,
        ];

        $languages[$languageKey] = $data;
        $languageConfig->item_value = array_values($languages);
        $languageConfig->save();

        return $this->updateSuccess();
    }

    public function destroy(string $code)
    {
        if ($this->defaultLanguage == $code) {
            return back()->with('failure', __('FsLang::tips.delete_default_language_error'));
        }

        $languageConfig = Config::where('item_key', 'language_menus')->firstOrFail();
        $languages = $languageConfig->item_value;

        $languages = collect($languages)->reject(function ($language) use ($code) {
            return $language['langTag'] == $code;
        })->values()->toArray();

        $languageConfig->item_value = $languages;
        $languageConfig->save();

        return $this->deleteSuccess();
    }
}
