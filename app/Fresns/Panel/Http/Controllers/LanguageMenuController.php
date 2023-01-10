<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Fresns\Panel\Http\Requests\UpdateDefaultLanguageRequest;
use App\Fresns\Panel\Http\Requests\UpdateLanguageMenuRequest;
use App\Fresns\Panel\Http\Requests\UpdateLanguageRankRequest;
use App\Helpers\CacheHelper;
use App\Models\CodeMessage;
use App\Models\Config;
use App\Models\Language;
use App\Models\Seo;

class LanguageMenuController extends Controller
{
    public function index()
    {
        $languageConfig = Config::where('item_key', 'language_menus')->firstOrFail();
        $languages = collect($languageConfig->item_value)->sortBy('rating');

        $defaultLanguageConfig = Config::where('item_key', 'default_language')->firstOrFail();
        $defaultLanguage = $defaultLanguageConfig->item_value;

        $statusConfig = Config::where('item_key', 'language_status')->firstOrFail();
        $status = $statusConfig->item_value;

        $codeConfig = Config::where('item_key', 'language_codes')->firstOrFail();
        $codes = $codeConfig->item_value;

        $continentConfig = Config::where('item_key', 'continents')->firstOrFail();
        $continents = $continentConfig->item_value;

        return view('FsView::systems.languages', compact(
            'languages', 'defaultLanguage', 'status',
            'codes', 'continents'
        ));
    }

    public function switchStatus()
    {
        $statusConfig = Config::where('item_key', 'language_status')->firstOrFail();
        $statusConfig->item_value = ! $statusConfig->item_value;
        $statusConfig->save();

        CacheHelper::forgetFresnsMultilingual('language_status');

        return $this->updateSuccess();
    }

    public function updateDefaultLanguage(UpdateDefaultLanguageRequest $request)
    {
        $defaultLanguageConfig = Config::where('item_key', 'default_language')->firstOrFail();
        $defaultLanguageConfig->item_value = $request->default_language;
        $defaultLanguageConfig->save();

        CacheHelper::forgetFresnsMultilingual('default_language');
        CacheHelper::forgetFresnsKey('fresns_default_langTag');

        return $this->updateSuccess();
    }

    public function updateRating(UpdateLanguageRankRequest $request, $langTag)
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
        $language['rating'] = $request->rating;

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
            'rating' => $request->rating,
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
            'packVersion' => 1,
            'isEnable' => (bool) $request->is_enable,
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
        $oldConfig = $languageCollection->where('langTag', $request->old_lang_tag)->first();
        if (! $oldConfig) {
            return back()->with('failure', __('FsLang::tips.language_not_exists'));
        }

        $langCode = $oldConfig['langCode'];
        // check exists
        $langTag = ($request->area_code && $request->area_status) ? $langCode.'-'.$request->area_code : $langCode;

        if ($langTag != $request->old_lang_tag && collect($languages)->where('langTag', $langTag)->first()) {
            return back()->with('failure', __('FsLang::tips.language_exists'));
        }

        // default language
        $defaultLanguageConfig = Config::where('item_key', 'default_language')->firstOrFail();
        $defaultLanguage = $defaultLanguageConfig->item_value;

        $areaName = '';
        if ($request->area_status && $request->area_code) {
            $areaCodeConfig = Config::where('item_key', 'area_codes')->firstOrFail();
            $areaCodes = $areaCodeConfig->item_value;

            $areaCode = collect($areaCodes)->where('code', $request->area_code)->firstOrFail();
            $areaName = $areaCode['name'];
        }

        $languageKey = $languageCollection->search(function ($item) use ($request) {
            return $item['langTag'] == $request->old_lang_tag;
        });

        if ($languageKey === false) {
            return back()->with('failure', __('FsLang::tips.language_not_exists'));
        }

        $data = [
            'rating' => $request->rating,
            'langCode' => $oldConfig['langCode'],
            'langName' => $oldConfig['langName'] ?? '',
            'langTag' => $langTag,
            'continentId' => $request->area_status ? $request->continent_id : 0,
            'areaStatus' => (bool) $request->area_status,
            'areaCode' => $request->area_status ? $request->area_code : null,
            'areaName' => $areaName,
            'writingDirection' => $oldConfig['writingDirection'],
            'lengthUnit' => $request->length_unit,
            'dateFormat' => $request->date_format,
            'timeFormatMinute' => $request->time_format_minute,
            'timeFormatHour' => $request->time_format_hour,
            'timeFormatDay' => $request->time_format_day,
            'timeFormatMonth' => $request->time_format_month,
            'packVersion' => 1,
            'isEnable' => (bool) $request->is_enable,
        ];

        $languages[$languageKey] = $data;
        $languageConfig->item_value = array_values($languages);
        $languageConfig->save();

        // If it is the default language, modify the configuration
        if ($defaultLanguage == $request->old_lang_tag && $request->old_lang_tag != $langTag) {
            $defaultLanguageConfig->item_value = $langTag;
            $defaultLanguageConfig->save();
        }

        // Language tag changes can be made with the event
        if ($request->old_lang_tag != $langTag) {
            $this->updateAllLangTag($request->old_lang_tag, $langTag);
        }

        return $this->updateSuccess();
    }

    protected function updateAllLangTag(string $oldLangTag, string $langTag)
    {
        Language::where('lang_tag', $oldLangTag)->update(['lang_tag' => $langTag]);
        Seo::where('lang_tag', $oldLangTag)->update(['lang_tag' => $langTag]);
        CodeMessage::where('lang_tag', $oldLangTag)->update(['lang_tag' => $langTag]);

        $configKeys = [
            'verifycode_template1',
            'verifycode_template2',
            'verifycode_template3',
            'verifycode_template4',
            'verifycode_template5',
            'verifycode_template6',
            'verifycode_template7',
        ];

        Config::whereIn('item_key', $configKeys)->get()->each(function ($config) use ($oldLangTag, $langTag) {
            $value = $config->item_value;
            if (! $value) {
                return;
            }

            foreach ($value as &$item) {
                foreach ($item['template'] as &$template) {
                    if ($template['langTag'] == $oldLangTag) {
                        $template['langTag'] = $langTag;
                    }
                }
            }

            $config->item_value = $value;
            $config->save();
        });
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
        })->toArray();

        $languageConfig->item_value = $languages;
        $languageConfig->save();

        return $this->deleteSuccess();
    }
}
