<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\File;
use App\Models\FileUsage;
use Illuminate\Support\Str;

class FileHelper
{
    // get file storage config by type
    public static function fresnsFileStorageConfigByType(int $type): array
    {
        $key = match ($type) {
            1 => 'image',
            2 => 'video',
            3 => 'audio',
            4 => 'document',
        };

        $data = ConfigHelper::fresnsConfigByItemKeys([
            "{$key}_service",
            "{$key}_secret_id",
            "{$key}_secret_key",
            "{$key}_bucket_name",
            "{$key}_bucket_region",
            "{$key}_bucket_domain",
            "{$key}_url_status",
            "{$key}_url_key",
            "{$key}_url_expire",
        ]);

        $config = [
            'service' => $data["{$key}_service"],
            'secretId' => $data["{$key}_secret_id"],
            'secretKey' => $data["{$key}_secret_key"],
            'bucketName' => $data["{$key}_bucket_name"],
            'bucketRegion' => $data["{$key}_bucket_region"],
            'bucketDomain' => $data["{$key}_bucket_domain"],
            'antiLinkStatus' => $data["{$key}_url_status"],
            'antiLinkKey' => $data["{$key}_url_key"],
            'antiLinkExpire' => $data["{$key}_url_expire"],
        ];

        $config['storageConfigStatus'] = true;
        if (empty($config['secretId']) || empty($config['secretKey']) || empty($config['bucketName']) || empty($config['bucketDomain'])) {
            $config['storageConfigStatus'] = false;
        }

        return $config;
    }

    // get file accept by type
    public static function fresnsFileAcceptByType(?int $type = null): string|array
    {
        $cacheKey = 'fresns_config_file_accept';
        $cacheTag = 'fresnsConfigs';
        $fileAccept = CacheHelper::get($cacheKey, $cacheTag);

        if (empty($fileAccept['images']) && empty($fileAccept['videos']) && empty($fileAccept['audios']) && empty($fileAccept['documents'])) {
            $imageFileExt = ConfigHelper::fresnsConfigByItemKey('image_extension_names');
            $videoFileExt = ConfigHelper::fresnsConfigByItemKey('video_extension_names');
            $audioFileExt = ConfigHelper::fresnsConfigByItemKey('audio_extension_names');
            $documentFileExt = ConfigHelper::fresnsConfigByItemKey('document_extension_names');

            $imageFileExt = Str::lower($imageFileExt);
            $videoFileExt = Str::lower($videoFileExt);
            $audioFileExt = Str::lower($audioFileExt);
            $documentFileExt = Str::lower($documentFileExt);

            // $builder = \Mimey\MimeMappingBuilder::create();
            // $mapping = $builder->getMapping();
            // $mapping['mimes'];
            // $mapping['extensions'];
            // foreach ($mapping['mimes'] as $ext => $mimes) {
            // }
            // foreach ($mapping['extensions'] as $mime => $exts) {
            // }

            $mimes = new \Mimey\MimeTypes;

            $imageFileExtArr = explode(',', $imageFileExt);
            $videoFileExtArr = explode(',', $videoFileExt);
            $audioFileExtArr = explode(',', $audioFileExt);
            $documentFileExtArr = explode(',', $documentFileExt);

            $imageFileMimeAccept = [];
            $imageFileExtAccept = [];
            foreach ($imageFileExtArr as $imageExt) {
                $fileExtMimes = $mimes->getAllMimeTypes($imageExt);
                foreach ($fileExtMimes as $fileExtMime) {
                    $imageFileMimeAccept[] = $fileExtMime;
                }

                $lowerFileExt = Str::lower($imageExt);
                $imageFileExtAccept[] = '.'.$lowerFileExt;
            }
            $videoFileMimeAccept = [];
            $videoFileExtAccept = [];
            foreach ($videoFileExtArr as $videoExt) {
                $fileExtMimes = $mimes->getAllMimeTypes($videoExt);
                foreach ($fileExtMimes as $fileExtMime) {
                    $videoFileMimeAccept[] = $fileExtMime;
                }

                $lowerFileExt = Str::lower($imageExt);
                $videoFileExtAccept[] = '.'.$lowerFileExt;
            }
            $audioFileMimeAccept = [];
            $audioFileExtAccept = [];
            foreach ($audioFileExtArr as $audioExt) {
                $fileExtMimes = $mimes->getAllMimeTypes($audioExt);
                foreach ($fileExtMimes as $fileExtMime) {
                    $audioFileMimeAccept[] = $fileExtMime;
                }

                $lowerFileExt = Str::lower($imageExt);
                $audioFileExtAccept[] = '.'.$lowerFileExt;
            }
            $documentFileMimeAccept = [];
            $documentFileExtAccept = [];
            foreach ($documentFileExtArr as $documentExt) {
                $fileExtMimes = $mimes->getAllMimeTypes($documentExt);
                foreach ($fileExtMimes as $fileExtMime) {
                    $documentFileMimeAccept[] = $fileExtMime;
                }

                $lowerFileExt = Str::lower($imageExt);
                $documentFileExtAccept[] = '.'.$lowerFileExt;
            }

            $imageFileAccept = array_merge($imageFileMimeAccept, $imageFileExtAccept);
            $videoFileAccept = array_merge($videoFileMimeAccept, $videoFileExtAccept);
            $audioFileAccept = array_merge($audioFileMimeAccept, $audioFileExtAccept);
            $documentFileAccept = array_merge($documentFileMimeAccept, $documentFileExtAccept);

            $fileAccept = [
                'images' => implode(',', $imageFileAccept),
                'videos' => implode(',', $videoFileAccept),
                'audios' => implode(',', $audioFileAccept),
                'documents' => implode(',', $documentFileAccept),
            ];

            CacheHelper::put($fileAccept, $cacheKey, $cacheTag);
        }

        if (empty($type)) {
            return $fileAccept;
        }

        return match ($type) {
            File::TYPE_IMAGE => $fileAccept['images'],
            File::TYPE_VIDEO => $fileAccept['videos'],
            File::TYPE_AUDIO => $fileAccept['audios'],
            File::TYPE_DOCUMENT => $fileAccept['documents'],
        };
    }

