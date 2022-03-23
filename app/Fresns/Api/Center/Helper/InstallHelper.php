<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Center\Helper;

use App\Fresns\Api\Helpers\FileHelper;
use App\Fresns\Api\Center\Base\BaseInstaller;
use App\Fresns\Api\Center\Base\PluginConst;
use App\Fresns\Api\Center\Common\LogService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallHelper
{
    public static function findInstaller($uniKey): ?BaseInstaller
    {
        return PluginHelper::findInstaller($uniKey);
    }

    // Installing plugin
    public static function installPluginFile($uniKey, $dirName, $downloadFileName, $options = [])
    {
        $toName = self::getExtensionsRootPath();
        $unzipResult = FileHelper::unzip($downloadFileName, $toName);

        $info = [];
        $info['unzipFromName'] = $downloadFileName;
        $info['unzipToName'] = $toName;
        $info['unzipResult'] = $unzipResult;

        return $info;
    }

    // Local Install: first copy the full amount of files to app/Plugins
    public static function installLocalPluginFile($uniKey, $dirName, $downloadFileName, $options = [])
    {
        $pluginRoot = PluginHelper::pluginRoot();
        $toName = implode(DIRECTORY_SEPARATOR, [$pluginRoot, $dirName]);
        $toName1 = implode(DIRECTORY_SEPARATOR, [$pluginRoot]);
        $files = substr(sprintf('%o', fileperms($downloadFileName)), -4);
        clearstatcache();
        LogService::info('Auth-toName1', $downloadFileName);
        LogService::info('Auth', $files);
        self::copyPluginDirectory($downloadFileName, $toName);
        $info = [];
        $info['unzipFromName'] = $downloadFileName;
        $info['unzipToName'] = $toName;

        return $info;
    }

    // Get the plugin storage directory
    public static function getPluginStorageDir()
    {
        $pathArr = [base_path(), 'public', 'storage', 'plugins'];
        $path = implode(DIRECTORY_SEPARATOR, $pathArr);

        // Create if not present
        $createDir = FileHelper::assetDir($path);
        if (! $createDir) {
            LogService::error('Failed to create directory', $path);

            return false;
        }

        return $path;
    }

    // Get all files of the plugin
    public static function pullPluginResourcesFiles($uniKey)
    {
        $info = [];

        if (! empty($srcPath)) {
            FileHelper::assetDir($destPath);
            File::copyDirectory($srcPath, $destPath);
        }

        // lang
        self::pullLang($uniKey);
    }

    // Handle language files
    public static function pullLang($uniKey)
    {
        $info = [];
        $srcPath = PluginHelper::frameworkLangPath($uniKey);
        $destPath = PluginHelper::pluginLangPath($uniKey);
        $info['lang_path_framework'] = $srcPath;
        $info['lang_path_plugin'] = $destPath;

        $dir = new \DirectoryIterator($srcPath);
        foreach ($dir as $file) {
            // Traversing subdirectories
            if ($file->isDir()) {
                $fileName = $file->getFilename();
                if (in_array($fileName, PluginConst::PLUGIN_SKIP_DIR_ARR)) {
                    continue;
                }

                $filePath = $file->getPath();

                $frameworkLangPath = implode(DIRECTORY_SEPARATOR, [$filePath, $fileName, $uniKey]);
                $pluginLangPath = implode(DIRECTORY_SEPARATOR, [$destPath, $fileName, $uniKey]);

                // Source directory exists
                if (is_dir($frameworkLangPath)) {
                    $info['lang_sub_path_framework'] = $frameworkLangPath;
                    $info['lang_sub_path_plugin'] = $pluginLangPath;
                    FileHelper::assetDir($pluginLangPath);
                    File::copyDirectory($frameworkLangPath, $pluginLangPath);
                }
            }
        }
    }

    // Common: Copy Plugin Directory
    public static function copyPluginDirectory($srcPath, $destPath)
    {
        LogService::Info('srcPath', $srcPath);
        if (! empty($srcPath) || is_dir($srcPath)) {
            LogService::Info('destPath', $destPath);
            FileHelper::assetDir($destPath);
            File::copyDirectory($srcPath, $destPath);
        }
    }

    // Common: Copy Plugin File
    public static function copyPluginFile($srcFile, $destPath)
    {
        if (file_exists($srcFile)) {
            File::copy($srcFile, $destPath);
        }
    }

    // Distribute plugin files to the framework directory
    public static function pushPluginResourcesFiles($uniKey)
    {
        $info = [];
        // Local Plugin Directory
        $extensionAllPath = self::getPluginExtensionPath($uniKey);
        // Plugin Directory
        $pluginAllPath = self::getPluginRuntimePath($uniKey);
        $frameworkAssetsPath = PluginHelper::frameworkAssetsPath($uniKey);
        $extensionAssetsPath = PluginHelper::extensionAssetsPath($uniKey);

        // Delete plugin directory
        (new Filesystem)->deleteDirectory($pluginAllPath);
        // Create Plugin Directory
        (new Filesystem)->ensureDirectoryExists($pluginAllPath);
        // Copy the plugin file to the plugin directory
        (new Filesystem)->copyDirectory($extensionAllPath, $pluginAllPath);
        // Copy plugin assets files
        (new Filesystem)->copyDirectory($extensionAssetsPath, $frameworkAssetsPath);

        // Delete the files in the plugin that need to be distributed
        $deleteRuntimeDirArr = ['assets', 'views', 'lang', 'LICENSE'];
        foreach ($deleteRuntimeDirArr as $subDir) {
            $delSubDir = implode(DIRECTORY_SEPARATOR, [$pluginAllPath, $subDir]);
            if (is_dir($delSubDir)) {
                File::deleteDirectory($delSubDir);
            }
            if (is_file($delSubDir)) {
                File::delete($delSubDir);
            }
        }

        // Initialization file loading
        InstallHelper::freshSystem();

        $pluginConfig = PluginHelper::findPluginConfigClass($uniKey);
        $type = $pluginConfig->type;

        // extension info
        $extensionViewPath = PluginHelper::extensionViewPath($uniKey);
        LogService::info('extensionViewPath', $extensionViewPath);
        // Theme Templates
        if ($type == PluginConst::PLUGIN_TYPE_THEME) {
            $frameworkThemePath = PluginHelper::frameworkThemePath($uniKey);
            LogService::info('frameworkThemePath', $frameworkThemePath);
            self::copyPluginDirectory($extensionViewPath, $frameworkThemePath);
        } else {
            // Plugin view files, distributed directly to the framework views directory, including settings files
            $frameworkViewPath = PluginHelper::frameworkViewPath($uniKey);
            self::copyPluginDirectory($extensionViewPath, $frameworkViewPath);

            $info['extension_view'] = $extensionViewPath;
            $info['framework_view'] = $frameworkViewPath;

            // lang
            self::pushLang($uniKey);
        }
    }

    // Language file synchronization
    public static function pushLang($uniKey)
    {
        $info = [];
        $extensionLangPath = PluginHelper::extensionLangPath($uniKey);
        $frameworkLangPath = PluginHelper::frameworkLangPath($uniKey);

        if (! is_dir($extensionLangPath)) {
            LogService::info('No Language Path');

            return;
        }

        $dir = new \DirectoryIterator($extensionLangPath);
        foreach ($dir as $file) {
            // Traversing subdirectories
            if ($file->isDir()) {
                $fileName = $file->getFilename();
                if (in_array($fileName, PluginConst::PLUGIN_SKIP_DIR_ARR)) {
                    continue;
                }
                $filePath = $file->getPath();

                $extensionSubLangPath = implode(DIRECTORY_SEPARATOR, [$extensionLangPath, $fileName]);

                $frameworkSubLangPath = implode(DIRECTORY_SEPARATOR, [$frameworkLangPath, $fileName, $uniKey]);

                $info['extension_sub_lang_path'] = $extensionSubLangPath;
                $info['framework_sub_lang_path'] = $frameworkSubLangPath;
                LogService::info('curr', $info);

                // extension -> framework
                if (is_dir($extensionSubLangPath)) {
                    self::copyPluginDirectory($extensionSubLangPath, $frameworkSubLangPath);
                }
            }
        }
    }

    // Delete plugin files and directories
    public static function deletePluginFiles($uniKey)
    {
        $info = [];
        $pluginConfig = PluginHelper::findPluginConfigClass($uniKey);
        $type = $pluginConfig->type;

        // Running the home directory
        $runtimeAllPath = self::getPluginRuntimePath($uniKey);

        // Plugin view files
        $frameworkViewPath = PluginHelper::frameworkViewPath($uniKey);

        $frameworkThemePath = PluginHelper::frameworkThemePath($uniKey);

        // Language Directory
        self::deleteLang($uniKey);

        $info['framework_view'] = $frameworkViewPath;
        $info['framework_theme_path'] = $frameworkThemePath;
        $info['runtime_all_path'] = $runtimeAllPath;

        foreach ($info as $key => $path) {
            if (is_dir($path)) {
                File::deleteDirectory($path);
            }
        }

        InstallHelper::freshSystem();
    }

    // Delete language file
    public static function deleteLang($uniKey)
    {
        $info = [];
        $srcPath = PluginHelper::frameworkLangPath($uniKey);
        $info['lang_path_framework'] = $srcPath;

        $dir = new \DirectoryIterator($srcPath);
        foreach ($dir as $file) {
            // Running the home directory
            if ($file->isDir()) {
                $fileName = $file->getFilename();
                if (in_array($fileName, PluginConst::PLUGIN_SKIP_DIR_ARR)) {
                    continue;
                }

                $filePath = $file->getPath();
                $frameworkLangPath = implode(DIRECTORY_SEPARATOR, [$filePath, $fileName, $uniKey]);

                if (is_dir($frameworkLangPath)) {
                    File::deleteDirectory($frameworkLangPath);
                }
            }
        }
    }

    //  All plugins are in this directory before installation
    public static function getExtensionsRootPath()
    {
        $pathArr = [base_path(), 'extensions'];
        $path = implode(DIRECTORY_SEPARATOR, $pathArr);

        return $path;
    }

    // Get the local path of the extensions
    public static function getPluginExtensionPath($dirName)
    {
        $pathArr = [base_path(), 'extensions', $dirName];
        $path = implode(DIRECTORY_SEPARATOR, $pathArr);

        return $path;
    }

    // Get the local path of the plugins
    public static function getPluginRuntimePath($dirName)
    {
        $pathArr = [base_path(), 'app', 'Plugins', $dirName];
        $path = implode(DIRECTORY_SEPARATOR, $pathArr);

        return $path;
    }

    public static function freshSystem()
    {
        $composer = app('composer');
        $composer->dumpAutoloads();

        Artisan::call('clear-compiled'); // Remove the compiled class file
        // Delete cache files
        $deleteDir = implode(DIRECTORY_SEPARATOR, [base_path(), 'bootstrap', 'cache']);
        $deleteFileArr = [
            'config.php',
            'packages.php',
            'services.php',
            'route.php',
        ];
        foreach ($deleteFileArr as $file) {
            $deleteFile = implode(DIRECTORY_SEPARATOR, [$deleteDir, $file]);
            if (is_file($deleteFile)) {
                File::delete($deleteFile);
            }
        }
    }
}
