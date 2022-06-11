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

class ExtendUserProfileController extends Controller
{
    public function index()
    {
        $plugins = Plugin::all();

        $plugins = $plugins->filter(function ($plugin) {
            return in_array('extendUser', $plugin->scene);
        });

        $pluginUsages = PluginUsage::where('type', 8)
            ->orderBy('rating')
            ->with('plugin', 'names')
            ->paginate();

        $roles = Role::with('names')->get();

        return view('FsView::extends.user-profile', compact('pluginUsages', 'plugins', 'roles'));
    }

    public function store(Request $request)
    {
        $pluginUsage = new PluginUsage;
        $pluginUsage->type = 8;
        $pluginUsage->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $pluginUsage->plugin_unikey = $request->plugin_unikey;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enable = $request->is_enable;
        $pluginUsage->rating = $request->rating;
        $pluginUsage->roles = $request->roles ? implode(',', $request->roles) : $pluginUsage->roles;
        $pluginUsage->icon_file_url = $request->icon_file_url;
        $pluginUsage->save();

        if ($request->file('icon_file')) {
            $wordBody = [
                'platformId' => 4,
                'useType' => 2,
                'tableName' => 'plugin_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $pluginUsage->id,
                'type' => 1,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $pluginUsage->icon_file_id = $fileId;
            $pluginUsage->icon_file_url = null;
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
        $pluginUsage = PluginUsage::find($id);
        if (! $pluginUsage) {
            return $this->updateSuccess();
        }
        $pluginUsage->name = $request->names[$this->defaultLanguage] ?? (current(array_filter($request->names)) ?: '');
        $pluginUsage->plugin_unikey = $request->plugin_unikey;
        $pluginUsage->parameter = $request->parameter;
        $pluginUsage->is_enable = $request->is_enable;
        $pluginUsage->rating = $request->rating;
        $pluginUsage->roles = $request->roles ? implode(',', $request->roles) : $pluginUsage->roles;
        if ($request->file('icon_file')) {
            $wordBody = [
                'platformId' => 4,
                'useType' => 2,
                'tableName' => 'plugin_usages',
                'tableColumn' => 'icon_file_id',
                'tableId' => $pluginUsage->id,
                'type' => 1,
                'file' => $request->file('icon_file'),
            ];
            $fresnsResp = \FresnsCmdWord::plugin('Fresns')->uploadFile($wordBody);
            if ($fresnsResp->isErrorResponse()) {
                return back()->with('failure', $fresnsResp->getMessage());
            }
            $fileId = PrimaryHelper::fresnsFileIdByFid($fresnsResp->getData('fid'));

            $pluginUsage->icon_file_id = $fileId;
            $pluginUsage->icon_file_url = null;
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

    public function updateRating($id, Request $request)
    {
        $pluginUsage = PluginUsage::findOrFail($id);
        $pluginUsage->rating = $request->rating;
        $pluginUsage->save();

        return $this->updateSuccess();
    }
}