    // get file storage path
    public static function fresnsFileStoragePath(int $fileType, int $usageType): string
    {
        $fileTypeDir = match ($fileType) {
            File::TYPE_IMAGE => 'images',
            File::TYPE_VIDEO => 'videos',
            File::TYPE_AUDIO => 'audios',
            File::TYPE_DOCUMENT => 'documents',
        };

        $usageTypeDir = match ($usageType) {
            FileUsage::TYPE_OTHER => '/others/{YYYYMM}/',
            FileUsage::TYPE_SYSTEM => '/systems/{YYYYMM}/',
            FileUsage::TYPE_OPERATION => '/operations/{YYYYMM}/',
            FileUsage::TYPE_STICKER => '/stickers/{YYYYMM}/',
            FileUsage::TYPE_USER => '/users/{YYYYMM}/{DD}/',
            FileUsage::TYPE_CONVERSATION => '/conversations/{YYYYMM}/{DD}/',
            FileUsage::TYPE_POST => '/posts/{YYYYMM}/{DD}/',
            FileUsage::TYPE_COMMENT => '/comments/{YYYYMM}/{DD}/',
            FileUsage::TYPE_EXTEND => '/extends/{YYYYMM}/{DD}/',
            FileUsage::TYPE_PLUGIN => '/plugins/{YYYYMM}/{DD}/',
        };

        $replaceUseTypeDir = str_replace(
            ['{YYYYMM}', '{DD}'],
            [date('Ym'), date('d')],
            $usageTypeDir
        );

        return sprintf('%s/%s', trim($fileTypeDir, '/'), trim($replaceUseTypeDir, '/'));
    }

