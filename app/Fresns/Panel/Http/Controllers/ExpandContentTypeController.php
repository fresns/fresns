<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Language;
use App\Models\Plugin;
use App\Models\PluginUsage;
use Illuminate\Http\Request;

class ExpandContentTypeController extends Controller
{
    public function index()
    {
        $plugins = Plugin::all();

        $plugins = $plugins->filter(function ($plugin) {
            return in_array('expandContentType', $plugin->scene);
        });

        $pluginUsages = PluginUsage::where('type', 4)
            ->orderBy('rank_num')
            ->with('plugin', 'names')
            ->paginate();

        return view('FsView::expands.content-type', compact('plugins', 'pluginUsages'));
    }

    public function store(Request $request)
    {
        $pluginUsage = new PluginUsage;
        $pluginUsage->type = 4;
        $pluginUsage->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $pluginUsage->plugin_unikey = $request->plugin_unikey;
        $pluginUsage->is_enable = $request->is_enable;
        $pluginUsage->rank_num = $request->rank_num;
        $pluginUsage->can_delete = 1;
        $pluginUsage->data_sources = [
            'postByAll' => [
                'pluginUnikey' => $request->post_list,
                'rankNumber' => [],
            ],
            'postByFollow' => [
                'pluginUnikey' => $request->post_follow,
                'rankNumber' => [],
            ],
            'postByNearby' => [
                'pluginUnikey' => $request->post_nearby,
                'rankNumber' => [],
            ],
        ];
        $pluginUsage->save();

        if ($request->update_name) {
            foreach ($request->names as $langTag => $content) {
                $language = Language::tableName('plugin_usages')
                    ->where('table_id', $pluginUsage->id)
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
                        'table_id' => $pluginUsage->id,
                        'lang_tag' => $langTag,
                    ]);
                }

                $language->lang_content = $content;
                $language->save();
            }
        }

        return $this->createSuccess();
    }

    public function update($id, Request $request)
    {
        $pluginUsage = PluginUsage::findOrFail($id);
        $pluginUsage->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $pluginUsage->plugin_unikey = $request->plugin_unikey;
        $pluginUsage->is_enable = $request->is_enable;
        $pluginUsage->rank_num = $request->rank_num;
        $dataSources = $pluginUsage->data_sources;

        if ($request->post_all != ($dataSources['postByAll']['pluginUnikey'] ?? null)) {
            $dataSources['postByAll'] = [
                'pluginUnikey' => $request->post_all,
                'rankNumber' => [],
            ];
        }

        if ($request->post_follow != ($dataSources['postByFollow']['pluginUnikey'] ?? null)) {
            $dataSources['postByFollow'] = [
                'pluginUnikey' => $request->post_follow,
                'rankNumber' => [],
            ];
        }

        if ($request->post_nearby != ($dataSources['postByNearby']['pluginUnikey'] ?? null)) {
            $dataSources['postByNearby'] = [
                'pluginUnikey' => $request->post_nearby,
                'rankNumber' => [],
            ];
        }

        $pluginUsage->data_sources = $dataSources;
        $pluginUsage->save();

        if ($request->update_name) {
            foreach ($request->names as $langTag => $content) {
                $language = Language::tableName('plugin_usages')
                    ->where('table_id', $pluginUsage->id)
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
                        'table_id' => $pluginUsage->id,
                        'lang_tag' => $langTag,
                    ]);
                }

                $language->lang_content = $content;
                $language->save();
            }
        }

        return $this->createSuccess();
    }

    public function destroy($id)
    {
        $pluginUsage = PluginUsage::findOrFail($id);
        $pluginUsage->delete();

        return $this->deleteSuccess();
    }

    public function updateRank($id, Request $request)
    {
        $pluginUsage = PluginUsage::findOrFail($id);
        $pluginUsage->rank_num = $request->rank_num;
        $pluginUsage->save();

        return $this->updateSuccess();
    }

    public function updateSource($id, $key, Request $request)
    {
        $pluginUsage = PluginUsage::findOrFail($id);
        $dataSources = $pluginUsage->data_sources;

        $requestTitles = $request->titles ?: [];
        $requestDescriptions = $request->descriptions ?: [];

        $data = [];
        foreach ($request->ids as $itemKey => $id) {
            $intro = [];
            $titles = json_decode($requestTitles[$itemKey] ?? '', true) ?: [];
            $descriptions = json_decode($requestDescriptions[$itemKey] ?? '', true) ?: [];
            foreach ($this->optionalLanguages as $language) {
                $title = $titles[$language['langTag']] ?? '';
                $description = $descriptions[$language['langTag']] ?? '';
                if (! $title && ! $description) {
                    continue;
                }
                $intro[] = [
                    'title' => $title,
                    'description' => $description,
                    'langTag' => $language['langTag'],
                ];
            }

            $data[] = [
                'id' => $id,
                'intro' => $intro,
            ];
        }

        $dataSources[$key]['rankNumber'] = $data;
        $pluginUsage->data_sources = $dataSources;
        $pluginUsage->save();

        return $this->updateSuccess();
    }
}
