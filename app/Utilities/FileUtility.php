<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\File;
use App\Models\FileUsage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUtility
{
    /**
     * Get the mime-type of a given file.
     *
     * @param  string  $path
     * @return string|false
     */
    public static function mimeTypeFromPath($path)
    {
        return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
    }

    /**
     * Get the mime-type of a given content.
     *
     * @param  string  $path
     * @return string|false
     */
    public static function mimeTypeFromContent($content)
    {
        return finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $content);
    }

    // uploadFile
    public static function uploadFile(array $bodyInfo, array $diskConfig, UploadedFile $file)
    {
        if (! Str::isJson($bodyInfo['moreJson'])) {
            return null;
        }

        // $diskConfig >> /config/filesystems.php
        // local, ftp, sftp
        $fresnsStorage = Storage::build($diskConfig);

        $storePath = FileHelper::fresnsFileStoragePath($bodyInfo['type'], $bodyInfo['usageType']);

        $diskPath = $fresnsStorage->putFile($storePath, $file);

        // $filepath = storage_path('app/public/'.$diskPath);

        return FileUtility::saveFileInfoToDatabase($bodyInfo, $diskPath, $file);
    }

    // uploadFileInfo
    public static function uploadFileInfo(array $bodyInfo)
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
        //     'type' => 'files->type and file_usages->file_type',
        //     'fileInfo' => [
        //         [
        //             'name' => 'files->name',
        //             'mime' => 'files->mime',
        //             'extension' => 'files->extension',
        //             'size' => 'files->size', // Unit: Byte
        //             'md5' => 'files->md5',
        //             'sha' => 'files->sha',
        //             'shaType' => 'files->sha_type',
        //             'disk' => 'files->disk',
        //             'path' => 'files->path',
        //             'imageWidth' => 'Image Only: files->image_width',
        //             'imageHeight' => 'Image Only: files->image_height',
        //             'videoTime' => 'Video Only: files->video_time',
        //             'videoCoverPath' => 'Video Only: files->video_cover_path',
        //             'videoGifPath' => 'Video Only: files->video_gif_path',
        //             'audioTime' => 'Audio Only: files->audio_time',
        //             'transcodingState' => 'Audio and Video Only: files->transcoding_state',
        //             'moreJson' => [
        //                 // files->more_json
        //             ],
        //             'originalPath' => 'files->original_path',
        //             'rating' => 'file_usages->rating',
        //             'remark' => 'file_usages->remark',
        //         ]
        //     ]
        // ];

        // if (! Str::isJson($bodyInfo['fileInfo'])) {
        //     return null;
        // }

        $fileIdArr = [];
        foreach ($bodyInfo['fileInfo'] as $fileInfo) {
            $imageIsLong = 0;
            if ($bodyInfo['type'] == 1 && ! empty($fileInfo['imageWidth']) >= 700) {
                if ($fileInfo['imageHeight'] >= $fileInfo['imageWidth'] * 3) {
                    $imageIsLong = 1;
                }
            }

            $fileInput = [
                'type' => $bodyInfo['type'], // bodyInfo
                'name' => $fileInfo['name'],
                'mime' => $fileInfo['mime'] ?? null,
                'extension' => $fileInfo['extension'],
                'size' => $fileInfo['size'],
                'md5' => $fileInfo['md5'] ?? null,
                'sha' => $fileInfo['sha'] ?? null,
                'sha_type' =>  $fileInfo['shaType'] ?? null,
                'disk' =>  $fileInfo['disk'] ?? 'remote',
                'path' => $fileInfo['path'],
                'image_width' => $fileInfo['imageWidth'] ?? null,
                'image_height' => $fileInfo['imageHeight'] ?? null,
                'image_is_long' => $imageIsLong,
                'video_time' => $fileInfo['videoTime'] ?? null,
                'video_cover_path' => $fileInfo['videoCoverPath'] ?? null,
                'video_gif_path' => $fileInfo['videoGifPath'] ?? null,
                'audio_time' => $fileInfo['audioTime'] ?? null,
                'more_json' => $fileInfo['moreJson'],
                'original_path' => $fileInfo['originalPath'] ?? null,
            ];
            $fileId = File::create($fileInput)->id;

            $accountId = PrimaryHelper::fresnsAccountIdByAid($bodyInfo['aid']);
            $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($bodyInfo['uid']);

            $tableId = $bodyInfo['tableId'];
            if (empty($bodyInfo['tableId'])) {
                $tableId = PrimaryHelper::fresnsPrimaryId($bodyInfo['tableName'], $bodyInfo['tableKey']);
            }

            $useInput = [
                'file_id' => $fileId,
                'file_type' => $bodyInfo['type'],
                'usage_type' => $bodyInfo['usageType'],
                'platform_id' => $bodyInfo['platformId'],
                'table_name' => $bodyInfo['tableName'],
                'table_column' => $bodyInfo['tableColumn'],
                'table_id' => $tableId,
                'table_key' => $bodyInfo['tableKey'] ?? null,
                'rating' => $fileInfo['rating'] ?? 9,
                'remark' => $fileInfo['remark'] ?? null,
                'account_id' => $accountId,
                'user_id' => $userId,
            ];

            FileUsage::create($useInput);

            $fileIdArr[] = $fileId;
        }

        $fileTypeName = match (intval($bodyInfo['type'])) {
            default => throw new \RuntimeException('Unknown file type: '.$bodyInfo['type']),
            1 => 'images',
            2 => 'videos',
            3 => 'audios',
            4 => 'documents',
        };

        $fileInfo = FileHelper::fresnsFileInfoListByIds($fileIdArr)[$fileTypeName];

        return $fileInfo;
    }

    // saveFileInfoToDatabase
    public static function saveFileInfoToDatabase(array $bodyInfo, string $diskPath, UploadedFile $file)
    {
        $imageWidth = null;
        $imageHeight = null;
        $imageIsLong = 0;
        if ($bodyInfo['type'] == 1) {
            $imageSize = getimagesize($file->path());
            $imageWidth = $imageSize[0] ?? null;
            $imageHeight = $imageSize[1] ?? null;

            if (! empty($imageWidth) >= 700) {
                if ($imageHeight >= $imageWidth * 3) {
                    $imageIsLong = 1;
                }
            }
        }

        $fileInput = [
            'type' => $bodyInfo['type'],
            'name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'md5' => $bodyInfo['md5'] ?? null,
            'sha' => $bodyInfo['sha'] ?? null,
            'sha_type' =>  $bodyInfo['shaType'] ?? null,
            'disk' =>  $bodyInfo['disk'] ?? 'remote',
            'path' => $diskPath,
            'image_width' => $imageWidth,
            'image_height' => $imageHeight,
            'image_is_long' => $imageIsLong,
            'more_json' => $bodyInfo['moreJson'] ?? null,
        ];

        $fileId = File::create($fileInput)->id;

        $accountId = PrimaryHelper::fresnsAccountIdByAid($bodyInfo['aid']);
        $userId = PrimaryHelper::fresnsUserIdByUidOrUsername($bodyInfo['uid']);

        $tableId = $bodyInfo['tableId'];
        if (empty($bodyInfo['tableId'])) {
            $tableId = PrimaryHelper::fresnsPrimaryId($bodyInfo['tableName'], $bodyInfo['tableKey']);
        }

        $useInput = [
            'file_id' => $fileId,
            'file_type' => $bodyInfo['type'],
            'usage_type' => $bodyInfo['usageType'],
            'platform_id' => $bodyInfo['platformId'],
            'table_name' => $bodyInfo['tableName'],
            'table_column' => $bodyInfo['tableColumn'],
            'table_id' => $tableId,
            'table_key' => $bodyInfo['tableKey'] ?? null,
            'account_id' => $accountId,
            'user_id' => $userId,
        ];
        FileUsage::create($useInput);

        return FileHelper::fresnsFileInfoById($fileId);
    }

    // logicalDeletionFiles
    public static function logicalDeletionFiles(array $fileIdsOrFids)
    {
        foreach ($fileIdsOrFids as $id) {
            if (StrHelper::isPureInt($id)) {
                $file = File::where('id', $id)->first();
            } else {
                $file = File::where('fid', $id)->first();
            }

            $file?->delete();
        }

        return true;
    }
}
