<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\File;

use App\Fresns\Words\File\DTO\CheckUploadPermDTO;
use App\Fresns\Words\File\DTO\GetAntiLinkFileInfoDTO;
use App\Fresns\Words\File\DTO\GetAntiLinkFileInfoListDTO;
use App\Fresns\Words\File\DTO\GetAntiLinkFileOriginalUrlDTO;
use App\Fresns\Words\File\DTO\GetUploadTokenDTO;
use App\Fresns\Words\File\DTO\LogicalDeletionFilesDTO;
use App\Fresns\Words\File\DTO\PhysicalDeletionFilesDTO;
use App\Fresns\Words\File\DTO\UploadFileDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Helpers\StrHelper;
use App\Models\Comment;
use App\Models\CommentLog;
use App\Models\File as FileModel;
use App\Models\FileUsage;
use App\Models\Post;
use App\Models\PostLog;
use App\Models\User;
use App\Utilities\ConfigUtility;
use App\Utilities\FileUtility;
use App\Utilities\PermissionUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;
use Illuminate\Support\Str;

class File
{
    use CmdWordResponseTrait;

    public function checkUploadPerm($wordBody)
    {
        $dtoWordBody = new CheckUploadPermDTO($wordBody);

        // table name
        $tableName = match ($dtoWordBody->usageType) {
            'userAvatar' => 'users',
            'userBanner' => 'users',
            'conversation' => 'conversations',
            'post' => 'posts',
            'comment' => 'comments',
            'postDraft' => 'post_logs',
            'commentDraft' => 'comment_logs',
        };

        // table column
        $tableColumn = match ($dtoWordBody->usageType) {
            'userAvatar' => 'avatar_file_id',
            'userBanner' => 'banner_file_id',
            'conversation' => 'id',
            'post' => 'id',
            'comment' => 'id',
            'postDraft' => 'id',
            'commentDraft' => 'id',
        };

        // usage type
        $usageType = match ($tableName) {
            'users' => FileUsage::TYPE_USER,
            'posts' => FileUsage::TYPE_POST,
            'comments' => FileUsage::TYPE_COMMENT,
            'conversations' => FileUsage::TYPE_CONVERSATION,
            'post_logs' => FileUsage::TYPE_POST,
            'comment_logs' => FileUsage::TYPE_COMMENT,
            default => FileUsage::TYPE_OTHER,
        };

        // publish type
        $publishType = match ($usageType) {
            FileUsage::TYPE_POST => 'post',
            FileUsage::TYPE_COMMENT => 'comment',
            default => null,
        };

        // fsid
        $fsid = $dtoWordBody->usageFsid;

        // type
        $fileTypeInt = $dtoWordBody->type;
        $fileTypeString = match ($dtoWordBody->type) {
            FileModel::TYPE_IMAGE => 'image',
            FileModel::TYPE_VIDEO => 'video',
            FileModel::TYPE_AUDIO => 'audio',
            FileModel::TYPE_DOCUMENT => 'document',
        };

        // auth user
        $authUser = PrimaryHelper::fresnsModelByFsid('user', $dtoWordBody->uid);

        // check extension
        if ($dtoWordBody->extension) {
            $extensionNames = ConfigHelper::fresnsConfigByItemKey("{$fileTypeString}_extension_names");
            $extensionArr = explode(',', $extensionNames);

            $extension = Str::of($dtoWordBody->extension)->ltrim('.');

            if (! in_array($extension, $extensionArr)) {
                return $this->failure(36310, ConfigUtility::getCodeMessage(36310), [
                    'currentFileExtension' => $extension,
                ]);
            }
        }

        // user role config
        $maxMb = ConfigHelper::fresnsConfigByItemKey("{$fileTypeString}_max_size") + 1;
        $maxDuration = ConfigHelper::fresnsConfigByItemKey("{$fileTypeString}_max_duration") + 1;
        if ($publishType) {
            $editorConfig = ConfigUtility::getEditorConfigByType($publishType, $authUser->id);

            $uploadStatus = $editorConfig[$fileTypeString]['status'];
            if (! $uploadStatus) {
                $errorCode = match ($fileTypeInt) {
                    FileModel::TYPE_IMAGE => 36109,
                    FileModel::TYPE_VIDEO => 36110,
                    FileModel::TYPE_AUDIO => 36111,
                    FileModel::TYPE_DOCUMENT => 36112,
                    default => 36200,
                };

                return $this->failure($errorCode, ConfigUtility::getCodeMessage($errorCode));
            }

            $maxMb = $editorConfig[$fileTypeString]['maxSize'];
            $maxDuration = $editorConfig[$fileTypeString]['maxDuration'];
        }

        // check size
        if ($dtoWordBody->size) {
            $maxBytes = $maxMb * 1024 * 1024;

            if ($dtoWordBody->size > $maxBytes) {
                return $this->failure(36113, ConfigUtility::getCodeMessage(36113));
            }
        }

        // check duration
        if ($dtoWordBody->duration && in_array($fileTypeString, ['video', 'audio'])) {
            if ($dtoWordBody->duration > $maxDuration) {
                return $this->failure(36114, ConfigUtility::getCodeMessage(36114));
            }
        }

        // check model
        switch ($tableName) {
            case 'users':
                // check type
                if ($fileTypeString != 'image') {
                    return $this->failure(36310, ConfigUtility::getCodeMessage(36310));
                }

                // query
                if (StrHelper::isPureInt($fsid)) {
                    $checkQuery = User::where('uid', $fsid)->first();
                } else {
                    $checkQuery = User::where('username', $fsid)->first();
                }

                $checkUser = $checkQuery?->id == $authUser->id;
                break;

            case 'posts':
                $checkQuery = Post::where('pid', $fsid)->first();

                $checkUser = $checkQuery?->user_id == $authUser->id;
                break;

            case 'comments':
                $checkQuery = Comment::where('cid', $fsid)->first();

                $checkUser = $checkQuery?->user_id == $authUser->id;
                break;

            case 'conversations':
                // check type
                $conversationFiles = ConfigHelper::fresnsConfigByItemKey('conversation_files');
                if (! in_array($fileTypeString, $conversationFiles)) {
                    $errorCode = match ($fileTypeInt) {
                        FileModel::TYPE_IMAGE => 36109,
                        FileModel::TYPE_VIDEO => 36110,
                        FileModel::TYPE_AUDIO => 36111,
                        FileModel::TYPE_DOCUMENT => 36112,
                        default => 36200,
                    };

                    return $this->failure($errorCode, ConfigUtility::getCodeMessage($errorCode));
                }

                // query
                if (StrHelper::isPureInt($fsid)) {
                    $checkQuery = User::where('uid', $fsid)->first();
                } else {
                    $checkQuery = User::where('username', $fsid)->first();
                }

                $checkUser = true;
                break;

            case 'post_logs':
                $checkQuery = PostLog::where('hpid', $fsid)->first();

                $checkUser = $checkQuery?->user_id == $authUser->id;
                break;

            case 'comment_logs':
                $checkQuery = CommentLog::where('hcid', $fsid)->first();

                $checkUser = $checkQuery?->user_id == $authUser->id;
                break;

            default:
                $checkQuery = null;
                $checkUser = false;
        }

        if (empty($checkQuery)) {
            return $this->failure(32201, ConfigUtility::getCodeMessage(32201));
        }

        if (! $checkUser) {
            return $this->failure(36500, ConfigUtility::getCodeMessage(36500));
        }

        // table id
        $tableId = $checkQuery->id;

        // conversation message
        if ($tableName == 'conversations') {
            $conversationPermInt = PermissionUtility::checkUserConversationPerm($checkQuery->id, $authUser->id);
            if ($conversationPermInt != 0) {
                return $this->failure($conversationPermInt, ConfigUtility::getCodeMessage($conversationPermInt));
            }

            $conversation = PrimaryHelper::fresnsModelConversation($authUser->id, $checkQuery->id);
            $tableId = $conversation->id;
        }

        $uploadNumberConfig = 1;
        if ($publishType) {
            $maxUploadNumber = $editorConfig[$fileTypeString]['maxUploadNumber'] ?? 0;

            $fileUsageQuery = FileUsage::where('file_type', $fileTypeInt)->where('usage_type', $usageType)->where('table_name', $tableName)->where('table_column', $tableColumn)->where('table_id', $tableId);

            $fileUsageQuery->whereRelation('file', 'is_uploaded', true);

            $fileCount = $fileUsageQuery->count();

            $uploadNumberConfig = $maxUploadNumber - $fileCount;

            if ($uploadNumberConfig <= 0) {
                return $this->failure(36115, ConfigUtility::getCodeMessage(36115));
            }
        }

        $data = [
            'usageType' => $usageType,
            'tableName' => $tableName,
            'tableColumn' => $tableColumn,
            'tableId' => $tableId,
            'tableKey' => $fsid,
            'maxUploadNumber' => $uploadNumberConfig,
        ];

        return $this->success($data);
    }

