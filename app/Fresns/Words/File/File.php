<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\File;

use App\Fresns\Words\Config\WordConfig;
use App\Fresns\Words\File\DTO\GetFileInfoOfAntiLinkDTO;
use App\Fresns\Words\File\DTO\GetFileUrlOfAntiLinkDTO;
use App\Fresns\Words\File\DTO\GetUploadTokenDTO;
use App\Fresns\Words\File\DTO\LogicalDeletionFileDTO;
use App\Fresns\Words\File\DTO\PhysicalDeletionFileDTO;
use App\Fresns\Words\File\DTO\UploadFile;
use App\Fresns\Words\File\DTO\UploadFileInfoDTO;
use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\PrimaryHelper;
use App\Models\File as FileModel;
use App\Models\FileAppend;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;
use Fresns\CmdWordManager\Traits\CmdWordResponseTrait;

class File
{
    use CmdWordResponseTrait;

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function getUploadToken($wordBody)
    {
        $dtoWordBody = new GetUploadTokenDTO($wordBody);
        $pluginUniKey = match ($dtoWordBody->type) {
            1 => ConfigHelper::fresnsConfigByItemKey('image_service'),
            2 => ConfigHelper::fresnsConfigByItemKey('video_service'),
            3 => ConfigHelper::fresnsConfigByItemKey('audio_service'),
            default => ConfigHelper::fresnsConfigByItemKey('document_service'),
        };

        if (empty($pluginUniKey)) {
            return ['code' => 20001, 'message' => 'plugin config not found'];
        }

        return \FresnsCmdWord::plugin($pluginUniKey)->getUploadToken($wordBody);
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function uploadFile($wordBody)
    {
        $dtoWordBody = new UploadFile($wordBody);

        $unikey = FileModel::getFileServiceInfoByFileType($dtoWordBody->type)['unikey'] ?? '';
        if (empty($unikey)) {
            return ['message' => 'Unconfigured Plugin', 'code' => 21001];
        }
        FileModel::getFileStorageConfigByFileType($dtoWordBody->type);

        $accountId = PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->aid ?? '');
        $userId = PrimaryHelper::fresnsUserIdByUid($dtoWordBody->uid ?? '');
        if (isset($dtoWordBody->tableId)) {
            $tableId = $this->getTableId($dtoWordBody->tableName, $dtoWordBody->tableId);
        }
        $uploadFile = $dtoWordBody->file;

        $storePath = $this->getFileTempPath($dtoWordBody->type.$dtoWordBody->tableType);
        $path = $uploadFile->store($storePath);
        $basePath = base_path();
        $basePath = $basePath.'/storage/app/';
        $newPath = $storePath.'/'.\Str::random(8).'.'.$uploadFile->getClientOriginalExtension();
        copy($basePath.$path, $basePath.$newPath);
        unlink($basePath.$path);

        $fileArr['file_type'] = $dtoWordBody->type;
        $fileArr['file_name'] = $uploadFile->getClientOriginalName();
        $fileArr['file_extension'] = $uploadFile->getClientOriginalExtension();
        $fileArr['file_path'] = str_replace('public', '', $newPath);
        $fileArr['table_type'] = $dtoWordBody->tableType;
        $fileArr['table_name'] = $dtoWordBody->tableName;
        $fileArr['table_column'] = $dtoWordBody->tableColumn;
        $fileArr['table_id'] = isset($tableId) ?? null;
        $fileArr['table_key'] = $dtoWordBody->tableKey ?? null;
        $fileArr['fid'] = \Str::random(12);
        $fid = $fileArr['fid'];

        $retId = FileModel::create($fileArr)->id;

        $input = [
            'file_id' => $retId,
            'file_mime' => $uploadFile->getMimeType(),
            'file_size' => $uploadFile->getSize(),
            'platform_id' => $dtoWordBody->platform,
            'account_id' => isset($accountId) ?? null,
            'user_id' => isset($userId) ?? null,
            'image_is_long' => 0,
        ];
        if ($dtoWordBody->type == 1) {
            $imageSize = getimagesize($uploadFile);
            $input['image_width'] = $imageSize[0] ?? null;
            $input['image_height'] = $imageSize[1] ?? null;
            if (! empty($input['image_width']) >= 700) {
                if ($input['image_height'] >= $input['image_width'] * 3) {
                    $input['image_is_long'] = 1;
                }
            }
        }
        FileAppend::insert($input);

        $fresnsResp = \FresnsCmdWord::plugin($unikey)->uploadFile([
            'fid' => $fid,
        ]);

        return $fresnsResp->getOrigin();
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function uploadFileInfo($wordBody)
    {
        $dtoWordBody = new UploadFileInfoDTO($wordBody);

        $unikey = FileModel::getFileServiceInfoByFileType($dtoWordBody->type)['unikey'] ?? '';
        if (empty($unikey)) {
            return ['message' => 'Unconfigured Plugin', 'code' => 21001];
        }

        FileModel::getFileStorageConfigByFileType($dtoWordBody->type);

        $tableId = $this->getTableId($dtoWordBody->tableName, $dtoWordBody->tableId);
        $fileInfoArr = json_decode($dtoWordBody->fileInfo, true);

        $fileIdArr = [];
        $fidArr = [];

        if ($fileInfoArr) {
            foreach ($fileInfoArr as $fileInfo) {
                $item = [];
                $item['fid'] = \Str::random(12);
                $item['file_type'] = $dtoWordBody->type;
                $item['file_name'] = $fileInfo['name'];
                $item['file_extension'] = $fileInfo['extension'];
                $item['file_path'] = $fileInfo['path'];
                $item['rank_num'] = $fileInfo['rankNum'];
                $item['table_type'] = $dtoWordBody->tableType;
                $item['table_name'] = $dtoWordBody->tableName;
                $item['table_column'] = $dtoWordBody->tableColumn;
                $item['table_id'] = $tableId ?? null;
                $item['table_key'] = $dtoWordBody->tableKey ?? null;
                $fieldId = FileModel::create($item)->id;
                $fileIdArr[] = $fieldId;
                $fidArr[] = $item['fid'];

                $append = [];
                $append['file_id'] = $fieldId;
                $append['file_original_path'] = $fileInfo['originalPath'] == '' ? null : $fileInfo['originalPath'];
                $append['file_mime'] = $fileInfo['mime'] == '' ? null : $fileInfo['mime'];
                $append['file_size'] = $fileInfo['size'] == '' ? null : $fileInfo['size'];
                $append['file_md5'] = $fileInfo['md5'] == '' ? null : $fileInfo['md5'];
                $append['file_sha1'] = $fileInfo['sha1'] == '' ? null : $fileInfo['sha1'];
                $append['image_width'] = $fileInfo['imageWidth'] == '' ? null : $fileInfo['imageWidth'];
                $append['image_height'] = $fileInfo['imageHeight'] == '' ? null : $fileInfo['imageHeight'];
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
                $append['image_is_long'] = $imageLong;
                $append['video_time'] = $fileInfo['videoTime'] == '' ? null : $fileInfo['videoTime'];
                $append['video_cover'] = $fileInfo['videoCover'] == '' ? null : $fileInfo['videoCover'];
                $append['video_gif'] = $fileInfo['videoGif'] == '' ? null : $fileInfo['videoGif'];
                $append['audio_time'] = $fileInfo['audioTime'] == '' ? null : $fileInfo['audioTime'];
                $append['platform_id'] = $dtoWordBody->platform;
                $append['more_json'] = json_encode($fileInfo['moreJson']);

                FileAppend::insert($append);
            }
        }

        $fresnsResp = \FresnsCmdWord::plugin($unikey)->uploadFileInfo([
            'fids' => $fidArr,
        ]);

        return $fresnsResp->getOrigin();
    }

    /**
     * @param $options
     * @return string
     */
    public function getFileTempPath($options)
    {
        $basePath = base_path().'/storage/app/public/';
        $fileTempPath = WordConfig::FILE_TEMP_PATH[$options] ?? '';
        if (empty($fileTempPath)) {
            $fileTempPath = '/temp_files/unknown/{ym}/{day}';
        }
        $fileTempPath = str_replace(['{ym}', '{day}'], [date('Ym', time()), date('d', time())], $fileTempPath);
        $realPath = $basePath.$fileTempPath;
        if (! is_dir($realPath)) {
            \Illuminate\Support\Facades\File::makeDirectory($realPath, 0755, true, true);
        }

        return 'public'.$fileTempPath;
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function getFileUrlOfAntiLink($wordBody)
    {
        $dtoWordBody = new GetFileUrlOfAntiLinkDTO($wordBody);

        $file = FileModel::idOrFid([
            'id' => $dtoWordBody->fileId,
            'fid' => $dtoWordBody->fid,
        ])->first();

        if (empty($file)) {
            return $this->success([], 'file not found', 21010);
        }

        $fileUniKey = $file->getFileServiceInfo();

        if ($fileUniKey['url_anti_status']) {
            return \FresnsCmdWord::plugin($fileUniKey['unikey'])->getFileUrlOfAntiLink($wordBody);
        }

        return $this->success(
            FileHelper::fresnsFileUrlById($file->id)
        );
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function getFileInfoOfAntiLink($wordBody)
    {
        $dtoWordBody = new GetFileInfoOfAntiLinkDTO($wordBody);

        $file = FileModel::idOrFid([
            'id' => $dtoWordBody->fileId,
            'fid' => $dtoWordBody->fid,
        ])->first();

        if (empty($file)) {
            return $this->success([], 'file not found', 21010);
        }

        $fileUniKey = $file->getFileServiceInfo();

        if ($fileUniKey['url_anti_status']) {
            return \FresnsCmdWord::plugin($fileUniKey['unikey'])->getFileInfoOfAntiLink($wordBody);
        }

        return $this->success(
            FileHelper::fresnsFileInfoById($file->id)
        );
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function logicalDeletionFile($wordBody)
    {
        $wordBody = new LogicalDeletionFileDTO($wordBody);
        if (isset($wordBody->fileId)) {
            $query = ['id' => $wordBody->fileId];
        } else {
            $query = ['fid' => $wordBody->fid];
        }
        $file = FileModel::where($query)->first();
        if (empty($file)) {
            return ['message' => 'file not found', 'code' => 20009];
        }
        FileModel::where($query)->delete();

        return ['message' => 'success', 'code' => 0];
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function physicalDeletionFile($wordBody)
    {
        $dtoWordBody = new PhysicalDeletionFileDTO($wordBody);
        if (isset($dtoWordBody->fileId)) {
            $query = ['id' => $dtoWordBody->fileId];
        } else {
            $query = ['fid' => $dtoWordBody->fid];
        }
        $file = FileModel::where($query)->first();
        if (empty($file)) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::CMD_WORD_DATA_ERROR)::throw();
        }

        $pluginUniKey = match ($file['file_type']) {
            1 => ConfigHelper::fresnsConfigByItemKey('image_service'),
            2 => ConfigHelper::fresnsConfigByItemKey('video_service'),
            3 => ConfigHelper::fresnsConfigByItemKey('audio_service'),
            default => ConfigHelper::fresnsConfigByItemKey('document_service'),
        };
        if (empty($pluginUniKey)) {
            ExceptionConstant::getHandleClassByCode(ExceptionConstant::PLUGIN_CONFIG_ERROR)::throw();
        }

        return \FresnsCmdWord::plugin($pluginUniKey)->physicalDeletionFile($wordBody);
    }

    /**
     * @param $tableName
     * @param $tableId
     * @return mixed
     */
    protected function getTableId($tableName, $tableId)
    {
        $tableId = match ($tableName) {
            'accounts'=>PrimaryHelper::fresnsAccountIdByAid($tableId),
            'users'=>PrimaryHelper::fresnsUserIdByUid($tableId),
            'posts'=>PrimaryHelper::fresnsPostIdByPid($tableId),
            'comments'=>PrimaryHelper::fresnsCommentIdByCid($tableId),
            'extends'=>PrimaryHelper::fresnsExtendIdByEid($tableId),
            'groups'=>PrimaryHelper::fresnsGroupIdByGid($tableId),
            'hashtags'=>PrimaryHelper::fresnsHashtagIdByHuri($tableId),
        };

        return $tableId;
    }
}
