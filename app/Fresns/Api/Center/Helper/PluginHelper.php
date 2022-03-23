<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Center\Helper;

use App\Fresns\Api\Center\Base\BaseInstaller;
use App\Fresns\Api\Center\Base\BasePluginConfig;
use App\Fresns\Api\Center\Base\PluginConst;
use App\Fresns\Api\Center\Common\LogService;
use App\Fresns\Api\FsDb\FresnsPlugins\FresnsPlugins;
use App\Fresns\Api\Helpers\FileHelper;
use App\Fresns\Api\Http\FresnsInstall\InstallService;
use Illuminate\Support\Facades\File;

class PluginHelper
{
    // Get Plugin Class
    public static function findPluginClass($uniKey)
    {
        $pluginClass = "\\App\Fresns\Api\\Plugins\\{$uniKey}\\Plugin";
        LogService::info('Get Plugin Class', $pluginClass);
        if (! class_exists($pluginClass)) {
            LogService::error('Plugin Class: Does not exist', $pluginClass);

            return null;
        }

        return new $pluginClass();
    }

    // Get Plugin Config Class
    public static function findPluginConfigClass($uniKey): ?BasePluginConfig
    {
        $configClass = "\\App\Fresns\Api\\Plugins\\{$uniKey}\\PluginConfig";
        if (! class_exists($configClass)) {
            LogService::error('Config Class: Does not exist', $configClass);

            return null;
        }

        return new $configClass();
    }

    // Get Plugin Installer Class
    public static function findInstaller($uniKey): ?BaseInstaller
    {
        $installClass = "\\App\Fresns\Api\\Plugins\\{$uniKey}\\Installer";
        if (! class_exists($installClass)) {
            LogService::error('Installer Class: Does not exist', $installClass);

            return null;
        }

        return new $installClass();
    }

    // Extension "assets" Path
    public static function extensionAssetsPath($uniKey)
    {
        $extensionRootPath = InstallHelper::getPluginExtensionPath($uniKey);
        $path = implode(DIRECTORY_SEPARATOR, [$extensionRootPath, 'assets']);

        return $path;
    }

    // Extension "views" Path
    public static function extensionViewPath($uniKey)
    {
        $extensionRootPath = InstallHelper::getPluginExtensionPath($uniKey);
        $path = implode(DIRECTORY_SEPARATOR, [$extensionRootPath, 'views']);

        return $path;
    }

    // Extension "lang" Path
    public static function extensionLangPath($uniKey)
    {
        $extensionRootPath = InstallHelper::getPluginExtensionPath($uniKey);
        $path = implode(DIRECTORY_SEPARATOR, [$extensionRootPath, 'lang']);

        return $path;
    }

    // Extension "Routes" Path
    public static function extensionRoutePath($uniKey)
    {
        $extensionRootPath = InstallHelper::getPluginExtensionPath($uniKey);
        $path = implode(DIRECTORY_SEPARATOR, [$extensionRootPath, 'Routes']);

        return $path;
    }

    // Plugin "lang" Path
    public static function pluginLangPath($uniKey)
    {
        $currPluginRoot = self::currPluginRoot($uniKey);
        $path = implode(DIRECTORY_SEPARATOR, [$currPluginRoot, 'Resources', 'lang']);

        return $path;
    }

    // Framework "assets" Path
    public static function frameworkAssetsPath($uniKey)
    {
        $path = implode(DIRECTORY_SEPARATOR, [PluginHelper::assetsRoot(), $uniKey]);

        return $path;
    }

    // Framework "views" Path
    public static function frameworkViewPath($uniKey)
    {
        $path = implode(DIRECTORY_SEPARATOR, [PluginHelper::viewRoot(), $uniKey]);

        return $path;
    }

    // Framework "theme" Path
    public static function frameworkThemePath($uniKey)
    {
        $path = implode(DIRECTORY_SEPARATOR, [PluginHelper::themeRoot(), $uniKey]);

        return $path;
    }

    // Framework "lang" Path
    public static function frameworkLangPath($uniKey)
    {
        $langRoot = self::langRoot();

        return $langRoot;
    }

    // Delete files according to unikey
    public static function uninstallByUniKey($uniKey)
    {
        // Delete File
        InstallHelper::deletePluginFiles($uniKey);

        // Delete Directory
        $pluginPath = PluginHelper::currPluginRoot($uniKey);
        if (is_dir($pluginPath)) {
            File::deleteDirectory($pluginPath);
        }

        $info = [];
        $info['pluginDir'] = $pluginPath;

        return $info;
    }

    // Get a plugin directory
    public static function currPluginRoot($uniKey)
    {
        $pathArr = [base_path(), 'app', 'Plugins', $uniKey];
        $path = implode(DIRECTORY_SEPARATOR, $pathArr);

        return $path;
    }

    // Plugin run root directory
    public static function pluginRoot()
    {
        $pathArr = [base_path(), 'app', 'Plugins'];

        return implode(DIRECTORY_SEPARATOR, $pathArr);
    }

    // Theme template directory
    public static function themeRoot()
    {
        $pathArr = [base_path(), 'resources', 'views', 'themes'];

        return implode(DIRECTORY_SEPARATOR, $pathArr);
    }

    // Plugin view directory
    public static function viewRoot()
    {
        $pathArr = [base_path(), 'resources', 'views', 'plugins'];

        return implode(DIRECTORY_SEPARATOR, $pathArr);
    }

    // Plugin lang directory
    public static function langRoot()
    {
        $pathArr = [base_path(), 'resources', 'lang'];

        return implode(DIRECTORY_SEPARATOR, $pathArr);
    }

    // all assets directory
    public static function assetsRoot()
    {
        $pathArr = [base_path(), 'public', 'assets'];

        return implode(DIRECTORY_SEPARATOR, $pathArr);
    }

    // Download Path
    public static function getDownloadPath()
    {
        $pathArr = [
            base_path(),
            'public',
            'storage',
            'extensions',
        ];
        $downloadPath = implode(DIRECTORY_SEPARATOR, $pathArr);
        FileHelper::assetDir($downloadPath);

        return $downloadPath;
    }

    // Whether the plugin is installed or enabled
    public static function pluginCanUse($uniKey)
    {
        if (InstallService::mode()) {
            return false;
        }
        // Get installation class
        $installer = InstallHelper::findInstaller($uniKey);
        if (empty($installer)) {
            LogService::info('info', 'Plugin Class: not found');

            return false;
        }
        $plugin = FresnsPlugins::where('unikey', $uniKey)->where('is_enable', 1)->first();
        if (empty($plugin)) {
            LogService::info('info', 'Plugin Not Enabled');

            return false;
        }

        return true;
    }

    public static function getPluginImageUrl(BasePluginConfig $pluginConfig)
    {
        $type = $pluginConfig->type;
        $uniKey = $pluginConfig->uniKey;

        $imgName = PluginConst::PLUGIN_IMAGE_NAME;
        $domain = request()->server('HTTP_ORIGIN');
        LogService::info('server', request()->server());

        LogService::info('domain', $domain);

        $url = $domain."/assets/{$uniKey}/{$imgName}";
        LogService::info('url', $url);

        if ($type == PluginConst::PLUGIN_TYPE_THEME) {
            $url = $domain."/assets/{$uniKey}/{$imgName}";
        }

        return $url;
    }
}
