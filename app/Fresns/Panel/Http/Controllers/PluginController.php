<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use App\Models\Plugin;
use Illuminate\Http\Request;

class PluginController extends Controller
{
    public function index(Request $request)
    {
        $isEnable = $request->is_enable;

        $plugins = Plugin::type(1);

        if ($request->has('is_enable')) {
            $plugins->where('is_enable', $request->is_enable);
        }

        $plugins = $plugins->get();

        // sidebar show
        $enablePlugins = Plugin::type(1)->where('is_enable', 1)->get();

        $enableCount = $enablePlugins->count();
        $disableCount = Plugin::type(1)->where('is_enable', 0)->count();

        return view('FsView::plugins.list', compact('plugins', 'enableCount', 'disableCount', 'isEnable', 'enablePlugins'));
    }

    public function engineIndex()
    {
        $engines = Plugin::type(3)->get();

        $configKeys = [];
        $engines->each(function ($engine) use (&$configKeys) {
            $configKeys[] = $engine->unikey.'_Pc';
            $configKeys[] = $engine->unikey.'_Mobile';
        });

        $configs = Config::whereIn('item_key', $configKeys)->get();
        $pluginKeys = $configs->pluck('item_value')->filter();
        $plugins = Plugin::whereIn('unikey', $pluginKeys)->get()->mapWithKeys(function ($plugin, $key) {
            return [$plugin->unikey => $plugin->name];
        })->toArray();

        $themes = Plugin::type(4)->get();

        return view('FsView::clients.engines', compact(
            'engines', 'configs', 'themes', 'plugins'
        ));
    }

    public function updateEngineTheme(Plugin $engine, Request $request)
    {
        $pcKey = $engine->unikey.'_Pc';
        $mobileKey = $engine->unikey.'_Mobile';

        $pcConfig = Config::where('item_key', $pcKey)->first();
        if ($request->has($pcKey)) {
            if (! $pcConfig) {
                $pcConfig = new Config();
                $pcConfig->item_key = $pcKey;
                $pcConfig->item_type = 'string';
                $pcConfig->item_tag = 'themes';
                $pcConfig->is_enable = 1;
                $pcConfig->is_restful = 1;
            }
            $pcConfig->item_value = $request->$pcKey;
            $pcConfig->save();
        } else {
            if ($pcConfig) {
                $pcConfig->item_value = $request->$pcKey;
                $pcConfig->save();
            }
        }

        $mobileConfig = Config::where('item_key', $mobileKey)->first();
        if ($request->has($mobileKey)) {
            if (! $mobileConfig) {
                $mobileConfig = new Config();
                $mobileConfig->item_key = $mobileKey;
                $mobileConfig->item_type = 'string';
                $mobileConfig->item_tag = 'themes';
                $mobileConfig->is_enable = 1;
                $mobileConfig->is_restful = 1;
            }

            $mobileConfig->item_value = $request->$mobileKey;
            $mobileConfig->save();
        } else {
            if ($mobileConfig) {
                $mobileConfig->item_value = $request->$pcKey;
                $mobileConfig->save();
            }
        }

        return $this->updateSuccess();
    }

    public function themeIndex()
    {
        $themes = Plugin::type(4)->get();

        return view('FsView::clients.themes', compact(
            'themes'
        ));
    }

    public function appIndex()
    {
        $apps = Plugin::type(2)->get();

        return view('FsView::clients.apps', compact(
            'apps'
        ));
    }

    public function update(Plugin $plugin, Request $request)
    {
        if ($request->has('is_enable')) {
            $plugin->is_enable = $request->is_enable;
        }
        $plugin->save();

        return $this->updateSuccess();
    }

    public function destroy(Plugin $plugin)
    {
        $plugin->delete();

        return $this->deleteSuccess();
    }
}
