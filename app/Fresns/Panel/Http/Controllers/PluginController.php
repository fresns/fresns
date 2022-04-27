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

    public function install(Request $request)
    {
        defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

        $installType = $request->get('install_type');

        $file = $request->file('plugin_zipball');
        if ($file && $file->isValid()) {
            // php artisan plugin:install ...
            // php artisan theme:install ...

            $command = match ($installType) {
                default => throw new \RuntimeException("unknown install_type {$installType}"),
                'plugin' => 'plugin:install',
                'theme' => 'theme:install',
            };

            $dir = storage_path('extensions');
            $filename = $file->hashName();
            $file->move($dir, $filename);

            \Artisan::call($command, [
                'path' => "$dir/$filename",
            ]);

            return \response()->json([
                'message' => __('FsLang::tips.installSuccess'),
                'data' => [
                    'output' => \Artisan::output(),
                ],
            ], 200);
        } elseif ($unikey = $request->get('plugin_unikey')) {
            // php artisan fresns:require ...
            \Artisan::call('fresns:require', [
                'unikey' => $unikey,
            ]);

            return \response()->json([
                'message' => __('FsLang::tips.installSuccess'),
                'data' => [
                    'output' => \Artisan::output(),
                ],
            ], 200);
        }

        return back()->with('failure', __('FsLang::tips.installFailure'));
    }

    public function upgrade(Request $request)
    {
        $unikey = $request->get('unikey');

        \Artisan::call('fresns:update', [
            'unikey' => $unikey,
        ]);

        return \response()->json([
            'message' => __('FsLang::tips.upgradeSuccess'),
            'data' => [
                'output' => \Artisan::output(),
            ],
        ], 200);

        return back()->with('failure', __('FsLang::tips.installFailure'));
    }

    public function update(Request $request)
    {
        if ($request->get('is_enable') != 0) {
            \Artisan::call('plugin:activate', ['plugin' => $request->plugin]);
        } else {
            \Artisan::call('plugin:deactivate', ['plugin' => $request->plugin]);
        }

        return $this->updateSuccess();
    }

    public function updateCode(Request $request)
    {
        $plugin = Plugin::where('unikey', $request->input('pluginUnikey'))->first();

        if (! empty($plugin)) {
            $plugin->upgrade_code = $request->upgradeCode;
            $plugin->save();

            return $this->updateSuccess();
        }

        return back()->with('failure', __('FsLang::tips.plugin_not_exists'));
    }

    public function uninstall(Request $request)
    {
        if ($request->get('clearData') == 1) {
            \Artisan::call('plugin:uninstall', ['plugin' => $request->plugin, '--cleardata' => true]);
        } else {
            \Artisan::call('plugin:uninstall', ['plugin' => $request->plugin, '--cleardata' => false]);
        }

        return response(\Artisan::output()."\n".__('FsLang::tips.uninstallSuccess'));
    }

    public function updateTheme(Request $request)
    {
        $theme = Plugin::where('unikey', $request->input('theme'))->first();

        if ($request->has('is_enable')) {
            $theme->is_enable = $request->is_enable;
        }
        $theme->save();

        return $this->updateSuccess();
    }

    public function uninstallTheme(Request $request)
    {
        if ($request->get('clearData') == 1) {
            \Artisan::call('theme:uninstall', ['theme' => $request->theme, '--cleandata' => true]);
        } else {
            \Artisan::call('theme:uninstall', ['theme' => $request->theme, '--cleandata' => false]);
        }

        return response()->json(['message' => \Artisan::output().__('FsLang::tips.uninstallSuccess')], 200);
    }
}
