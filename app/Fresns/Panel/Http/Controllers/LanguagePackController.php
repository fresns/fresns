<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\LanguageHelper;
use App\Models\Config;
use App\Models\Language;
use Illuminate\Http\Request;

class LanguagePackController extends Controller
{
    public function index()
    {
        return view('FsView::clients.language-packs');
    }

    public function edit($langTag)
    {
        $languagePack = Config::tag('languages')->where('item_key', 'language_pack')->first();
        $languageKeys = $languagePack ? $languagePack->item_value : [];

        $languages = LanguageHelper::fresnsLanguageByTableKey('language_pack_contents', 'object', $langTag);

        if ($langTag != $this->defaultLanguage) {
            $defaultLanguages = LanguageHelper::fresnsLanguageByTableKey('language_pack_contents', 'object', $this->defaultLanguage);
        } else {
            $defaultLanguages = $languages;
        }

        return view('FsView::clients.language-pack-config', compact(
            'languages', 'defaultLanguages', 'languageKeys', 'langTag'
        ));
    }

    public function update($langTag, Request $request)
    {
        $languagePack = Config::tag('languages')->where('item_key', 'language_pack')->first();
        $defaultKeys = $languagePack->item_value;

        $keys = $request->contents;
        $contents = $request->contents;

        // delete keys
        $defaultKeys = collect($defaultKeys)->reject(function ($defaultKey) use ($keys) {
            return $defaultKey['canDelete'] && ! in_array($defaultKey['name'], $keys);
        });
        $defaultKeyNames = $defaultKeys->pluck('name');

        $languagePackContents = [];

        $languageContent = Language::where([
            'table_name' => 'configs',
            'table_column' => 'item_value',
            'table_key' => 'language_pack_contents',
            'lang_tag' => $langTag,
        ])->first();
        if (! $languageContent) {
            $languageContent = new Language;
            $languageContent->table_name = 'configs';
            $languageContent->table_column = 'item_value';
            $languageContent->table_key = 'language_pack_contents';
            $languageContent->lang_tag = $langTag;
        }

        foreach ($request->keys as $key => $langKey) {
            if (! $defaultKeyNames->contains($langKey)) {
                $defaultKeys->push([
                    'name' => $langKey,
                    'canDelete' => true,
                ]);
            }
            if (! isset($contents[$key]) || ! $contents[$key]) {
                continue;
            }

            $languagePackContents[$langKey] = $contents[$key];
        }

        $languagePack->item_value = $defaultKeys->toArray();
        $languagePack->save();

        $languageContent->lang_content = json_encode($languagePackContents);
        $languageContent->save();

        return $this->updateSuccess();
    }
}
