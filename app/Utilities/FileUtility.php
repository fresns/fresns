<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\CacheHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\SessionKey;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mime\MimeTypes;

class FileUtility
{
    // uploadFile
    public static function uploadFile(array $bodyInfo, array $diskConfig, UploadedFile $file): ?File
    {
        // $bodyInfoExample = [
        //     'platformId' => 'file_usages->platform_id',
        //     'usageType' => 'file_usages->usage_type',
        //     'tableName' => 'file_usages->table_name',
        //     'tableColumn' => 'file_usages->table_column',
        //     'tableId' => 'file_usages->table_id',
        //     'tableKey' => 'file_usages->table_key',
        //     'aid' => 'file_usages->account_id',
        //     'uid' => 'file_usages->user_id',
        //     'type' => 'files->type',
        //     'warningType' => 'files->warning_type',
        //     'moreInfo' => 'files->more_info',
        // ];

        // check file info
        $fileType = $bodyInfo['type'] ?? null;
        $usageType = $bodyInfo['usageType'] ?? null;

        if (empty($fileType) || empty($usageType)) {
            return null;
        }

        $fresnsStorage = Storage::build($diskConfig);

        $storePath = FileHelper::fresnsFileStoragePath($fileType, $usageType);

        $diskPath = $fresnsStorage->putFile($storePath, $file);

        // $filepath = storage_path('app/public/'.$diskPath);

        if (empty($diskPath)) {
            return null;
        }

        $sha256Hash = hash_file('sha256', $file->path());

        $fileInfo = [
            'type' => $fileType,
            'sha' => $sha256Hash,
            'shaType' => 'sha256',
            'path' => $diskPath,
            'audioDuration' => $bodyInfo['audioDuration'] ?? null,
            'videoDuration' => $bodyInfo['videoDuration'] ?? null,
            'videoPosterPath' => $bodyInfo['videoPosterPath'] ?? null,
            'transcodingState' => $fileInfo['transcodingState'] ?? File::TRANSCODING_STATE_WAIT,
            'originalPath' => $fileInfo['originalPath'] ?? null,
            'warningType' => $bodyInfo['warningType'] ?? File::WARNING_NONE,
            'uploaded' => $fileInfo['uploaded'] ?? true,
        ];

        $usageInfo = [
            'usageType' => $usageType,
            'platformId' => $bodyInfo['platformId'] ?? null,
            'tableName' => $bodyInfo['tableName'] ?? null,
            'tableColumn' => $bodyInfo['tableColumn'] ?? null,
            'tableId' => $bodyInfo['tableId'] ?? null,
            'tableKey' => $bodyInfo['tableKey'] ?? null,
            'moreInfo' => $bodyInfo['moreInfo'] ?? null,
            'aid' => $bodyInfo['aid'] ?? null,
            'uid' => $bodyInfo['uid'] ?? null,
            'remark' => $bodyInfo['remark'] ?? null,
        ];

        return FileUtility::uploadFileInfo($file, $fileInfo, $usageInfo);
    }

    // uploadFileInfo
    public static function uploadFileInfo(UploadedFile $file, array $fileInfo, ?array $usageInfo = []): ?File
    {
        // $fileInfoExample = [
        //     'type' => 'files->type',
        //     'sha' => 'files->sha',
        //     'shaType' => 'files->sha_type',
        //     'path' => 'files->path',
        //     'audioDuration' => 'Audio Only: files->audio_duration',
        //     'videoDuration' => 'Video Only: files->video_duration',
        //     'videoPosterPath' => 'Video Only: files->video_poster_path',
        //     'transcodingState' => 'files->transcoding_state', // audio or video only
        //     'originalPath' => 'files->original_path',
        //     'warningType' => 'files->warning_type',
        //     'uploaded' => 'files->is_uploaded',
        // ];

        // check file info
        $fileType = $fileInfo['type'] ?? null;
        $filePath = $fileInfo['path'] ?? null;

        if (empty($fileType) || empty($filePath)) {
            return null;
        }

        $name = $file->getClientOriginalName();
        $mime = $file->getMimeType();
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();
        $imageWidth = null;
        $imageHeight = null;

        if ($fileInfo['type'] == File::TYPE_IMAGE) {
            $imageSize = getimagesize($file->path());

            $imageWidth = $imageSize[0] ?? null;
            $imageHeight = $imageSize[1] ?? null;
        }

        $fileInfo['name'] = $name;
        $fileInfo['mime'] = $mime;
        $fileInfo['extension'] = $extension;
        $fileInfo['size'] = $size;
        $fileInfo['imageWidth'] = $imageWidth;
        $fileInfo['imageHeight'] = $imageHeight;

        return FileUtility::saveFileInfo($fileInfo, $usageInfo);
    }

