<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Panel\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Config;
use App\Models\Plugin;
use App\Utilities\AppUtility;
use App\Utilities\ConfigUtility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class ExtensionController extends Controller
{
    public function pluginIndex(Request $request)
    {
        AppUtility::checkPluginsStatus(1);
        $plugins = Plugin::type(1);

        $isEnable = match ($request->status) {
            'active' => 1,
            'inactive' => 0,
            default => null,
        };

        if (! is_null($isEnable)) {
            $plugins->isEnable($isEnable);
        }

        $plugins = $plugins->get();

        $enableCount = Plugin::type(1)->isEnable()->count();
        $disableCount = Plugin::type(1)->isEnable(false)->count();

        return view('FsView::extensions.plugins', compact('plugins', 'enableCount', 'disableCount', 'isEnable'));
    }

    public function panelIndex(Request $request)
    {
        AppUtility::checkPluginsStatus(2);
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
        AppUtility::checkPluginsStatus(3);
        $engines = Plugin::type(3)->get();

        $configKeys = [];
        $engines->each(function ($engine) use (&$configKeys) {
            $configKeys[] = $engine->unikey.'_Desktop';
            $configKeys[] = $engine->unikey.'_Mobile';
        });

        $configs = Config::whereIn('item_key', $configKeys)->get();
        $pluginKeys = $configs->pluck('item_value')->filter();
        $plugins = Plugin::whereIn('unikey', $pluginKeys)->get()->mapWithKeys(function ($plugin, $key) {
            return [$plugin->unikey => $plugin->name];
        })->toArray();

        $themes = Plugin::type(4)->get();

        $FresnsEngine = Config::where('item_key', 'FresnsEngine')->first()?->item_value;
        $themeUnikey['desktop'] = Config::where('item_key', 'FresnsEngine_Desktop')->value('item_value');
        $themeUnikey['mobile'] = Config::where('item_key', 'FresnsEngine_Mobile')->value('item_value');

        $themeName['desktop'] = Plugin::where('unikey', $themeUnikey['desktop'])->value('name');
        $themeName['mobile'] = Plugin::where('unikey', $themeUnikey['mobile'])->value('name');

        return view('FsView::extensions.engines', compact('engines', 'configs', 'themes', 'plugins', 'FresnsEngine', 'themeUnikey', 'themeName'));
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
        $desktopKey = $unikey.'_Desktop';
        $mobileKey = $unikey.'_Mobile';

        $desktopConfig = Config::where('item_key', $desktopKey)->first();
        if ($request->has($desktopKey)) {
            if (! $desktopConfig) {
                $desktopConfig = new Config();
                $desktopConfig->item_key = $desktopKey;
                $desktopConfig->item_type = 'string';
                $desktopConfig->item_tag = 'themes';
            }
            $desktopConfig->item_value = $request->$desktopKey;
            $desktopConfig->save();
        } else {
            if ($desktopConfig) {
                $desktopConfig->item_value = $request->$desktopKey;
                $desktopConfig->save();
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
                $mobileConfig->item_value = $request->$desktopKey;
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

    public function install(Request $request)
    {
        $installType = $request->install_type;
        $installMethod = $request->install_method;

        switch ($installMethod) {
            // unikey
            case 'inputUnikey':
                $pluginUnikey = $request->plugin_unikey;

                if (empty($pluginUnikey)) {
                    return back()->with('failure', __('FsLang::tips.install_not_entered_key'));
                }

                // market-manager
                $exitCode = Artisan::call('market:require', [
                    'unikey' => $pluginUnikey,
                ]);
                $output = Artisan::output();
            break;

            // directory
            case 'inputDirectory':
                $pluginDirectory = $request->plugin_directory;

                if (empty($pluginDirectory)) {
                    return back()->with('failure', __('FsLang::tips.install_not_entered_directory'));
                }

                if (strpos($pluginDirectory, '/') == false) {
                    $pluginDirectory = "extensions/{$installType}s/{$pluginDirectory}";
                }

                if (str_starts_with($pluginDirectory, '/')) {
                    $pluginDirectory = realpath($pluginDirectory);
                } else {
                    $pluginDirectory = realpath(base_path($pluginDirectory));
                }

                // plugin-manager or theme-manager
                $exitCode = Artisan::call("{$installType}:install", [
                    'path' => $pluginDirectory,
                ]);
                $output = Artisan::output();
            break;

            // zipball
            case 'inputZipball':
                $pluginZipball = null;
                $file = $request->file('plugin_zipball');
                if ($file && $file->isValid()) {
                    $dir = storage_path('extensions');
                    $filename = $file->hashName();
                    $file->move($dir, $filename);

                    $pluginZipball = "$dir/$filename";
                }

                if (empty($pluginZipball)) {
                    return back()->with('failure', __('FsLang::tips.install_not_upload_zip'));
                }

                // plugin-manager or theme-manager
                $exitCode = Artisan::call("{$installType}:install", [
                    'path' => $pluginZipball,
                ]);
                $output = Artisan::output();
            break;
        }

        if ($exitCode == 0) {
            return \response($output."\n ".__('FsLang::tips.installSuccess'));
        } else {
            if ($output == '') {
                $output = __('FsLang::tips.viewLog')."\n ".' /storage/logs';
            }

            return \response($output."\n ".__('FsLang::tips.installFailure'));
        }
    }

    public function upgrade(Request $request)
    {
        $unikey = $request->get('unikey');
        $packageType = match ($request->get('type')) {
            default => 'plugin',
            4 => 'theme',
        };
        $installType = $request->get('install_type', 'market');

        // market-manager
        $code = Artisan::call('market:upgrade', [
            'unikey' => $unikey,
            'package_type' => $packageType,
            '--install_type' => $installType,
        ]);

        $message = __('FsLang::tips.upgradeSuccess');
        if ($code != 0) {
            $message = __('FsLang::tips.installFailure');
        }

        return \response()->json([
            'message' => $message,
            'data' => [
                'output' => Artisan::output()."\n".__('FsLang::tips.upgradeSuccess'),
            ],
        ], 200);

        return back()->with('failure', __('FsLang::tips.installFailure'));
    }

    public function update(Request $request)
    {
        if ($request->get('is_enable') != 0) {
            $exitCode = Artisan::call('market:activate', ['unikey' => $request->plugin]);
        } else {
            $exitCode = Artisan::call('market:deactivate', ['unikey' => $request->plugin]);
        }

        return $this->updateSuccess();
    }

    public function uninstall(Request $request)
    {
        if ($request->get('clearData') == 1) {
            $exitCode = Artisan::call('market:remove-plugin', ['unikey' => $request->plugin, '--cleardata' => true]);
        } else {
            $exitCode = Artisan::call('market:remove-plugin', ['unikey' => $request->plugin, '--cleardata' => false]);
        }

        // $exitCode = 0 success
        // $exitCode != 0 fail

        $message = __('FsLang::tips.uninstallSuccess');
        if ($exitCode != 0) {
            $message = __('FsLang::tips.uninstallFailure');
        }

        return response(Artisan::output()."\n".$message);
    }

    public function uninstallTheme(Request $request)
    {
        if ($request->get('clearData') == 1) {
            $theme = $request->theme;
            if (! $theme) {
                abort(404);
            }

            $themeConfig = AppHelper::getThemeConfig($theme);
            $functionKeys = $themeConfig['functionKeys'] ?? [];

            $configItemKeys = Config::whereIn('item_key', collect($functionKeys)
                ->pluck('itemKey'))
                ->where('is_custom', 1)
                ->pluck('item_key');

            ConfigUtility::removeFresnsConfigItems($configItemKeys);

            $exitCode = Artisan::call('market:remove-theme', ['unikey' => $request->theme, '--cleardata' => true]);
        } else {
            $exitCode = Artisan::call('market:remove-theme', ['unikey' => $request->theme, '--cleardata' => false]);
        }

        // $exitCode = 0 success
        // $exitCode != 0 fail

        $message = __('FsLang::tips.uninstallSuccess');
        if ($exitCode != 0) {
            $message = __('FsLang::tips.uninstallFailure');
        }

        return response()->json(['message' => Artisan::output().$message], 200);
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
}
