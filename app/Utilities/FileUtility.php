<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Utilities;

use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Models\File as FileModel;
use App\Models\FileAppend;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUtility
{
    public static function uploadFile(array $bodyInfo, UploadedFile $file)
    {
        if (!Str::isJson($bodyInfo['moreJson'])) {
            return null;
        }

        $fresnsStorage = Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => '/storage',
        ]);

        $storePath = FileHelper::fresnsFileStoragePath($bodyInfo['type'], $bodyInfo['useType']);

        $diskPath = $fresnsStorage->putFile($storePath, $file);

        // $filepath = storage_path('app/public/'.$diskPath);

        return FileUtility::saveFileInfoToDatabase($bodyInfo, $diskPath, $file);
    }

    public static function uploadFileInfo(array $bodyInfo)
    {
        if (!Str::isJson($bodyInfo['fileInfo'])) {
            return null;
        }

        $accountId = PrimaryHelper::fresnsAccountIdByAid($bodyInfo['aid']);
        $userId = PrimaryHelper::fresnsUserIdByUid($bodyInfo['uid']);

        $tableId = $bodyInfo['tableId'];
        if (empty($bodyInfo['tableId'])) {
            $tableId = PrimaryHelper::fresnsPrimaryId($bodyInfo['tableName'], $bodyInfo['tableKey']);
        }

        $fileInfoArr = json_decode($bodyInfo['fileInfo'], true);

        $fileIdArr = [];
        foreach ($fileInfoArr as $fileInfo) {
            $item = [];
            $item['fid'] = Str::random(12);
            $item['type'] = $bodyInfo['type'];
            $item['name'] = $fileInfo['name'];
            $item['mime'] = $fileInfo['mime'] ?? null;
            $item['extension'] = $fileInfo['extension'];
            $item['size'] = $fileInfo['size'];
            $item['md5'] = $bodyInfo['md5'] ?? null;
            $item['sha'] = $bodyInfo['sha'] ?? null;
            $item['sha_type'] = $bodyInfo['shaType'] ?? null;
            $item['path'] = $fileInfo['path'];
            $item['image_width'] = $fileInfo['imageWidth'] ?? null;
            $item['image_height'] = $fileInfo['imageHeight'] ?? null;
            $imageLong = 0;
            if (! empty($fileInfo['image_width'])) {
                if ($fileInfo['image_width'] >= 700) {
                    if ($fileInfo['image_height'] >= $fileInfo['image_width'] * 3) {
                        $imageLong = 1;
                    } else {
                        $imageLong = 0;
                    }
                }
            }
            $item['image_is_long'] = $imageLong ?? 0;
            $item['video_time'] = $fileInfo['videoTime'] ?? null;
            $item['video_cover'] = $fileInfo['videoCover'] ?? null;
            $item['video_gif'] = $fileInfo['videoGif'] ?? null;
            $item['audio_time'] = $fileInfo['audioTime'] ?? null;
            $item['more_json'] = json_encode($fileInfo['moreJson']);

            $fileId = FileModel::create($item)->id;
            $fileIdArr[] = $fileId;

            $append = [];
            $append['file_id'] = $fileId;
            $append['file_type'] = $bodyInfo['type'];
            $append['platform_id'] = $bodyInfo['platformId'];
            $append['use_type'] = $bodyInfo['useType'];
            $append['table_name'] = $bodyInfo['tableName'];
            $append['table_column'] = $bodyInfo['tableColumn'];
            $append['table_id'] = $tableId;
            $append['table_key'] = $bodyInfo['tableKey'] ?? null;
            $append['rating'] = $fileInfo['rating'] ?? 9;
            $append['account_id'] = $accountId;
            $append['user_id'] = $userId;
            $append['original_path'] = $fileInfo['originalPath'] ?? null;

            FileAppend::insert($append);
        }

        $fileTypeName = match ($bodyInfo['type']) {
            1 => 'images',
            2 => 'videos',
            3 => 'audios',
            4 => 'documents',
        };

        $fileInfo = FileHelper::fresnsAntiLinkFileInfoListByIds($fileIdArr)[$fileTypeName];

        return $fileInfo;
    }

    public static function saveFileInfoToDatabase(array $bodyInfo, string $diskPath, UploadedFile $file)
    {
        $fileArr['fid'] = Str::random(12);
        $fileArr['type'] = $bodyInfo['type'];
        $fileArr['name'] = $file->getClientOriginalName();
        $fileArr['mime'] = $file->getMimeType();
        $fileArr['extension'] = $file->getClientOriginalExtension();
        $fileArr['size'] = $file->getSize();
        $fileArr['md5'] = $bodyInfo['md5'] ?? null;
        $fileArr['sha'] = $bodyInfo['sha'] ?? null;
        $fileArr['sha_type'] = $bodyInfo['shaType'] ?? null;
        $fileArr['path'] = $diskPath;
        $fileArr['more_json'] = $bodyInfo['moreJson'] ?? null;
        if ($bodyInfo['type'] == 1) {
            $imageSize = getimagesize($file->path());
            $fileArr['image_width'] = $imageSize[0] ?? null;
            $fileArr['image_height'] = $imageSize[1] ?? null;
            $fileArr['image_is_long'] = 0;
            if (! empty($fileArr['image_width']) >= 700) {
                if ($fileArr['image_height'] >= $fileArr['image_width'] * 3) {
                    $fileArr['image_is_long'] = 1;
                }
            }
        }
        $fileId = FileModel::create($fileArr)->id;

        $accountId = PrimaryHelper::fresnsAccountIdByAid($bodyInfo['aid']);
        $userId = PrimaryHelper::fresnsUserIdByUid($bodyInfo['uid']);

        $tableId = $bodyInfo['tableId'];
        if (empty($bodyInfo['tableId'])) {
            $tableId = PrimaryHelper::fresnsPrimaryId($bodyInfo['tableName'], $bodyInfo['tableKey']);
        }

        $appendInput = [
            'file_id' => $fileId,
            'file_type' => $bodyInfo['type'],
            'platform_id' => $bodyInfo['platformId'],
            'use_type' => $bodyInfo['useType'],
            'table_name' => $bodyInfo['tableName'],
            'table_column' => $bodyInfo['tableColumn'],
            'table_id' => $tableId,
            'table_key' => $bodyInfo['tableKey'] ?? null,
            'account_id' => $accountId,
            'user_id' => $userId,
        ];
        FileAppend::create($appendInput);

        return FileHelper::fresnsFileInfoById($fileId);
    }

    public static function logicalDeletionFiles(array $fileIdsOrFids)
    {
        FileModel::whereIn('id', $fileIdsOrFids)->orWhereIn('fid', $fileIdsOrFids)->delete();

        return true;
    }
}