    // saveFileInfo
    public static function saveFileInfo(array $fileInfo, ?array $usageInfo = []): ?File
    {
        // $fileInfoExample = [
        //     'type' => 'files->type', // required
        //     'name' => 'files->name', // required
        //     'mime' => 'files->mime',
        //     'extension' => 'files->extension', // required
        //     'size' => 'files->size', // required, unit: Byte
        //     'sha' => 'files->sha',
        //     'shaType' => 'files->sha_type',
        //     'path' => 'files->path', // required
        //     'imageWidth' => 'Image Only: files->image_width',
        //     'imageHeight' => 'Image Only: files->image_height',
        //     'audioDuration' => 'Audio Only: files->audio_duration',
        //     'videoDuration' => 'Video Only: files->video_duration',
        //     'videoPosterPath' => 'Video Only: files->video_poster_path',
        //     'transcodingState' => 'files->transcoding_state', // audio or video Only
        //     'originalPath' => 'files->original_path',
        //     'warningType' => 'files->warning_type',
        //     'uploaded' => 'files->is_uploaded',
        // ];

        // check file info
        $checkItems = [
            'type' => $fileInfo['type'] ?? null,
            'name' => $fileInfo['name'] ?? null,
            'extension' => $fileInfo['extension'] ?? null,
            'size' => $fileInfo['size'] ?? null,
            'path' => $fileInfo['path'] ?? null,
        ];

        $filteredItems = array_filter($checkItems);

        if (count($filteredItems) < count($checkItems)) {
            return null;
        }

        // file model
        $file = File::where('path', $fileInfo['path'])->first();
        if (! $file) {
            $imageWidth = $fileInfo['imageWidth'] ?: null;
            $imageHeight = $fileInfo['imageHeight'] ?: null;
            $imageIsLong = false;

            if ($fileInfo['type'] == File::TYPE_IMAGE && $imageWidth >= 700) {
                if ($imageHeight >= $imageWidth * 3) {
                    $imageIsLong = true;
                }
            }

            $mime = $fileInfo['mime'] ?? null;
            if (empty($mime)) {
                $mimeTypes = new MimeTypes();

                $types = $mimeTypes->getMimeTypes($fileInfo['extension']);

                $mime = $types[0] ?? 'application/octet-stream';
            }

            $fileInput = [
                'type' => $fileInfo['type'],
                'name' => $fileInfo['name'],
                'mime' => $mime,
                'extension' => $fileInfo['extension'],
                'size' => $fileInfo['size'],
                'sha' => $fileInfo['sha'] ?? null,
                'sha_type' => $fileInfo['shaType'] ?? null,
                'path' => $fileInfo['path'],
                'image_width' => $imageWidth,
                'image_height' => $imageHeight,
                'image_is_long' => $imageIsLong,
                'audio_duration' => $fileInfo['audioDuration'] ?? null,
                'video_duration' => $fileInfo['videoDuration'] ?? null,
                'video_poster_path' => $fileInfo['videoPosterPath'] ?? null,
                'transcoding_state' => $fileInfo['transcodingState'] ?? File::TRANSCODING_STATE_WAIT,
                'original_path' => $fileInfo['originalPath'] ?? null,
                'warning_type' => $bodyInfo['warningType'] ?? File::WARNING_NONE,
                'is_uploaded' => $fileInfo['uploaded'] ?? true,
            ];

            $file = File::create($fileInput);
        }

        if ($usageInfo) {
            FileUtility::saveFileUsageInfo($file->type, $file->id, $usageInfo);
        }

        return $file;
    }

    // saveFileUsageInfo
    public static function saveFileUsageInfo(int $fileType, int $fileId, ?array $usageInfo = []): ?FileUsage
    {
        // $usageInfoExample = [
        //     'usageType' => 'file_usages->usage_type',
        //     'platformId' => 'file_usages->platform_id',
        //     'tableName' => 'file_usages->table_name',
        //     'tableColumn' => 'file_usages->table_column',
        //     'tableId' => 'file_usages->table_id',
        //     'tableKey' => 'file_usages->table_key',
        //     'sortOrder' => 'file_usages->sort_order',
        //     'moreInfo' => [
        //         // files->more_info
        //     ],
        //     'aid' => 'file_usages->account_id',
        //     'uid' => 'file_usages->user_id',
        //     'remark' => 'file_usages->remark',
        // ];

        // check usage info
        $usageType = $usageInfo['usageType'] ?? null;
        $tableName = $usageInfo['tableName'] ?? null;
        $tableId = $usageInfo['tableId'] ?? null;
        $tableKey = $usageInfo['tableKey'] ?? null;

        if (empty($tableId) && empty($tableKey)) {
            return null;
        }

        if (empty($usageType) || empty($tableName)) {
            return null;
        }

        $aid = $usageInfo['aid'] ?? null;
        $accountId = PrimaryHelper::fresnsPrimaryId('account', $aid);

        $userId = null;
        $uid = $usageInfo['uid'] ?? null;
        if ($uid) {
            $userId = PrimaryHelper::fresnsPrimaryId('user', $uid);
            $accountId = PrimaryHelper::fresnsAccountIdByUserId($userId);
        }

        $useInput = [
            'file_id' => $fileId,
            'file_type' => $fileType,
            'usage_type' => $usageType,
            'platform_id' => $usageInfo['platformId'] ?? SessionKey::PLATFORM_OTHER,
            'table_name' => $tableName,
            'table_column' => $usageInfo['tableColumn'] ?? 'id',
            'table_id' => $tableId,
            'table_key' => $tableKey,
            'sort_order' => $usageInfo['sortOrder'] ?? 9,
            'more_info' => $usageInfo['moreInfo'] ?? null,
            'account_id' => $accountId,
            'user_id' => $userId,
            'remark' => $usageInfo['remark'] ?? null,
        ];

        return FileUsage::create($useInput);
    }

    // logicalDeletionFiles
    public static function logicalDeletionFiles(array $fileIdsOrFids): void
    {
        foreach ($fileIdsOrFids as $id) {
            if (StrHelper::isPureInt($id)) {
                $file = File::where('id', $id)->first();
            } else {
                $file = File::where('fid', $id)->first();
            }

            if (empty($file)) {
                continue;
            }

            $file->delete();

            CacheHelper::clearDataCache('file', $file->fid);
        }
    }
}
