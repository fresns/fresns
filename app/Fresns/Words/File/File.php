<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\File;

use App\Fresns\Words\File\DTO\GetAntiLinkFileInfoDTO;
use App\Fresns\Words\File\DTO\GetAntiLinkFileInfoListDTO;
use App\Fresns\Words\File\DTO\GetAntiLinkFileOriginalUrlDTO;
use App\Fresns\Words\File\DTO\GetStorageTokenDTO;
use App\Fresns\Words\File\DTO\LogicalDeletionFilesDTO;
use App\Fresns\Words\File\DTO\PhysicalDeletionFilesDTO;
use App\Fresns\Words\File\DTO\UploadFileDTO;
use App\Fresns\Words\File\DTO\UploadFileInfoDTO;
use App\Helpers\FileHelper;
use App\Utilities\ConfigUtility;
use App\Utilities\FileUtility;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;

class File
{
    use CmdWordResponseTrait;

    public function getStorageToken($wordBody)
    {
        $dtoWordBody = new GetStorageTokenDTO($wordBody);

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($dtoWordBody->type);

        if (! $storageConfig['storageConfigStatus']) {
            return $this->failure(21000, ConfigUtility::getCodeMessage(21000, 'CmdWord'));
        }

        $fresnsResp = \FresnsCmdWord::plugin($storageConfig['service'])->getStorageToken($wordBody);

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