    public function getUploadToken($wordBody)
    {
        $dtoWordBody = new GetUploadTokenDTO($wordBody);

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($dtoWordBody->type);

        if (! $storageConfig['storageConfigStatus']) {
            return $this->failure(21000, ConfigUtility::getCodeMessage(21000, 'CmdWord'));
        }

        $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->getUploadToken($wordBody);

        return $fresnsResp->getOrigin();
    }

    public function uploadFile($wordBody)
    {
        $dtoWordBody = new UploadFileDTO($wordBody);

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($dtoWordBody->type);

        if (! $storageConfig['storageConfigStatus']) {
            return $this->failure(21000, ConfigUtility::getCodeMessage(21000, 'CmdWord'));
        }

        $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->uploadFile($wordBody);

        return $fresnsResp->getOrigin();
    }

    public function getAntiLinkFileInfo($wordBody)
    {
        $dtoWordBody = new GetAntiLinkFileInfoDTO($wordBody);

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($dtoWordBody->type);

        if (! $storageConfig['storageConfigStatus']) {
            return $this->failure(21000, ConfigUtility::getCodeMessage(21000, 'CmdWord'));
        }

        if ($storageConfig['antiLinkStatus']) {
            $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->getAntiLinkFileInfo($wordBody);

            return $fresnsResp->getOrigin();
        }

        return $this->success(FileHelper::fresnsFileInfoById($dtoWordBody->fileIdOrFid));
    }