    // get file info by file id or fid
    public static function fresnsFileInfoById(int|string $fileIdOrFid): ?array
    {
        if (StrHelper::isPureInt($fileIdOrFid)) {
            $file = File::whereId($fileIdOrFid)->first();
        } else {
            $file = File::whereFid($fileIdOrFid)->first();
        }

        if (empty($file)) {
            return null;
        }

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($file->type);

        if ($storageConfig['antiLinkStatus']) {
            $fresnsResponse = \FresnsCmdWord::plugin($storageConfig['service'])->getAntiLinkFileInfo([
                'type' => $file->type,
                'fileIdOrFid' => strval($file->id),
            ]);

            return $fresnsResponse->getData() ?? null;
        }

        return $file->getFileInfo();
    }

    // get file info list by file id or fid
    public static function fresnsFileInfoListByIds(array $fileIdsOrFids): array
    {
        $files = File::whereIn('id', $fileIdsOrFids)
            ->orWhereIn('fid', $fileIdsOrFids)
            ->get()
            ->groupBy('type');

        $data['images'] = $files->get(File::TYPE_IMAGE)?->all() ?? [];
        $data['videos'] = $files->get(File::TYPE_VIDEO)?->all() ?? [];
        $data['audios'] = $files->get(File::TYPE_AUDIO)?->all() ?? [];
        $data['documents'] = $files->get(File::TYPE_DOCUMENT)?->all() ?? [];

        $fileList = FileHelper::handleAntiLinkFileInfoList($data);

        return $fileList;
    }

    // get file info list by table column
    public static function fresnsFileInfoListByTableColumn(string $tableName, string $tableColumn, ?int $tableId = null, ?string $tableKey = null): array
    {
        $fileUsageQuery = FileUsage::with('file')
            ->where('table_name', $tableName)
            ->where('table_column', $tableColumn)
            ->orderBy('rating');

        if (empty($tableId)) {
            $fileUsageQuery->where('table_key', $tableKey);
        } else {
            $fileUsageQuery->where('table_id', $tableId);
        }

        $fileUsages = $fileUsageQuery->get();

        $fileData = $fileUsages->map(fn ($fileUsage) => $fileUsage->file)->groupBy('type');

        $data['images'] = $fileData->get(File::TYPE_IMAGE)?->all() ?? [];
        $data['videos'] = $fileData->get(File::TYPE_VIDEO)?->all() ?? [];
        $data['audios'] = $fileData->get(File::TYPE_AUDIO)?->all() ?? [];
        $data['documents'] = $fileData->get(File::TYPE_DOCUMENT)?->all() ?? [];

        $fileList = FileHelper::handleAntiLinkFileInfoList($data);

        return $fileList;
    }

    // get file url by table column
    public static function fresnsFileUrlByTableColumn(?int $idColumn = null, ?string $urlColumn = null, ?string $urlType = null): ?string
    {
        if (! $idColumn && ! $urlColumn) {
            return null;
        }

        if (! $idColumn) {
            if (substr($urlColumn, 0, 1) === '/') {
                return StrHelper::qualifyUrl($urlColumn);
            }

            return $urlColumn;
        }

        $file = File::where('id', $idColumn)->first();

        if (empty($file)) {
            return null;
        }

        $urlType = $urlType ?: 'imageConfigUrl';

        $antiLinkStatus = FileHelper::fresnsFileStorageConfigByType($file->type)['antiLinkStatus'];

        if ($antiLinkStatus) {
            $fresnsResponse = \FresnsCmdWord::plugin()->getAntiLinkFileInfo([
                'type' => $file->type,
                'fileIdOrFid' => strval($file->id),
            ]);

            return $fresnsResponse->getData($urlType) ?? null;
        }

        return $file->getFileInfo()[$urlType] ?? null;
    }

    // get file url by file id or fid
    public static function fresnsFileUrlById(int|string $fileIdOrFid, ?string $urlConfig = null): ?string
    {
        $fileInfo = FileHelper::fresnsFileInfoById($fileIdOrFid);

        $key = match ($fileInfo['type']) {
            File::TYPE_IMAGE => 'imageConfig',
            File::TYPE_VIDEO => 'video',
            File::TYPE_AUDIO => 'audio',
            File::TYPE_DOCUMENT => 'documentPreview',
            default => 'imageConfig',
        };

        $urlConfig = $urlConfig ?: "{$key}Url";

        return $fileInfo[$urlConfig] ?? null;
    }

