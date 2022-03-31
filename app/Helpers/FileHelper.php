<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\File;

class FileHelper
{
    public static function fresnsFileUrlById(int $fileId)
    {
        $file = File::idOrFid(['id' => $fileId])->firstOrFail();

        $fileInfo = $file->getFileInfo();

        return collect($fileInfo)->only([
            'type',
            'imageDefaultUrl',
            'imageConfigUrl',
            'imageAvatarUrl',
            'imageRatioUrl',
            'imageSquareUrl',
            'imageBigUrl',
            'imageOriginalUrl',
            'videoCover',
            'videoGif',
            'videoUrl',
            'videoOriginalUrl',
            'audioUrl',
            'audioOriginalUrl',
            'documentUrl',
            'documentOriginalUrl',
        ]);
    }

    public static function fresnsFileUrlByFid(string $fid)
    {
        $file = File::idOrFid(['fid' => $fid])->firstOrFail();

        $fileInfo = $file->getFileInfo();

        return collect($fileInfo)->only([
            'type',
            'imageDefaultUrl',
            'imageConfigUrl',
            'imageAvatarUrl',
            'imageRatioUrl',
            'imageSquareUrl',
            'imageBigUrl',
            'imageOriginalUrl',
            'videoCover',
            'videoGif',
            'videoUrl',
            'videoOriginalUrl',
            'audioUrl',
            'audioOriginalUrl',
            'documentUrl',
            'documentOriginalUrl',
        ]);
    }

    public static function fresnsFileInfoById(int $fileId)
    {
        $file = File::idOrFid(['id' => $fileId])->firstOrFail();

        $fileInfo = $file->getFileInfo();

        return $fileInfo;
    }

    public static function fresnsFileInfoByFid(string $fid)
    {
        $file = File::idOrFid(['fid' => $fid])->firstOrFail();

        $fileInfo = $file->getFileInfo();

        return $fileInfo;
    }

    /**
     * Determine the storage type based on the file key value.
     *
     * @param  string  $itemKey
     * @return string
     */
    public static function fresnsFileConfigTypeByItemKey(string $itemKey)
    {
        $file = ConfigHelper::fresnsConfigByItemKey($itemKey);
        if (is_numeric($file)) {
            return 'ID';
        } elseif (preg_match("/^(http:\/\/|https:\/\/).*$/", $file)) {
            return 'URL';
        }

        return 'null';
    }

    public static function fresnsFileImageUrlByColumn($fileId, $fileUrl, $urlType)
    {
        if (! $fileId) {
            return $fileUrl;
        }

        if (! File::isEnableAntiTheftChainOfFileType(File::TYPE_IMAGE)) {
            return $fileUrl;
        }

        $fresnsResponse = \FresnsCmdWord::plugin()->getFileUrlOfAntiLink([
            'fileId' => $fileId,
        ]);

        return $fresnsResponse->getData($urlType);
    }
}
