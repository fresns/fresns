<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\LanguagePack;
use Illuminate\Http\Request;

class LanguagePackController extends Controller
{
    public function index(Request $request)
    {
        $languagesQuery = LanguagePack::query();

        $languagesQuery->when($request->key, function ($query, $value) {
            $query->where('lang_key', $value);
        });

        $languages = $languagesQuery->paginate(50);

        return view('FsView::clients.language-packs', compact('languages'));
    }

    public function store(Request $request)
    {
        $language = LanguagePack::where('lang_key', $request->langKey)->first();

        if ($language) {
            return back()->with('failure', __('FsLang::tips.language_exists'));
        }

        $languageItem = [
            'lang_key' => $request->langKey,
            'lang_values' => $request->langValues,
        ];

        LanguagePack::create($languageItem);

        return $this->createSuccess();
    }

    public function update(LanguagePack $languagePack, Request $request)
    {
        if (! $languagePack) {
            return back()->with('failure', __('FsLang::tips.updateFailure'));
        }

        $langValues = $languagePack->langValues;

        foreach ($request->langValues as $langTag => $langContent) {
            $langValues[$langTag] = $langContent;
        }

        $languagePack->lang_values = $langValues;
        $languagePack->save();

        return $this->updateSuccess();
    }

    public function destroy(LanguagePack $languagePack)
    {
        if (! $languagePack->is_custom) {
            return back()->with('failure', __('FsLang::tips.deleteFailure'));
        }

        $languagePack->delete();

        return $this->deleteSuccess();
    }
}
