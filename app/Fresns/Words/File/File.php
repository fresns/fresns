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
use App\Models\Config;
use App\Models\FileAppend;
use App\Models\Plugin;
use Fresns\CmdWordManager\Exceptions\Constants\ExceptionConstant;

class File
{
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
        $unikey = $this->getFileUniKey($dtoWordBody->tableType)['unikey'] ?? '';
        $type = $dtoWordBody->type ?? 0;
        $uploadFile = $dtoWordBody->file;
        $paramsExist = $this->validParamExist($dtoWordBody->type);
        if ($paramsExist == false) {
            return ['message' => 'Unconfigured Plugin', 'code' => 500];
        }
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
        $fileArr['rank_num'] = 9;
        $fileArr['table_type'] = $dtoWordBody->tableType;
        $fileArr['table_name'] = $dtoWordBody->tableName;
        $fileArr['table_column'] = $dtoWordBody->tableColumn;
        $fileArr['table_id'] = isset($dtoWordBody->tableId) ? $this->getTableId($dtoWordBody->tableName, $dtoWordBody->tableId) : null;
        $fileArr['table_key'] = $dtoWordBody->tableKey ?? null;
        $fileArr['fid'] = \Str::random(12);

        $retId = \App\Models\File::create($fileArr)->id;

        $fileArr['real_path'] = $newPath;
        $input = [
            'file_id' => $retId,
            'file_mime' => $uploadFile->getMimeType(),
            'file_size' => $uploadFile->getSize(),
            'platform_id' => $dtoWordBody->platform,
            'transcoding_state' => 1,
            'user_id' => isset($dtoWordBody->user_id) ? PrimaryHelper::fresnsUserIdByUid($dtoWordBody->user_id) : null,
            'account_id' => isset($dtoWordBody->account_id) ? PrimaryHelper::fresnsAccountIdByAid($dtoWordBody->account_id) : null,
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
        $fileArr['file_size'] = $input['file_size'];
        FileAppend::insert($input);

        $fidArr = [$fileArr['fid']];
        $fileIdArr = [$retId];
        if (! empty($unikey)) {
            $input = [];
            $input['fid'] = json_encode($fidArr);
            \FresnsCmdWord::plugin($unikey)->physicalDeletionFile($input);
        }

        $data['files'] = [];

        if ($fileIdArr) {
            $data['files'][] = $this->getFileData($fileIdArr, $type);
        }

        return ['code' => 0, 'message' => 'success', 'data' => $data];
    }