    // get file original url by file id or fid
    public static function fresnsFileOriginalUrlById(int|string $fileIdOrFid): ?string
    {
        if (StrHelper::isPureInt($fileIdOrFid)) {
            $file = File::whereId($fileIdOrFid)->first();
        } else {
            $file = File::whereFid($fileIdOrFid)->first();
        }

        if (empty($file)) {
            return null;
        }

        $storageConfig = FileHelper::fresnsFileStorageConfigByType($file->type);

        if ($storageConfig['antiLinkStatus']) {
            $fresnsResponse = \FresnsCmdWord::plugin($storageConfig['service'])->getAntiLinkFileOriginalUrl([
                'type' => $file->type,
                'fileIdOrFid' => strval($file->id),
            ]);

            return $fresnsResponse->getData('originalUrl') ?? null;
        }

        return $file->getFileOriginalUrl();
    }

    // get file document preview url
    public static function fresnsFileDocumentPreviewUrl(string $documentUrl, string $fid, ?string $fileExtension = null): ?string
    {
        $config = ConfigHelper::fresnsConfigByItemKeys([
            'document_online_preview',
            'document_preview_extension_names',
        ]);

        if (empty($config['document_online_preview']) || empty($config['document_preview_extension_names']) || empty($fileExtension)) {
            return null;
        }

        $documentPreviewUrl = null;

        $previewExtArr = explode(',', $config['document_preview_extension_names']);

        if (in_array($fileExtension, $previewExtArr)) {
            $replaceUrl = str_replace('{docurl}', $documentUrl, $config['document_online_preview']);

            $documentPreviewUrl = str_replace('{fid}', $fid, $replaceUrl);
        }

        return $documentPreviewUrl;
    }

    // get file type number
    public static function fresnsFileTypeNumber(?string $fileName = null): int
    {
        $fileName = Str::lower($fileName);

        $fileTypeNumber = match ($fileName) {
            'image' => File::TYPE_IMAGE,
            'video' => File::TYPE_VIDEO,
            'audio' => File::TYPE_AUDIO,
            'document' => File::TYPE_DOCUMENT,
            'images' => File::TYPE_IMAGE,
            'videos' => File::TYPE_VIDEO,
            'audios' => File::TYPE_AUDIO,
            'documents' => File::TYPE_DOCUMENT,
            default => File::TYPE_IMAGE,
        };

        return $fileTypeNumber;
    }

    // get file path by handle position
    public static function fresnsFilePathByHandlePosition(string $position, ?string $parameter = null, ?string $filePath = null): ?string
    {
        $position = match ($position) {
            'path-start' => 'path-start',
            'path-end' => 'path-end',
            'name-start' => 'name-start',
            'name-end' => 'name-end',
            default => null,
        };

        if (empty($position) || empty($filePath)) {
            return $filePath;
        }

        if ($position == 'path-start') {
            return $parameter.$filePath;
        }

        if ($position == 'path-end') {
            return $filePath.$parameter;
        }

        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $fileExtension = '.'.pathinfo($filePath, PATHINFO_EXTENSION);
        $fileDirectory = dirname($filePath);

        $newFilePath = match ($position) {
            'name-start' => $fileDirectory.$parameter.$fileName.$fileExtension,
            'name-end' => $fileDirectory.'/'.$fileName.$parameter.$fileExtension,
        };

        return $newFilePath;
    }

