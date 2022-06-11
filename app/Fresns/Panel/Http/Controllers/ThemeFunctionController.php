<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\ConfigHelper;
use App\Models\Config;
use App\Models\Language;
use App\Models\Plugin;
use App\Utilities\ConfigUtility;
use Illuminate\Http\Request;

class ThemeFunctionController extends Controller
{
    protected function getThemeConfig($theme)
    {
        if (! $theme) {
            abort(404, __('FsLang::tips.theme_error'));
        }

        $themeJsonFile = base_path('extensions/themes/'.$theme.'/theme.json');
        if (! $themeJsonFile) {
            abort(403, __('FsLang::tips.theme_json_file_error'));
        }

        $themeConfig = json_decode(\File::get($themeJsonFile), true);

        return $themeConfig;
    }

    public function show($theme)
    {
        $themeConfig = $this->getThemeConfig($theme);

        $functionKeys = collect($themeConfig['functionKeys'] ?? []);

        $view = $theme.'.functions';
        if (! view()->exists($view)) {
            abort(404, __('FsLang::tips.theme_functions_file_error'));
        }

        $configs = Config::whereIn('item_key', $functionKeys->pluck('itemKey'))->get();
        $configValue = $configs->pluck('item_value', 'item_key');
        $themeParams = [];

        // language keys
        $langKeys = $functionKeys->where('isMultilingual', true)->pluck('itemKey');
        $languages = Language::ofConfig()->whereIn('table_key', $langKeys)->get();

        foreach ($functionKeys as $functionKey) {
            $key = $functionKey['itemKey'];
            $functionKey['value'] = $configValue[$key] ?? null;
            // File
            if ($functionKey['itemType'] == 'file') {
                $functionKey['fileType'] = ConfigHelper::fresnsConfigFileValueTypeByItemKey($key);

                if ($functionKey['fileType'] == 'ID') {
                    $functionKey['fileUrl'] = ConfigHelper::fresnsConfigFileUrlByItemKey($key);
                } else {
                    $functionKey['fileUrl'] = $functionKey['value'];
                }
            }

            // Multilingual
            if ($functionKey['isMultilingual']) {
                $functionKey['languages'] = $languages->where('table_key', $key)->values();
                $functionKey['defaultLanguage'] = $languages->where('table_key', $key)->where('lang_tag', $this->defaultLanguage)->first()['lang_content'] ?? '';
            }

            $themeParams[$key] = $functionKey;
        }

        $plugins = Plugin::all();

        return view($view, compact('themeParams', 'plugins'));
    }

    public function update(Request $request)
    {
        $themeConfig = $this->getThemeConfig($request->theme);

        $functionKeys = $themeConfig['functionKeys'] ?? [];

        $fresnsConfigItems = [];
        foreach ($functionKeys as $functionKey) {
            if ($functionKey['itemType'] == 'file') {
                if ($request->file($functionKey['itemKey'].'_file')) {
                    $wordBody = [
                        'platformId' => 4,
                        'useType' => 2,
                        'tableName' => 'configs',
                        'tableColumn' => 'item_value',
                        'tableKey' => $functionKey['itemKey'],
                        'type' => 1,
                        'file' => $request->file($functionKey['itemKey'].'_file'),
                    ];
                    $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
                    if ($fresnsResp->isErrorResponse()) {
                        return back()->with('failure', $fresnsResp->getMessage());
                    }
                    $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));
                    $request->request->set($functionKey['itemKey'], $fileId);
                } elseif ($request->get($functionKey['itemKey'].'_url')) {
                    $request->request->set($functionKey['itemKey'], $request->get($functionKey['itemKey'].'_url'));
                }
            }

            $value = $request->{$functionKey['itemKey']};
            if ($functionKey['itemType'] == 'plugins') {
                $value = array_values($value);
            }

            $fresnsConfigItems[] = [
                'item_key' => $functionKey['itemKey'],
                'item_value' => $value,
                'item_type' => $functionKey['itemType'],
                'item_tag' => $functionKey['itemTag'],
                'is_multilingual' => $functionKey['isMultilingual'],
            ];
        }
        ConfigUtility::changeFresnsConfigItems($fresnsConfigItems);

        return $this->createSuccess();
    }

    public function updateLanguage(Request $request)
    {
        $key = $request->key;
        $theme = $request->theme;
        if (! $key || ! $theme) {
            abort(404);
        }
        $themeConfig = $this->getThemeConfig($theme);

        $functionKeys = $themeConfig['functionKeys'] ?? [];
        $functionKey = collect($functionKeys)->where('itemKey', $key)->first();
        if (! $functionKey) {
            abort(404);
        }

        $content = $request->languages[$this->defaultLanguage] ?? current(array_filter($request->languages));

        $fresnsConfigItem = [
            'item_key' => $functionKey['itemKey'],
            'item_value' => $content,
            'item_type' => $functionKey['itemType'],
            'item_tag' => $functionKey['itemTag'],
            'is_multilingual' => $functionKey['isMultilingual'],
        ];

        foreach ($request->languages as $langTag => $content) {
            $fresnsConfigItem['language_values'][] = [
                'lang_tag' => $langTag,
                'lang_content' => $content,
            ];
        }

        ConfigUtility::changeFresnsConfigItems([$fresnsConfigItem]);

        return $this->createSuccess();
    }
}
