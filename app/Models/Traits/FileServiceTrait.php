<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\FileHelper;
use App\Helpers\StrHelper;
use App\Models\File;
use Illuminate\Support\Facades\Storage;

trait FileServiceTrait
{
    public function size(): string
    {
        $fileData = $this;

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($fileData->size, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        $sizeString = round($bytes, 2).' '.$units[$pow];

        return $sizeString;
    }

    public function getFileUrl(): ?string
    {
        $fileData = $this;

        $fileConfigInfo = FileHelper::fresnsFileStorageConfigByType($fileData->type);

        if ($fileConfigInfo['filesystemDisk'] == 'local') {
            $filePath = Storage::build(config('filesystems.disks.public'))->url($fileData->path);
        } else {
            $filePath = $fileData->path;
        }

        $fileUrl = StrHelper::qualifyUrl($filePath, $fileConfigInfo['accessDomain']);

        return $fileUrl;
    }

    public function getFileOriginalUrl(): ?string
    {
        $fileData = $this;

        $fileConfigInfo = FileHelper::fresnsFileStorageConfigByType($fileData->type);

        if ($fileConfigInfo['filesystemDisk'] == 'local') {
            $fileOriginalPath = Storage::build(config('filesystems.disks.public'))->url($fileData->original_path);
            $filePath = Storage::build(config('filesystems.disks.public'))->url($fileData->path);
        } else {
            $fileOriginalPath = $fileData->original_path;
            $filePath = $fileData->path;
        }

        $path = $fileData->original_path ? $fileOriginalPath : $filePath;

        $originalUrl = StrHelper::qualifyUrl($path, $fileConfigInfo['accessDomain']);

        return $originalUrl;
    }

    public function getFileInfo(): array
    {
        $fileData = $this;

        $substitutionConfig = match ($fileData->type) {
            File::TYPE_IMAGE => 'image_substitution',
            File::TYPE_VIDEO => 'video_substitution',
            File::TYPE_AUDIO => 'audio_substitution',
            File::TYPE_DOCUMENT => 'document_substitution',
        };

        $warningText = match ($fileData->warning_type) {
            File::WARNING_NONE => 'none',
            File::WARNING_NUDITY => 'nudity',
            File::WARNING_VIOLENCE => 'violence',
            File::WARNING_SENSITIVE => 'sensitive',
            default => 'none',
        };

        $info['fid'] = $fileData->fid;
        $info['type'] = $fileData->type;
        $info['status'] = (bool) $fileData->is_enabled;
        $info['substitutionImageUrl'] = ConfigHelper::fresnsConfigFileUrlByItemKey($substitutionConfig);
        $info['warning'] = $warningText;
        $info['name'] = $fileData->name;
        $info['mime'] = $fileData->mime;
        $info['extension'] = $fileData->extension;
        $info['size'] = $fileData->size();
        $info['width'] = $fileData->width;
        $info['height'] = $fileData->height;
        $info['duration'] = $fileData->duration;
        $info['sha'] = $fileData->sha;
        $info['shaType'] = $fileData->sha_type;

        $fileMetaInfo = $this->getFileMetaInfoByType();

        return array_merge($info, $fileMetaInfo);
    }

    public function getFileMetaInfoByType(): array
    {
        $info = match ($this->type) {
            File::TYPE_IMAGE => $this->getImageMetaInfo(),
            File::TYPE_VIDEO => $this->getVideoMetaInfo(),
            File::TYPE_AUDIO => $this->getAudioMetaInfo(),
            File::TYPE_DOCUMENT => $this->getDocumentMetaInfo(),
            default => throw new \LogicException('unknown file type '.$this->type),
        };

        return $info;
    }

    public function getImageMetaInfo(): array
    {
        $fileData = $this;

        $imageConfig = ConfigHelper::fresnsConfigByItemKeys([
            'image_access_domain',
            'image_filesystem_disk',
            'image_handle_position',
            'image_thumb_config',
            'image_thumb_ratio',
            'image_thumb_square',
            'image_thumb_big',
        ]);

        if ($imageConfig['image_filesystem_disk'] == 'local') {
            $filePath = Storage::build(config('filesystems.disks.public'))->url($fileData->path);
        } else {
            $filePath = $fileData->path;
        }

        $info['imageLong'] = (bool) $fileData->is_long_image;

        $imageHandlePosition = $imageConfig['image_handle_position'];

        // imageHandlePosition = empty
        if (empty($imageHandlePosition)) {
            $imageUrl = StrHelper::qualifyUrl($filePath, $imageConfig['image_access_domain']);

            $info['imageConfigUrl'] = $imageUrl;
            $info['imageRatioUrl'] = $imageUrl;
            $info['imageSquareUrl'] = $imageUrl;
            $info['imageBigUrl'] = $imageUrl;

            return $info;
        }

        // imageHandlePosition = path-end
        if ($imageHandlePosition == 'path-end') {
            $imageUrl = StrHelper::qualifyUrl($filePath, $imageConfig['image_access_domain']);

            $info['imageConfigUrl'] = $imageUrl.$imageConfig['image_thumb_config'];
            $info['imageRatioUrl'] = $imageUrl.$imageConfig['image_thumb_ratio'];
            $info['imageSquareUrl'] = $imageUrl.$imageConfig['image_thumb_square'];
            $info['imageBigUrl'] = $imageUrl.$imageConfig['image_thumb_big'];

            return $info;
        }

        // imageHandlePosition = path-start
        $imageConfigPath = $imageConfig['image_thumb_config'].$filePath;
        $imageRatioPath = $imageConfig['image_thumb_ratio'].$filePath;
        $imageSquarePath = $imageConfig['image_thumb_square'].$filePath;
        $imageBigPath = $imageConfig['image_thumb_big'].$filePath;

        // imageHandlePosition = name-start && name-end
        if ($imageHandlePosition == 'name-start' || $imageHandlePosition == 'name-end') {
            $handlePath = FileHelper::fresnsFilePathForImage($imageHandlePosition, $filePath);

            $imageConfigPath = $handlePath['configPath'];
            $imageRatioPath = $handlePath['ratioPath'];
            $imageSquarePath = $handlePath['squarePath'];
            $imageBigPath = $handlePath['bigPath'];
        }

        $info['imageConfigUrl'] = StrHelper::qualifyUrl($imageConfigPath, $imageConfig['image_access_domain']);
        $info['imageRatioUrl'] = StrHelper::qualifyUrl($imageRatioPath, $imageConfig['image_access_domain']);
        $info['imageSquareUrl'] = StrHelper::qualifyUrl($imageSquarePath, $imageConfig['image_access_domain']);
        $info['imageBigUrl'] = StrHelper::qualifyUrl($imageBigPath, $imageConfig['image_access_domain']);

        return $info;
    }

    public function getVideoMetaInfo(): array
    {
        $fileData = $this;

        $videoConfig = ConfigHelper::fresnsConfigByItemKeys([
            'video_access_domain',
            'video_filesystem_disk',
            'video_poster_parameter',
            'video_poster_handle_position',
            'video_transcode_parameter',
            'video_transcode_handle_position',
        ]);

        if ($videoConfig['video_filesystem_disk'] == 'local') {
            $posterPath = Storage::build(config('filesystems.disks.public'))->url($fileData->video_poster_path);
            $filePath = Storage::build(config('filesystems.disks.public'))->url($fileData->path);
        } else {
            $posterPath = $fileData->video_poster_path;
            $filePath = $fileData->path;
        }

        if ($videoConfig['video_poster_handle_position']) {
            $posterPath = FileHelper::fresnsFilePathByHandlePosition($videoConfig['video_poster_handle_position'], $videoConfig['video_poster_parameter'], $posterPath);
        }

        if ($videoConfig['video_transcode_handle_position']) {
            $filePath = FileHelper::fresnsFilePathByHandlePosition($videoConfig['video_transcode_handle_position'], $videoConfig['video_transcode_parameter'], $filePath);
        }

        $info['videoPosterUrl'] = StrHelper::qualifyUrl($posterPath, $videoConfig['video_access_domain']);
        $info['videoUrl'] = StrHelper::qualifyUrl($filePath, $videoConfig['video_access_domain']);
        $info['transcodingState'] = $fileData->transcoding_state;

        return $info;
    }

    public function getAudioMetaInfo(): array
    {
        $fileData = $this;

        $audioConfig = ConfigHelper::fresnsConfigByItemKeys([
            'audio_access_domain',
            'audio_filesystem_disk',
            'audio_transcode_parameter',
            'audio_transcode_handle_position',
        ]);

        if ($audioConfig['audio_filesystem_disk'] == 'local') {
            $filePath = Storage::build(config('filesystems.disks.public'))->url($fileData->path);
        } else {
            $filePath = $fileData->path;
        }

        if ($audioConfig['audio_transcode_handle_position']) {
            $filePath = FileHelper::fresnsFilePathByHandlePosition($audioConfig['audio_transcode_handle_position'], $audioConfig['audio_transcode_parameter'], $filePath);
        }

        $info['audioUrl'] = StrHelper::qualifyUrl($filePath, $audioConfig['audio_access_domain']);
        $info['transcodingState'] = $fileData->transcoding_state;

        return $info;
    }

    public function getDocumentMetaInfo(): array
    {
        $fileData = $this;

        $info['documentPreviewUrl'] = FileHelper::fresnsFileDocumentPreviewUrl($fileData->extension);

        return $info;
    }
}