    // get file path for image
    // position name-start && name-end
    public static function fresnsFilePathForImage(string $position, ?string $filePath = null): array
    {
        $position = match ($position) {
            'name-start' => 'name-start',
            'name-end' => 'name-end',
            default => null,
        };

        if (empty($position) || empty($filePath)) {
            return [
                'configPath' => $filePath,
                'ratioPath' => $filePath,
                'squarePath' => $filePath,
                'bigPath' => $filePath,
            ];
        }

        $fileName = pathinfo($filePath, PATHINFO_FILENAME);
        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
        $fileDirectory = dirname($filePath);

        $fileNameLength = strlen($fileName);
        $newFileName = substr($fileName, 0, $fileNameLength - 16);

        switch ($position) {
            case 'name-start':
                $configPath = $fileDirectory.'/config-'.$newFileName.'.'.$fileExtension;
                $ratioPath = $fileDirectory.'/ratio-'.$newFileName.'.'.$fileExtension;
                $squarePath = $fileDirectory.'/square-'.$newFileName.'.'.$fileExtension;
                $bigPath = $fileDirectory.'/big-'.$newFileName.'.'.$fileExtension;
                break;

            case 'name-end':
                $configPath = $fileDirectory.'/'.$newFileName.'-config.'.$fileExtension;
                $ratioPath = $fileDirectory.'/'.$newFileName.'-ratio.'.$fileExtension;
                $squarePath = $fileDirectory.'/'.$newFileName.'-square.'.$fileExtension;
                $bigPath = $fileDirectory.'/'.$newFileName.'-big.'.$fileExtension;
                break;
        }

        return [
            'configPath' => $configPath,
            'ratioPath' => $ratioPath,
            'squarePath' => $squarePath,
            'bigPath' => $bigPath,
        ];
    }

    // handle anti link file info to list
    public static function handleAntiLinkFileInfoList(array $files): array
    {
        $imageStorageConfig = FileHelper::fresnsFileStorageConfigByType(File::TYPE_IMAGE);
        $videoStorageConfig = FileHelper::fresnsFileStorageConfigByType(File::TYPE_VIDEO);
        $audioStorageConfig = FileHelper::fresnsFileStorageConfigByType(File::TYPE_AUDIO);
        $documentStorageConfig = FileHelper::fresnsFileStorageConfigByType(File::TYPE_DOCUMENT);

        // image
        if ($imageStorageConfig['antiLinkStatus'] && ! empty($files['images'])) {
            $fids = array_column($files['images'], 'fid');

            $fresnsResponse = \FresnsCmdWord::plugin($imageStorageConfig['service'])->getAntiLinkFileInfoList([
                'type' => 1,
                'fileIdsOrFids' => $fids,
            ]);

            $files['images'] = $fresnsResponse->getData();
        } else {
            $files['images'] = array_map(fn ($item) => $item->getFileInfo(), $files['images']);
        }

        // video
        if ($videoStorageConfig['antiLinkStatus'] && ! empty($files['videos'])) {
            $fids = array_column($files['videos'], 'fid');

            $fresnsResponse = \FresnsCmdWord::plugin($videoStorageConfig['service'])->getAntiLinkFileInfoList([
                'type' => 2,
                'fileIdsOrFids' => $fids,
            ]);

            $files['videos'] = $fresnsResponse->getData();
        } else {
            $files['videos'] = array_map(fn ($item) => $item->getFileInfo(), $files['videos']);
        }

        // audio
        if ($audioStorageConfig['antiLinkStatus'] && ! empty($files['audios'])) {
            $fids = array_column($files['audios'], 'fid');

            $fresnsResponse = \FresnsCmdWord::plugin($audioStorageConfig['service'])->getAntiLinkFileInfoList([
                'type' => 3,
                'fileIdsOrFids' => $fids,
            ]);

            $files['audios'] = $fresnsResponse->getData();
        } else {
            $files['audios'] = array_map(fn ($item) => $item->getFileInfo(), $files['audios']);
        }

        // document
        if ($documentStorageConfig['antiLinkStatus'] && ! empty($files['documents'])) {
            $fids = array_column($files['documents'], 'fid');

            $fresnsResponse = \FresnsCmdWord::plugin($documentStorageConfig['service'])->getAntiLinkFileInfoList([
                'type' => 4,
                'fileIdsOrFids' => $fids,
            ]);

            $files['documents'] = $fresnsResponse->getData();
        } else {
            $files['documents'] = array_map(fn ($item) => $item->getFileInfo(), $files['documents']);
        }

        return $files;
    }
}
