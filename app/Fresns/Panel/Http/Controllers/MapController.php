<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\PrimaryHelper;
use App\Models\Config;
use App\Models\Language;
use App\Models\Plugin;
use App\Models\PluginUsage;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function index()
    {
        $config = Config::where('item_key', 'maps')->first();
        $mapServices = $config->item_value ?? [];
        $mapServices = collect($mapServices)->mapWithKeys(function ($service) {
            return [$service['id'] => $service];
        });

        $mapKeys = collect($mapServices)->pluck('id')->map(function ($id) {
            return 'map_'.$id;
        });

        $maps = Config::whereIn('item_key', $mapKeys)->get();
        $maps = $maps->mapWithKeys(function ($config) {
            return [$config->item_key => $config->item_value];
        });

        $pluginUsages = PluginUsage::where('type', 9)
            ->orderBy('rating')
            ->with('plugin')
            ->get();

        $plugins = Plugin::all();

        $languages = Language::tableName('plugin_usages')
            ->where('table_column', 'name')
            ->whereIn('table_id', $pluginUsages->pluck('id'))
            ->get();

        return view('FsView::systems.maps', compact(
            'pluginUsages', 'mapServices', 'maps',
            'plugins', 'languages',
        ));
    }

    public function store(Request $request)
    {
        $map = PluginUsage::where('parameter', $request->parameter)
            ->where('type', 9)
            ->first();

        if ($map) {
            return back()->with('failure', __('FsLang::tips.map_exists'));
        }

        $map = new PluginUsage;
        $map->plugin_unikey = $request->plugin_unikey;
        $map->is_enable = $request->is_enable;
        $map->rating = $request->rating;
        $map->parameter = $request->parameter;
        $map->icon_file_url = $request->icon_file_url ?: '';
        $map->type = 9;
        $map->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $map->save();

        if ($request->file('icon_file')) {
            $wordBody = [
                'platformId' => 4,
                'useType' => 2,
                'tableName' => 'plugin_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $map->id,
                'type' => 1,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $map->icon_file_id = $fileId;
            $map->icon_file_url = null;
            $map->save();
        }

        $config = Config::where('item_key', 'map_'.$request->parameter)
            ->where('item_tag', 'maps')
            ->first();

        if (! $config) {
            $config = new Config();
            $config->item_key = 'map_'.$request->parameter;
            $config->item_tag = 'maps';
            $config->item_type = 'object';
        }

        $config->item_value = [
            'mapId' => $request->parameter,
            'appId' => $request->app_id,
            'appKey' => $request->app_key,
        ];
        $config->save();

        foreach ($request->names as $langTag => $content) {
            $language = Language::tableName('plugin_usages')
                ->where('table_name', 'plugin_usages')
                ->where('table_column', 'name')
                ->where('table_id', $map->id)
                ->where('lang_tag', $langTag)
                ->first();
            if (! $language) {
                // create but no content
                if (! $content) {
                    continue;
                }
                $language = new Language();
                $language->fill([
                    'table_name' => 'plugin_usages',
                    'table_column' => 'name',
                    'table_id' => $map->id,
                    'lang_tag' => $langTag,
                ]);
            }

            $language->lang_content = $content;
            $language->save();
        }

        return $this->createSuccess();
    }

    public function update(Request $request, PluginUsage $map)
    {
        $map->plugin_unikey = $request->plugin_unikey;
        $map->is_enable = $request->is_enable;
        $map->rating = $request->rating;
        $map->parameter = $request->parameter;
        $map->type = 9;
        $map->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');

        if ($request->file('icon_file')) {
            $wordBody = [
                'platformId' => 4,
                'useType' => 2,
                'tableName' => 'plugin_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $map->id,
                'type' => 1,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $map->icon_file_id = $fileId;
            $map->icon_file_url = null;
        } elseif ($map->icon_file_url != $request->icon_file_url) {
            $map->icon_file_id = null;
            $map->icon_file_url = $request->icon_file_url;
        }

        $map->save();

        $config = Config::where('item_key', 'map_'.$request->parameter)
            ->where('item_tag', 'maps')
            ->first();

        if (! $config) {
            $config = new Config();
            $config->item_key = 'map_'.$request->parameter;
            $config->item_tag = 'maps';
            $config->item_type = 'object';
        }

        $config->item_value = [
            'mapId' => $request->parameter,
            'appId' => $request->app_id,
            'appKey' => $request->app_key,
        ];
        $config->save();

        foreach ($request->names as $langTag => $content) {
            $language = Language::tableName('plugin_usages')
                ->where('table_column', 'name')
                ->where('table_id', $map->id)
                ->where('lang_tag', $langTag)
                ->first();
            if (! $language) {
                // create but no content
                if (! $content) {
                    continue;
                }
                $language = new Language();
                $language->fill([
                    'table_name' => 'plugin_usages',
                    'table_column' => 'name',
                    'table_id' => $map->id,
                    'lang_tag' => $langTag,
                ]);
            }

            $language->lang_content = $content;
            $language->save();
        }

        return $this->updateSuccess();
    }
}
