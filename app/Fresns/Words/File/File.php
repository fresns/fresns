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
use App\Fresns\Words\File\DTO\UploadFileInfoDTO;
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

        // check model
        switch ($tableName) {
            case 'users':
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

            $conversation = PrimaryHelper::fresnsModelConversation($authUser->id, $checkQuery->id);
            $tableId = $conversation->id;
        }

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

        // check publish file count
        $publishType = match ($usageType) {
            FileUsage::TYPE_POST => 'post',
            FileUsage::TYPE_COMMENT => 'comment',
            default => null,
        };

        if ($publishType) {
            $editorConfig = ConfigUtility::getEditorConfigByType($publishType, $authUser->id);

            $uploadStatus = $editorConfig[$fileTypeString]['status'] ?? false;
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

            $uploadNumber = $editorConfig[$fileTypeString]['uploadNumber'] ?? 0;

            $fileCount = FileUsage::where('file_type', $fileTypeInt)
                ->where('usage_type', $usageType)
                ->where('table_name', $tableName)
                ->where('table_column', $tableColumn)
                ->where('table_id', $tableId)
                ->count();

            if ($fileCount >= $uploadNumber) {
                return $this->failure(36115, ConfigUtility::getCodeMessage(36115));
            }
        }

        $data = [
            'usageType' => $usageType,
            'tableName' => $tableName,
            'tableColumn' => $tableColumn,
            'tableId' => $tableId,
            'tableKey' => $fsid,
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

        // $bodyInfo = [
        //     'platformId' => $dtoWordBody->platformId,
        //     'usageType' => $dtoWordBody->usageType,
        //     'tableName' => $dtoWordBody->tableName,
        //     'tableColumn' => $dtoWordBody->tableColumn,
        //     'tableId' => $dtoWordBody->tableId,
        //     'tableKey' => $dtoWordBody->tableKey,
        //     'aid' => $dtoWordBody->aid,
        //     'uid' => $dtoWordBody->uid,
        //     'type' => $dtoWordBody->type,
        //     'warningType' => $dtoWordBody->warningType,
        //     'moreInfo' => $dtoWordBody->moreInfo,
        // ];
        // $uploadFile = FileUtility::uploadFile($bodyInfo, $dtoWordBody->file);

        // return $this->success($uploadFile);

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($dtoWordBody->type);

        if (! $storageConfig['storageConfigStatus']) {
            return $this->failure(21000, ConfigUtility::getCodeMessage(21000, 'CmdWord'));
        }

        $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->uploadFile($wordBody);

        return $fresnsResp->getOrigin();
    }

    public function uploadFileInfo($wordBody)
    {
        $dtoWordBody = new UploadFileInfoDTO($wordBody);

        $bodyInfo = [
            'platformId' => $dtoWordBody->platformId,
            'usageType' => $dtoWordBody->usageType,
            'tableName' => $dtoWordBody->tableName,
            'tableColumn' => $dtoWordBody->tableColumn,
            'tableId' => $dtoWordBody->tableId,
            'tableKey' => $dtoWordBody->tableKey,
            'aid' => $dtoWordBody->aid,
            'uid' => $dtoWordBody->uid,
            'type' => $dtoWordBody->type,
            'fileInfo' => $dtoWordBody->fileInfo,
            'warningType' => $dtoWordBody->warningType,
            'moreInfo' => $dtoWordBody->moreInfo,
        ];
        $uploadFileInfo = FileUtility::uploadFileInfo($bodyInfo);

        return $this->success($uploadFileInfo);
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
