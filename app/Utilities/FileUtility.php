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
use App\Models\ArchiveUsage;
use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\File;
use App\Models\FileUsage;
use App\Models\SessionKey;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Mime\MimeTypes;

class FileUtility
{
    // uploadFile
    public static function uploadFile(array $bodyInfo, array $diskConfig, UploadedFile $file): ?File
    {
        // $bodyInfoExample = [
        //     'type' => 'files->type',
        //     'width' => 'files->width', // image and video Only
        //     'height' => 'files->height', // image and video Only
        //     'duration' => 'files->duration', // audio and video Only
        //     'warningType' => 'files->warning_type',

        //     'usageType' => 'file_usages->usage_type',
        //     'platformId' => 'file_usages->platform_id',
        //     'tableName' => 'file_usages->table_name',
        //     'tableColumn' => 'file_usages->table_column',
        //     'tableId' => 'file_usages->table_id',
        //     'tableKey' => 'file_usages->table_key',
        //     'sortOrder' => 'file_usages->sort_order',
        //     'moreInfo' => [
        //         // file_usages->more_info
        //     ],
        //     'aid' => 'file_usages->account_id',
        //     'uid' => 'file_usages->user_id',
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
            'width' => $bodyInfo['width'] ?? null,
            'height' => $bodyInfo['height'] ?? null,
            'duration' => $bodyInfo['duration'] ?? null,
            'sha' => $sha256Hash,
            'shaType' => 'sha256',
            'warningType' => $bodyInfo['warningType'] ?? File::WARNING_NONE,
            'path' => $diskPath,
            'uploaded' => true,
        ];

        $usageInfo = [
            'usageType' => $usageType,
            'platformId' => $bodyInfo['platformId'] ?? null,
            'tableName' => $bodyInfo['tableName'] ?? null,
            'tableColumn' => $bodyInfo['tableColumn'] ?? null,
            'tableId' => $bodyInfo['tableId'] ?? null,
            'tableKey' => $bodyInfo['tableKey'] ?? null,
            'sortOrder' => $bodyInfo['sortOrder'] ?? 9,
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
        //     'width' => 'files->width', // image and video Only
        //     'height' => 'files->height', // image and video Only
        //     'duration' => 'files->duration', // audio and video Only
        //     'sha' => 'files->sha',
        //     'shaType' => 'files->sha_type',
        //     'warningType' => 'files->warning_type',
        //     'path' => 'files->path',
        //     'transcodingState' => 'files->transcoding_state', // audio and video only
        //     'videoPosterPath' => 'files->video_poster_path', // video only
        //     'originalPath' => 'files->original_path',
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
        $mediaWidth = null;
        $mediaHeight = null;

        if ($fileInfo['type'] == File::TYPE_IMAGE) {
            $imageSize = getimagesize($file->path());

            $mediaWidth = $fileInfo['width'] ?? $imageSize[0] ?? null;
            $mediaHeight = $fileInfo['height'] ?? $imageSize[1] ?? null;
        }

        $fileInfo['name'] = $name;
        $fileInfo['mime'] = $mime;
        $fileInfo['extension'] = $extension;
        $fileInfo['size'] = $size;
        $fileInfo['width'] = $mediaWidth;
        $fileInfo['height'] = $mediaHeight;

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
        //     'width' => 'files->width', // image and video Only
        //     'height' => 'files->height', // image and video Only
        //     'duration' => 'files->duration', // audio and video Only
        //     'sha' => 'files->sha',
        //     'shaType' => 'files->sha_type',
        //     'warningType' => 'files->warning_type',
        //     'path' => 'files->path', // required
        //     'transcodingState' => 'files->transcoding_state', // audio and video only
        //     'videoPosterPath' => 'files->video_poster_path', // video only
        //     'originalPath' => 'files->original_path',
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
            $imageWidth = $fileInfo['width'] ?: null;
            $imageHeight = $fileInfo['height'] ?: null;
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
                'width' => $fileInfo['width'] ?? null,
                'height' => $fileInfo['height'] ?? null,
                'duration' => $fileInfo['duration'] ?? null,
                'sha' => $fileInfo['sha'] ?? null,
                'sha_type' => $fileInfo['shaType'] ?? null,
                'warning_type' => $fileInfo['warningType'] ?? File::WARNING_NONE,
                'path' => $fileInfo['path'],
                'transcoding_state' => $fileInfo['transcodingState'] ?? File::TRANSCODING_STATE_WAIT,
                'video_poster_path' => $fileInfo['videoPosterPath'] ?? null,
                'original_path' => $fileInfo['originalPath'] ?? null,
                'is_long_image' => $imageIsLong,
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

    // useFile
    public static function useFile(int $userId, int|string $fileIdOrFid, string $tableName, string $tableColumn, int $tableId): void
    {
        if (! in_array($tableName, ['users', 'conversations', 'archive_usages'])) {
            return;
        }

        $user = User::where('id', $userId)->first();

        if (StrHelper::isPureInt($fileIdOrFid)) {
            $file = File::where('id', $fileIdOrFid)->first();
        } else {
            $file = File::where('fid', $fileIdOrFid)->first();
        }

        $fileId = $file->id;

        switch ($tableName) {
            case 'users':
                if ($tableColumn == 'avatar_file_id') {
                    if ($user->avatar_file_id && $user->avatar_file_id != $fileId) {
                        UserLog::create([
                            'user_id' => $user->id,
                            'type' => UserLog::TYPE_AVATAR,
                            'content' => $user->avatar_file_id,
                        ]);
                    }

                    $user->update([
                        'avatar_file_id' => $fileId,
                    ]);
                }

                if ($tableColumn == 'banner_file_id') {
                    if ($user->banner_file_id && $user->banner_file_id != $fileId) {
                        UserLog::create([
                            'user_id' => $user->id,
                            'type' => UserLog::TYPE_BANNER,
                            'content' => $user->banner_file_id,
                        ]);
                    }

                    $user->update([
                        'banner_file_id' => $fileId,
                    ]);
                }

                CacheHelper::forgetFresnsUser($user->id, $user->uid);
                break;

            case 'conversations':
                $conversation = Conversation::where('id', $tableId)->first();

                $receiveUserId = ($user->id == $conversation->a_user_id) ? $conversation->b_user_id : $conversation->a_user_id;

                // conversation message
                $messageInput = [
                    'conversation_id' => $conversation->id,
                    'send_user_id' => $user->id,
                    'message_type' => ConversationMessage::TYPE_FILE,
                    'message_text' => null,
                    'message_file_id' => $fileId,
                    'receive_user_id' => $receiveUserId,
                ];
                ConversationMessage::create($messageInput);

                CacheHelper::forgetFresnsKey("fresns_user_overview_conversations_{$user->uid}", 'fresnsUsers');
                CacheHelper::forgetFresnsKey("fresns_user_overview_conversations_{$receiveUserId}", 'fresnsUsers');
                break;

            case 'archive_usages':
                $archiveUsage = ArchiveUsage::where('id', $tableId)->first();

                $archiveUsage->update([
                    'archive_value' => $fileId,
                ]);

                if ($archiveUsage->usage_type == ArchiveUsage::TYPE_USER) {
                    CacheHelper::forgetFresnsUser($archiveUsage->usage_id);
                }
                break;
        }
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