    public function getAntiLinkFileInfoList($wordBody)
    {
        $dtoWordBody = new GetAntiLinkFileInfoListDTO($wordBody);

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($dtoWordBody->type);

        if (! $storageConfig['storageConfigStatus']) {
            return $this->failure(21000, ConfigUtility::getCodeMessage(21000, 'CmdWord'));
        }

        if ($storageConfig['antiLinkStatus']) {
            $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->getAntiLinkFileInfoList($wordBody);

            return $fresnsResp->getOrigin();
        }

        return $this->success(FileHelper::fresnsFileInfoListByIds($dtoWordBody->fileIdsOrFids));
    }

    public function getAntiLinkFileOriginalUrl($wordBody)
    {
        $dtoWordBody = new GetAntiLinkFileOriginalUrlDTO($wordBody);

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($dtoWordBody->type);

        if (! $storageConfig['storageConfigStatus']) {
            return $this->failure(21000, ConfigUtility::getCodeMessage(21000, 'CmdWord'));
        }

        if ($storageConfig['antiLinkStatus']) {
            $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->getAntiLinkFileOriginalUrl($wordBody);

            return $fresnsResp->getOrigin();
        }

        return $this->success([
            'originalUrl' => FileHelper::fresnsFileOriginalUrlById($dtoWordBody->fileIdOrFid),
        ]);
    }

    public function logicalDeletionFiles($wordBody)
    {
        $dtoWordBody = new LogicalDeletionFilesDTO($wordBody);

        FileUtility::logicalDeletionFiles($dtoWordBody->fileIdsOrFids);

        return $this->success();
    }

    public function physicalDeletionFiles($wordBody)
    {
        $dtoWordBody = new PhysicalDeletionFilesDTO($wordBody);

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($dtoWordBody->type);

        if (! $storageConfig['storageConfigStatus']) {
            return $this->failure(21000, ConfigUtility::getCodeMessage(21000, 'CmdWord'));
        }

        $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->physicalDeletionFiles($wordBody);

        return $fresnsResp->getOrigin();
    }
}
