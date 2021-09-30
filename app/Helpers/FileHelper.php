<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Http\Center\Common\LogService;
use Illuminate\Support\Facades\File;

class FileHelper
{
    // Get all files in the directory
    public static function getAllFiles($path, &$files, $ignoreExtensionArr = [])
    {
        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ($file != '.' && $file != '..') {
                    self::getAllFiles($path.'/'.$file, $files, $ignoreExtensionArr);
                }
            }
            $dp->close();
        }
        if (is_file($path)) {
            $extension = pathinfo($path)['extension'];
            if (in_array($extension, $ignoreExtensionArr)) {
                return;
            }

            $files[] = $path;
        }
    }

    // Get all directories under the directory
    public static function getAllFolders($path, &$files, $ignoreArr = [])
    {
        if (is_dir($path)) {
            $dp = dir($path);
            while ($file = $dp->read()) {
                if ($file != '.' && $file != '..') {
                    $files[] = $path;
                    $files = array_unique($files);
                    self::getAllFolders($path.'/'.$file, $files, $ignoreArr);
                }
            }
            $dp->close();
        }
    }

    // Get the Root Path second level directory
    public static function getFolderDepth($folders, $rootPath, $depth)
    {
        $newFolders = [];
        foreach ($folders as $folder) {
            $suffix = str_replace($rootPath, '', $folder);
            $suffixArr = explode(DIRECTORY_SEPARATOR, $suffix);
            if (is_array($suffixArr) && count($suffixArr) == $depth) {
                $newFolders[] = str_replace($rootPath, '', $folder);
            }
        }

        return $newFolders;
    }

    // Verify that the directory exists, or create it if it does not.
    public static function assetDir($path)
    {
        if (is_dir($path)) {
            return true;
        }

        return File::makeDirectory($path, 0755, true, true);
    }

    /**
     * Unzip the file.
     *
     * @param $fromName / Name of the file being decompressed
     * @param $toName / Which directory to unzip
     * @return false / Success returns true, Failure returns false
     */
    public static function unzip($fromName, $toName)
    {
        if (! file_exists($fromName)) {
            LogService::info('File does not exist', $fromName);

            return false;
        }
        $zipArc = new \ZipArchive();
        if (! $zipArc->open($fromName)) {
            LogService::info('File does not exist');

            return false;
        }
        if (! $zipArc->extractTo($toName)) {
            $zipArc->close();

            return false;
        }

        return $zipArc->close();
    }

    /**
     * The debugging inside this function is done with var_dump.
     *
     * @param $zipFilename
     * @param $path
     * @param $toPath
     */
    public static function zip($zipFilename, $folderName, $path, $toPath)
    {
        $zip = new \ZipArchive();
        $zip->open($zipFilename, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
        foreach ($files as $name =>  $file) {
            // Skip all subdirectories
            if (! $file->isDir()) {
                // var_dump($filePath);
                $filePath = $file->getRealPath();

                // Get file extensions with substr/strlen
                $relativePath = "{$folderName}/".substr($filePath, strlen($path) + 1);

                // var_dump($relativePath);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();

        return true;
    }

    /**
     * Copy all the files and folders under one folder to another folder (keeping the original structure).
     *
     * @param <string> $rootFrom / Source folder path
     * @param <string> $rootTo / Destination folder path
     */
    public static function cp_files($rootFrom, $rootTo)
    {
        $handle = opendir($rootFrom);
        while (false !== ($file = readdir($handle))) {
            // DIRECTORY_SEPARATOR Separator for the system's folder names
            $fileFrom = $rootFrom.DIRECTORY_SEPARATOR.$file;
            $fileTo = $rootTo.DIRECTORY_SEPARATOR.$file;
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir($fileFrom)) {
                mkdir($fileTo, 0755, true, true);
                self::cp_files($fileFrom, $fileTo);
            } else {
                @copy($fileFrom, $fileTo);
                if (strstr($fileTo, 'access_token.txt')) {
                    chmod($fileTo, 0755);
                }
            }
        }
    }
}