    /**
     * @param $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function uploadFileInfo($wordBody)
    {
        $wordBody = new UploadFileInfoDTO($wordBody);
        $unikey = $this->getFileUniKey($wordBody->tableType)['unikey'] ?? '';
        $type = $wordBody->type ?? 0;
        $fileInfo = $wordBody->fileInfo ?? null;
        $paramsExist = $this->validParamExist($wordBody->type);
//        if ($paramsExist == false) {
//            return ['message' => 'Unconfigured Plugin', 'code' => 500];
//        }

        $fileInfoArr = json_decode($fileInfo, true);
        $fileIdArr = [];
        $fidArr = [];
        if ($fileInfoArr) {
            foreach ($fileInfoArr as $fileInfo) {
                $item = [];
                $item['file_type'] = $type;
                $item['file_name'] = $fileInfo['name'];
                $item['file_extension'] = $fileInfo['extension'];
                $item['file_path'] = $fileInfo['path'];
                $item['rank_num'] = $fileInfo['rankNum'];
                $fid = $fileInfo['fid'] ?? \Str::random(12);
                $item['fid'] = $fid;
                $item['table_type'] = $wordBody->tableType;
                $item['table_name'] = $wordBody->tableName;
                $item['table_column'] = $wordBody->tableColumn;
                $item['table_id'] = $tableId ?? null;
                $item['table_key'] = $tableKey ?? null;
                $fieldId = \App\Models\File::create($item)->id;
                //      FresnsSubPluginService::addSubTablePluginItem(FresnsFilesConfig::CFG_TABLE, $fieldId);
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
                $append['platform_id'] = $wordBody->platform;
                $append['transcoding_state'] = $fileInfo['transcodingState'] ?? 2;
                $append['more_json'] = json_encode($fileInfo['moreJson']);

                FileAppend::insert($append);
            }
        }

        if (! empty($unikey)) {
            $input = [];
            $input['fid'] = json_encode($fidArr);
            //          \FresnsCmdWord::plugin($unikey)->fresns_cmd_upload_file($input);
        }

        $data['files'] = [];

        if ($fileIdArr) {
            $data['files'][] = $this->getFileData($fileIdArr, $type);
        }

        return ['code' => 0, 'message' => 'success', 'data' => $data];
    }

    /**
     * @param $fileIdArr
     * @param $type
     * @return array
     */
    protected function getFileData($fileIdArr, $type)
    {
        $filesArr = \App\Models\File::whereIn('id', $fileIdArr)->get()->toArray();
        foreach ($filesArr as $file) {
            $item = [];
            $fid = $file['fid'];
            $append = FileAppend::where('file_id', $file['id'])->first();
            $item['fid'] = $file['fid'];
            $item['type'] = $file['file_type'];
            $item['name'] = $file['file_name'];
            $item['extension'] = $file['file_extension'];
            $item['mime'] = $append['file_mime'];
            $item['size'] = $append['file_size'];
            $item['rankNum'] = $file['rank_num'];
            if ($type == 1) {
                $item['imageWidth'] = $append['image_width'] ?? '';
                $item['imageHeight'] = $append['image_height'] ?? '';
                $item['imageLong'] = $append['image_is_long'] ?? 0;
                $input['fid'] = $fid;
                $output = FileHelper::fresnsFileInfoByFid($fid);
                $item['imageDefaultUrl'] = $output['imageDefaultUrl'];
                $item['imageConfigUrl'] = $output['imageConfigUrl'];
                $item['imageAvatarUrl'] = $output['imageAvatarUrl'];
                $item['imageRatioUrl'] = $output['imageRatioUrl'];
                $item['imageSquareUrl'] = $output['imageSquareUrl'];
                $item['imageBigUrl'] = $output['imageBigUrl'];
            }
            if ($type == 2) {
                $item['videoTime'] = $append['video_time'] ?? '';
                $input['fid'] = $fid;
                $output = FileHelper::fresnsFileInfoByFid($fid);
                $item['videoCover'] = $output['videoCover'];
                $item['videoGif'] = $output['videoGif'];
                $item['videoUrl'] = $output['videoUrl'];
                $item['transcodingState'] = $append['transcoding_state'];
            }
            if ($type == 3) {
                $item['audioTime'] = $append['audio_time'] ?? '';
                $input['fid'] = $fid;
                $output = FileHelper::fresnsFileInfoByFid($fid);
                $item['audioUrl'] = $output['audioUrl'];
                $item['transcodingState'] = $append['transcoding_state'];
            }
            if ($type == 4) {
                $input['fid'] = $fid;
                $output = FileHelper::fresnsFileInfoByFid($fid);
                $item['docUrl'] = $output['documentUrl'];
            }
            $item['moreJson'] = json_decode($append['more_json'], true);
        }

        return $item;
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
     * @param  int  $fileType
     * @return bool
     */
    public function validParamExist(int $fileType)
    {
        switch ($fileType) {
            case 1:
                $configQuery = ['image_secret_id', 'image_secret_key', 'image_bucket_domain'];
                $validParam = ['image_secret_id', 'image_secret_key', 'image_bucket_domain'];
                break;
            case 2:
                $configQuery = ['video_secret_id', 'video_secret_key', 'video_bucket_domain'];
                $validParam = ['video_secret_id', 'video_secret_key', 'video_bucket_domain'];
                break;
            case 3:
                $configQuery = ['audio_secret_id', 'audio_secret_key', 'audio_bucket_domain'];
                $validParam = ['audio_secret_id', 'audio_secret_key', 'audio_bucket_domain'];
                break;
            case 4:
                $configQuery = ['document_secret_id', 'document_secret_key', 'document_bucket_domain'];
                $validParam = ['document_secret_id', 'document_secret_key', 'document_bucket_domain'];
                break;
        }
        $configMapInDB = Config::whereIn('item_key', $configQuery)->pluck('item_value', 'item_key')->toArray();
        foreach ($validParam as $v) {
            if (! isset($configMapInDB[$v]) || $configMapInDB[$v] == '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  int  $type
     * @return array
     */
    protected function getFileUniKey(int $type)
    {
        $data = [];
        switch ($type) {
            case 1:
                $data['status'] = ConfigHelper::fresnsConfigByItemKey('image_url_status');
                $data['unikey'] = ConfigHelper::fresnsConfigByItemKey('image_service');
                break;
            case 2:
                $data['status'] = ConfigHelper::fresnsConfigByItemKey('video_url_status');
                $data['unikey'] = ConfigHelper::fresnsConfigByItemKey('video_service');
                break;
            case 3:
                $data['status'] = ConfigHelper::fresnsConfigByItemKey('audio_url_status');
                $data['unikey'] = ConfigHelper::fresnsConfigByItemKey('audio_service');
                break;
            case 4:
                $data['status'] = ConfigHelper::fresnsConfigByItemKey('document_url_status');
                $data['unikey'] = ConfigHelper::fresnsConfigByItemKey('document_service');
                break;
        }

        return $data;
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
        if (isset($dtoWordBody->fileId)) {
            $file = \App\Models\File::where('id', $dtoWordBody->fileId)->first();
        } else {
            $file = \App\Models\File::where('fid', $dtoWordBody->fid)->first();
        }
        if (empty($file)) {
            return ['code' => 20009, 'message' => 'file not found', 'data' => []];
        }
        $fileUniKey = $this->getFileUniKey($file->file_type);
        if ($fileUniKey['status'] == false) {
            $fileContent = FileHelper::fresnsFileInfoById($dtoWordBody->fileId);
            $fileContent = array_diff_key($fileContent, ['fid' => 0, 'rankNum' => 0, 'name' => 0, 'extension' => 0, 'mime' => 0, 'size' => 0,
                'moreJson' => 0, 'imageWidth' => 0, 'imageHeight' => 0, 'imageLong' => 0, 'videoTime' => 0, 'audioTime' => 0, 'transcodingState' => 0, 'file_type' => 0, ]);

            return ['code' => 0, 'data' => $fileContent, 'message' => 'success'];
        }

        return \FresnsCmdWord::plugin($fileUniKey['unikey'])->getFileUrlOfAntiLink($wordBody);
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
        if (isset($dtoWordBody->fileId)) {
            $file = \App\Models\File::where('id', $dtoWordBody->fileId)->first();
        } else {
            $file = \App\Models\File::where('fid', $dtoWordBody->fid)->first();
        }
        if (empty($file)) {
            return ['code' => 20009, 'message' => 'file not found', 'data' => []];
        }
        $fileUniKey = $this->getFileUniKey($file->file_type);
        if ($fileUniKey['status'] == false) {
            $fileContent = FileHelper::fresnsFileInfoById($dtoWordBody->fileId);
            $fileContent = array_diff_key($fileContent, ['documentOriginalUrl' => 0, 'audioOriginalUrl' => 0, 'imageOriginalUrl' => 0]);

            return ['code' => 0, 'data' => $fileContent, 'message' => 'success'];
        }

        return \FresnsCmdWord::plugin($fileUniKey['unikey'])->getFileUrlOfAntiLink($wordBody);
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
            $query = ['id' => $wordBody->fileId, 'is_enable' => 1];
        } else {
            $query = ['fid' => $wordBody->fid, 'is_enable' => 1];
        }
        $file = \App\Models\File::where($query)->first();
        if (empty($file)) {
            return ['message' => 'file not found', 'code' => 20009];
        }
        \App\Models\File::where($query)->update(['deleted_at' => date('Y-m-d H:i:s'), 'is_enable' => 0]);

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
        $file = \App\Models\File::where($query)->first();
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
