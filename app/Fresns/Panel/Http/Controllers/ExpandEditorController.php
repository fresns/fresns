<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\PrimaryHelper;
use App\Models\Language;
use App\Models\Plugin;
use App\Models\PluginUsage;
use App\Models\Role;
use Illuminate\Http\Request;

class ExpandEditorController extends Controller
{
    public function index()
    {
        $plugins = Plugin::all();

        $plugins = $plugins->filter(function ($plugin) {
            return in_array('expandEditor', $plugin->scene ?: []);
        });

        $pluginUsages = PluginUsage::where('type', 3)
            ->orderBy('rank_num')
            ->with('plugin', 'names')
            ->paginate();

        $roles = Role::with('names')->get();

        return view('FsView::expands.editor', compact('pluginUsages', 'plugins', 'roles'));
    }

    public function store(Request $request)
    {
        $pluginUsage = new PluginUsage;
        $pluginUsage->type = 3;
        $pluginUsage->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $pluginUsage->plugin_unikey = $request->plugin_unikey;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enable = $request->is_enable;
        $pluginUsage->rank_num = $request->rank_num;
        $pluginUsage->editor_number = $request->editor_number;
        $pluginUsage->roles = $request->roles ? implode(',', $request->roles) : '';
        $pluginUsage->scene = $request->scene ? implode(',', $request->scene) : '';
        $pluginUsage->icon_file_url = $request->icon_file_url;
        $pluginUsage->can_delete = 1;
        $pluginUsage->save();

        if ($request->file('icon_file')) {
            $wordBody = [
                'platform' => 4,
                'type' => 1,
                'tableType' => 3,
                'tableName' => 'plugin_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $pluginUsage->id,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->errorResponse();
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $pluginUsage->icon_file_id = $fileId;
            $pluginUsage->icon_file_url = $fresnsResp->getData('imageConfigUrl');
            $pluginUsage->save();
        }

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
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enable = $request->is_enable;
        $pluginUsage->rank_num = $request->rank_num;
        $pluginUsage->editor_number = $request->editor_number;
        $pluginUsage->roles = $request->roles ? implode(',', $request->roles) : '';
        $pluginUsage->scene = $request->scene ? implode(',', $request->scene) : '';

        if ($request->file('icon_file')) {
            $wordBody = [
                'platform' => 4,
                'type' => 1,
                'tableType' => 3,
                'tableName' => 'plugin_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $pluginUsage->id,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return $fresnsResp->errorResponse();
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $pluginUsage->icon_file_id = $fileId;
            $pluginUsage->icon_file_url = $fresnsResp->getData('imageConfigUrl');
        } elseif ($pluginUsage->icon_file_url != $request->icon_file_url) {
            $pluginUsage->icon_file_id = null;
            $pluginUsage->icon_file_url = $request->icon_file_url;
        }

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

        return $this->updateSuccess();
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
}
