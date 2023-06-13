<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
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
        AppUtility::checkPluginsStatus(Plugin::TYPE_PLUGIN);
        $plugins = Plugin::type(Plugin::TYPE_PLUGIN);

        $isEnabled = match ($request->status) {
            'active' => 1,
            'inactive' => 0,
            default => null,
        };

        if (! is_null($isEnabled)) {
            $plugins->isEnabled($isEnabled);
        }

        $plugins = $plugins->latest()->get();

        $enableCount = Plugin::type(Plugin::TYPE_PLUGIN)->isEnabled()->count();
        $disableCount = Plugin::type(Plugin::TYPE_PLUGIN)->isEnabled(false)->count();

        return view('FsView::extensions.plugins', compact('plugins', 'enableCount', 'disableCount', 'isEnabled'));
    }

    public function panelIndex(Request $request)
    {
        AppUtility::checkPluginsStatus(Plugin::TYPE_PANEL);
        $panels = Plugin::type(Plugin::TYPE_PANEL);

        $isEnabled = match ($request->status) {
            'active' => 1,
            'inactive' => 0,
            default => null,
        };

        if ($isEnabled) {
            $panels->isEnabled($isEnabled);
        }

        $panels = $panels->latest()->get();

        $enableCount = Plugin::type(Plugin::TYPE_PANEL)->isEnabled()->count();
        $disableCount = Plugin::type(Plugin::TYPE_PANEL)->where('is_enabled', false)->count();

        return view('FsView::extensions.panels', compact('panels', 'enableCount', 'disableCount', 'isEnabled'));
    }

    public function engineIndex()
    {
        AppUtility::checkPluginsStatus(Plugin::TYPE_ENGINE);
        $engines = Plugin::type(Plugin::TYPE_ENGINE)->latest()->get();

        $configKeys = [];
        $engines->each(function ($engine) use (&$configKeys) {
            $configKeys[] = $engine->fskey.'_Desktop';
            $configKeys[] = $engine->fskey.'_Mobile';
        });

        $configs = Config::whereIn('item_key', $configKeys)->get();
        $pluginKeys = $configs->pluck('item_value')->filter();
        $plugins = Plugin::whereIn('fskey', $pluginKeys)->get()->mapWithKeys(function ($plugin, $key) {
            return [$plugin->fskey => $plugin->name];
        })->toArray();

        $themes = Plugin::type(Plugin::TYPE_THEME)->latest()->get();

        $FresnsEngine = Config::where('item_key', 'FresnsEngine')->first()?->item_value;
        $themeFskey['desktop'] = Config::where('item_key', 'FresnsEngine_Desktop')->value('item_value');
        $themeFskey['mobile'] = Config::where('item_key', 'FresnsEngine_Mobile')->value('item_value');

        $themeName['desktop'] = Plugin::where('fskey', $themeFskey['desktop'])->value('name');
        $themeName['mobile'] = Plugin::where('fskey', $themeFskey['mobile'])->value('name');

        return view('FsView::extensions.engines', compact('engines', 'configs', 'themes', 'plugins', 'FresnsEngine', 'themeFskey', 'themeName'));
    }

    public function updateDefaultEngine(Request $request)
    {
        if ($request->get('is_enabled') != 0) {
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

    public function updateEngineTheme(string $fskey, Request $request)
    {
        $desktopKey = $fskey.'_Desktop';
        $mobileKey = $fskey.'_Mobile';

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
        $themes = Plugin::type(Plugin::TYPE_THEME)->latest()->get();

        return view('FsView::extensions.themes', compact('themes'));
    }

    public function install(Request $request)
    {
        $installMethod = $request->install_method;

        switch ($installMethod) {
            // fskey
            case 'inputFskey':
                $pluginFskey = $request->plugin_fskey;

                if (empty($pluginFskey)) {
                    return back()->with('failure', __('FsLang::tips.install_not_entered_key'));
                }

                // market-manager
                $exitCode = Artisan::call('market:require', [
                    'fskey' => $pluginFskey,
                    '--install_type' => 'market',
                ]);
                $output = Artisan::output();
                break;

                // directory
            case 'inputDirectory':
                $pluginDirectory = $request->plugin_directory;

                if (empty($pluginDirectory)) {
                    return back()->with('failure', __('FsLang::tips.install_not_entered_directory'));
                }

                // plugin-manager or theme-manager
                $exitCode = Artisan::call('market:require', [
                    'fskey' => $pluginDirectory,
                    '--install_type' => 'local',
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
                $exitCode = Artisan::call('market:require', [
                    'fskey' => $pluginZipball,
                    '--install_type' => 'local',
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
        $fskey = $request->get('fskey');

        // market-manager
        $code = Artisan::call('market:upgrade', [
            'fskey' => $fskey,
            '--install_type' => 'market',
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
        if ($request->get('is_enabled') != 0) {
            $exitCode = Artisan::call('market:activate', ['fskey' => $request->plugin]);
        } else {
            $exitCode = Artisan::call('market:deactivate', ['fskey' => $request->plugin]);
        }

        return $this->updateSuccess();
    }

    public function uninstall(Request $request)
    {
        if ($request->get('clearData') == 1) {
            $exitCode = Artisan::call('market:remove-plugin', ['fskey' => $request->plugin, '--cleardata' => true]);
        } else {
            $exitCode = Artisan::call('market:remove-plugin', ['fskey' => $request->plugin, '--cleardata' => false]);
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

            if ($functionKeys) {
                $itemKeys = array_map(function ($item) {
                    return $item['itemKey'];
                }, $functionKeys);

                ConfigUtility::removeFresnsConfigItems($itemKeys);
            }

            $exitCode = Artisan::call('market:remove-theme', ['fskey' => $request->theme]);
        } else {
            $exitCode = Artisan::call('market:remove-theme', ['fskey' => $request->theme]);
        }

        // $exitCode = 0 success
        // $exitCode != 0 fail

        $message = __('FsLang::tips.uninstallSuccess');
        if ($exitCode != 0) {
            $message = __('FsLang::tips.uninstallFailure');
        }

        return response(Artisan::output()."\n".$message);
    }

    public function updateCode(Request $request)
    {
        $plugin = Plugin::where('fskey', $request->input('pluginFskey'))->first();

        if ($plugin) {
            $plugin->upgrade_code = $request->upgradeCode;
            $plugin->save();

            return $this->updateSuccess();
        }

        return back()->with('failure', __('FsLang::tips.plugin_not_exists'));
    }
}
