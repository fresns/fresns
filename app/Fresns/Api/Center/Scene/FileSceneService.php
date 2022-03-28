<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Center\Scene;

use App\Fresns\Api\Base\Services\BaseService;
use App\Fresns\Api\Center\Common\LogService;
use App\Fresns\Api\Helpers\FileHelper;

class FileSceneService extends BaseService
{
    // Insert
    public static function createFile()
    {
    }

    // Get the file storage path
    public static function getPath($options)
    {
        $t = time();
        $ym = date('Ym', $t);
        $day = date('d', $t);
        $suffixArr = [];
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_1) {
            $suffixArr = ['mores'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_2) {
            $suffixArr = ['configs', 'system'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_3) {
            $suffixArr = ['configs', 'operating'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_4) {
            $suffixArr = ['configs', 'stickers'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_5) {
            $suffixArr = ['configs', 'user'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_6) {
            $suffixArr = ['avatars', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['images', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['images', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['images', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['images', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['images', 'plugins', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['videos', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['videos', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['videos', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['videos', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['videos', 'plugins', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['audios', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['audios', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['audios', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['audios', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['audios', 'plugins', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['documents', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['documents', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['documents', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['documents', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['documents', 'plugins', $ym, $day];
        }

        if (empty($suffixArr)) {
            // Test Path /avatars/{YYYYMM}/{DD}
            $suffixArr = ['avatars', $ym, $day];
        }

        $basePathArr = [
            base_path(),
            'storage', 'app', 'public',
        ];
        $realPath = implode(DIRECTORY_SEPARATOR, array_merge($basePathArr, $suffixArr));

        // Create a directory
        if (! FileHelper::assetDir($realPath)) {
            LogService::error('Failed to create a directory:', $realPath);

            return false;
        }

        // Spliced as:
        array_unshift($suffixArr, 'public');

        return implode(DIRECTORY_SEPARATOR, $suffixArr);
    }

    // Editor: file upload
    public static function getEditorPath($options)
    {
        $t = time();
        $ym = date('Ym', $t);
        $day = date('d', $t);
        $suffixArr = [];
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_1) {
            $suffixArr = ['temp_files', 'mores'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_2) {
            $suffixArr = ['temp_files', 'configs', 'system'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_3) {
            $suffixArr = ['temp_files', 'configs', 'operating'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_4) {
            $suffixArr = ['temp_files', 'configs', 'stickers'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_5) {
            $suffixArr = ['temp_files', 'configs', 'user'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_6) {
            $suffixArr = ['temp_files', 'avatars', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['temp_files', 'images', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['temp_files', 'images', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['temp_files', 'images', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['temp_files', 'images', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['temp_files', 'images', 'plugins', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['temp_files', 'videos', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['temp_files', 'videos', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['temp_files', 'videos', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['temp_files', 'videos', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['temp_files', 'videos', 'plugins', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['temp_files', 'audios', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['temp_files', 'audios', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['temp_files', 'audios', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['temp_files', 'audios', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['temp_files', 'audios', 'plugins', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['temp_files', 'documents', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['temp_files', 'documents', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['temp_files', 'documents', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['temp_files', 'documents', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['temp_files', 'documents', 'plugins', $ym, $day];
        }

        if (empty($suffixArr)) {
            // Test Path /avatars/{YYYYMM}/{DD}
            $suffixArr = ['avatars', $ym, $day];
        }

        $basePathArr = [
            base_path(),
            'storage', 'app', 'public',
        ];
        $realPath = implode(DIRECTORY_SEPARATOR, array_merge($basePathArr, $suffixArr));

        // Create a directory
        if (! FileHelper::assetDir($realPath)) {
            LogService::error('Failed to create a directory:', $realPath);

            return false;
        }

        // Spliced as:
        array_unshift($suffixArr, 'public');

        return implode(DIRECTORY_SEPARATOR, $suffixArr);
    }

    // Official file storage path
    public static function getFormalEditorPath($options)
    {
        $t = time();
        $ym = date('Ym', $t);
        $day = date('d', $t);
        $suffixArr = [];
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_1) {
            $suffixArr = ['mores'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_2) {
            $suffixArr = ['configs', 'system'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_3) {
            $suffixArr = ['configs', 'operating'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_4) {
            $suffixArr = ['configs', 'stickers'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_5) {
            $suffixArr = ['configs', 'user'];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_6) {
            $suffixArr = ['avatars', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['images', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['images', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['images', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['images', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_1 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['images', 'plugins', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['videos', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['videos', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['videos', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['videos', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_2 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['videos', 'plugins', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['audios', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['audios', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['audios', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['audios', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_3 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['audios', 'plugins', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_7) {
            $suffixArr = ['documents', 'dialogs', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_8) {
            $suffixArr = ['documents', 'posts', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_9) {
            $suffixArr = ['documents', 'comments', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_10) {
            $suffixArr = ['documents', 'extends', $ym, $day];
        }
        if ($options['file_type'] == FileSceneConfig::FILE_TYPE_4 && $options['table_type'] == FileSceneConfig::TABLE_TYPE_11) {
            $suffixArr = ['documents', 'plugins', $ym, $day];
        }

        if (empty($suffixArr)) {
            // Test Path /avatars/{YYYYMM}/{DD}
            $suffixArr = ['avatars', $ym, $day];
        }

        $basePathArr = [
            base_path(),
            'storage', 'app', 'public',
        ];
        $realPath = implode(DIRECTORY_SEPARATOR, array_merge($basePathArr, $suffixArr));
        // Create a directory
        if (! FileHelper::assetDir($realPath)) {
            LogService::error('Failed to create a directory:', $realPath);

            return false;
        }

        // Spliced as:
        // array_unshift($suffixArr, 'public');

        return implode(DIRECTORY_SEPARATOR, $suffixArr);
    }
}
