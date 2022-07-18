<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Models\Config;
use App\Models\Plugin;
use App\Utilities\ConfigUtility;
use Illuminate\Http\Request;

class ExtensionController extends Controller
{
    public function pluginIndex(Request $request)
    {
        $plugins = Plugin::type(1);

        $isEnable = match ($request->status) {
            'active' => 1,
            'inactive' => 0,
            default => null,
        };

        if ($isEnable) {
            $plugins->isEnable($isEnable);
        }

        $plugins = $plugins->get();

        $enableCount = Plugin::type(1)->isEnable()->count();
        $disableCount = Plugin::type(1)->where('is_enable', 0)->count();

        return view('FsView::extensions.plugins', compact('plugins', 'enableCount', 'disableCount', 'isEnable'));
    }

    public function panelIndex(Request $request)
    {
        $panels = Plugin::type(2);

        $isEnable = match ($request->status) {
            'active' => 1,
            'inactive' => 0,
            default => null,
        };

        if ($isEnable) {
            $panels->isEnable($isEnable);
        }

        $panels = $panels->get();

        $enableCount = Plugin::type(2)->isEnable()->count();
        $disableCount = Plugin::type(2)->where('is_enable', 0)->count();

        return view('FsView::extensions.panels', compact('panels', 'enableCount', 'disableCount', 'isEnable'));
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

        $FresnsEngine = Config::where('item_key', 'FresnsEngine')->first()?->item_value;
        $themeUnikey['pc'] = Config::where('item_key', 'FresnsEngine_Pc')->value('item_value');
        $themeUnikey['mobile'] = Config::where('item_key', 'FresnsEngine_Mobile')->value('item_value');

        $themeName['pc'] = Plugin::where('unikey', $themeUnikey['pc'])->value('name');
        $themeName['mobile'] = Plugin::where('unikey', $themeUnikey['mobile'])->value('name');

        return view('FsView::extensions.engines', compact(
            'engines', 'configs', 'themes', 'plugins', 'FresnsEngine', 'themeUnikey', 'themeName'
        ));
    }

    public function updateDefaultEngine(Request $request)
    {
        if ($request->get('is_enable') != 0) {
            Config::where('item_key', 'FresnsEngine')->update([
                'item_value' => 'true',
            ]);
        } else {
            Config::where('item_key', 'FresnsEngine')->update([
                'item_value' => 'false',
            ]);
        }

        return $this->updateSuccess();
    }

    public function updateEngineTheme(string $unikey, Request $request)
    {
        $pcKey = $unikey.'_Pc';
        $mobileKey = $unikey.'_Mobile';

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

        return view('FsView::extensions.themes', compact('themes'));
    }

    public function appIndex()
    {
        $apps = Plugin::type(2)->get();

        return view('FsView::extensions.apps', compact('apps'));
    }

    public function install(?string $unikey = null, Request $request)
    {
        if ($unikey) {
            $request->offsetSet('install_method', 'inputUrl');
        }

        defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
        defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'r'));
        defined('STDERR') or define('STDERR', fopen('php://stderr', 'r'));

        $installType = $request->get('install_type');
        $installMethod = $request->get('install_method');

        switch ($installMethod) {
            case 'inputDir':
                // php artisan plugin:install ...
                // php artisan theme:install ...

                $command = match ($installType) {
                    default => throw new \RuntimeException("unknown install_type {$installType}"),
                    'plugin' => 'plugin:install',
                    'theme' => 'theme:install',
                };

                $dir = base_path($request->get('plugin_dir'));

                \Artisan::call($command, [
                    'path' => "$dir",
                    '--force' => true,
                ]);

                return \response(\Artisan::output()."\n".__('FsLang::tips.installSuccess'));
            break;
            case 'inputUrl':
            case 'inputUnikey':
                if ($unikey = $request->get('plugin_unikey', $unikey)) {
                    // php artisan fresns:require ...
                    \Artisan::call('fresns:require', [
                        'unikey' => $unikey,
                    ]);
                    $output = \Artisan::output();

                    if ($installMethod == 'inputUrl') {
                        if ($output == "\n") {
                            return back()->with('failure', __('FsLang::tips.installFailure'));
                        }

                        return back()->with('success', "\n $unikey ".__('FsLang::tips.installSuccess'));
                    } else {
                        if ($output == "\n") {
                            return \response("$unikey ".__('FsLang::tips.installFailure'));
                        }

                        return \response($output."\n $unikey ".__('FsLang::tips.installSuccess'));
                    }
                }
            break;
            case 'inputFile':
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
                        '--force' => true,
                    ]);

                    return \response(\Artisan::output()."\n".__('FsLang::tips.installSuccess'));
                }
            break;
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
                'output' => \Artisan::output()."\n".__('FsLang::tips.upgradeSuccess'),
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

    public function uninstallTheme(Request $request)
    {
        if ($request->get('clearData') == 1) {
            $theme = $request->theme;
            if (! $theme) {
                abort(404);
            }

            $themeJsonFile = resource_path('themes/'.$theme.'/theme.json');

            if (! \File::exists($themeJsonFile)) {
                return back()->with('failure', __('FsLang::tips.theme_json_file_error'));
            }

            $themeConfig = json_decode(\File::get($themeJsonFile), true);
            $functionKeys = $themeConfig['functionKeys'] ?? [];

            $configItemKeys = Config::whereIn('item_key', collect($functionKeys)
                ->pluck('itemKey'))
                ->where('is_custom', 1)
                ->pluck('item_key');

            ConfigUtility::removeFresnsConfigItems($configItemKeys);

            \Artisan::call('theme:uninstall', ['plugin' => $request->theme, '--cleardata' => true]);
        } else {
            \Artisan::call('theme:uninstall', ['plugin' => $request->theme, '--cleardata' => false]);
        }

        return response()->json(['message' => \Artisan::output().__('FsLang::tips.uninstallSuccess')], 200);
    }
}
