<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
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

        $config = Config::tag('languages')->where('item_key', $langTag)->first();
        $languages = $config ? $config->item_value : [];

        $languages = collect($languages)
            ->mapWithKeys(function ($language) {
                return [$language['name'] => $language['content']];
            });

        if ($langTag != $this->defaultLanguage) {
            $defaultConfig = Config::tag('languages')->where('item_key', $this->defaultLanguage)->first();
            $defaultLanguages = $defaultConfig ? $defaultConfig->item_value : [];

            $defaultLanguages = collect($defaultLanguages)
                ->mapWithKeys(function ($language) {
                    return [$language['name'] => $language['content']];
                });
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

        $languages = [];

        $config = Config::tag('languages')->where('item_key', $langTag)->first();
        if (! $config) {
            $config = new Config;
            $config->item_key = $langTag;
            $config->item_type = 'array';
            $config->item_tag = 'languages';
            $config->is_enable = 1;
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

            $languages[] = [
                'name' => $langKey,
                'content' => $contents[$key],
            ];
        }

        $languagePack->item_value = $defaultKeys->toArray();
        $languagePack->save();

        $config->item_value = $languages;
        $config->save();

        return $this->updateSuccess();
    }
}
