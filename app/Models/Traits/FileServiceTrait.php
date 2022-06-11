<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
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
    public function getFileOriginalUrl()
    {
        $fileData = $this;

        $fileConfigInfo = FileHelper::fresnsFileStorageConfigByType($fileData->type);

        if ($fileConfigInfo['filesystemDisk'] == 'local') {
            $fileOriginalPath = Storage::url($fileData->fileAppend->original_path);
            $filePath = Storage::url($fileData->path);
        } else {
            $fileOriginalPath = $fileData->fileAppend->original_path;
            $filePath = $fileData->path;
        }

        $path = $fileOriginalPath ?: $filePath;

        $originalUrl = StrHelper::qualifyUrl($path, $fileConfigInfo['bucketDomain']);

        return $originalUrl;
    }

    public function getFileInfo()
    {
        $fileData = $this;

        if ($fileData->size > 1048576) {
            $fileSize = round($fileData->size / 1048576);
            $fileSizeInfo = $fileSize.' MB';
        } elseif ($fileData->size > 1024) {
            $fileSize = round($fileData->size / 1024, 2);
            $fileSizeInfo = $fileSize.' KB';
        } else {
            $fileSizeInfo = $fileData->size.' B';
        }

        $substitutionConfig = match ($fileData->type) {
            File::TYPE_IMAGE => 'image_substitution',
            File::TYPE_VIDEO => 'video_substitution',
            File::TYPE_AUDIO => 'audio_substitution',
            File::TYPE_DOCUMENT => 'document_substitution',
        };

        $info['fid'] = $fileData->fid;
        $info['type'] = $fileData->type;
        $info['status'] = (bool) $fileData->is_enable;
        $info['substitutionImageUrl'] = ConfigHelper::fresnsConfigFileUrlByItemKey($substitutionConfig);
        $info['name'] = $fileData->name;
        $info['mime'] = $fileData->mime;
        $info['extension'] = $fileData->extension;
        $info['size'] = $fileSizeInfo;
        $info['md5'] = $fileData->md5;
        $info['sha'] = $fileData->sha;
        $info['shaType'] = $fileData->sha_type;
        $info['moreJson'] = $fileData->more_json;

        $fileMetaInfo = $this->getFileMetaInfoByType();

        return array_merge($info, $fileMetaInfo);
    }

    public function getFileMetaInfoByType()
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

    public function getImageMetaInfo()
    {
        $fileData = $this;

        $imageConfig = ConfigHelper::fresnsConfigByItemKeys([
            'image_bucket_domain',
            'image_filesystem_disk',
            'image_thumb_config',
            'image_thumb_avatar',
            'image_thumb_ratio',
            'image_thumb_square',
            'image_thumb_big'
        ]);

        if ($imageConfig['image_filesystem_disk'] == 'local') {
            $filePath = Storage::url($fileData->path);
        } else {
            $filePath = $fileData->path;
        }

        $imageDefaultUrl = StrHelper::qualifyUrl($filePath, $imageConfig['image_bucket_domain']);

        $info['imageWidth'] = $fileData->image_width;
        $info['imageHeight'] = $fileData->image_height;
        $info['imageLong'] = (bool) $fileData->image_is_long;
        $info['imageDefaultUrl'] = $imageDefaultUrl;
        $info['imageConfigUrl'] = $imageDefaultUrl.$imageConfig['image_thumb_config'];
        $info['imageAvatarUrl'] = $imageDefaultUrl.$imageConfig['image_thumb_avatar'];
        $info['imageRatioUrl'] = $imageDefaultUrl.$imageConfig['image_thumb_ratio'];
        $info['imageSquareUrl'] = $imageDefaultUrl.$imageConfig['image_thumb_square'];
        $info['imageBigUrl'] = $imageDefaultUrl.$imageConfig['image_thumb_big'];

        return $info;
    }

    public function getVideoMetaInfo()
    {
        $fileData = $this;

        $videoConfig = ConfigHelper::fresnsConfigByItemKeys([
            'video_bucket_domain',
            'video_filesystem_disk',
        ]);

        if ($videoConfig['video_filesystem_disk'] == 'local') {
            $videoCoverPath = Storage::url($fileData->video_cover_path);
            $videoGifPath = Storage::url($fileData->video_gif_path);
            $filePath = Storage::url($fileData->path);
        } else {
            $videoCoverPath = $fileData->video_cover_path;
            $videoGifPath = $fileData->video_gif_path;
            $filePath = $fileData->path;
        }

        $info['videoTime'] = $fileData->video_time;
        $info['videoCoverUrl'] = StrHelper::qualifyUrl($videoCoverPath, $videoConfig['video_bucket_domain']);
        $info['videoGifUrl'] = StrHelper::qualifyUrl($videoGifPath, $videoConfig['video_bucket_domain']);
        $info['videoUrl'] = StrHelper::qualifyUrl($filePath, $videoConfig['video_bucket_domain']);
        $info['transcodingState'] = $fileData->transcoding_state;

        return $info;
    }

    public function getAudioMetaInfo()
    {
        $fileData = $this;

        $audioConfig = ConfigHelper::fresnsConfigByItemKeys([
            'audio_bucket_domain',
            'audio_filesystem_disk',
        ]);

        if ($audioConfig['audio_filesystem_disk'] == 'local') {
            $filePath = Storage::url($fileData->path);
        } else {
            $filePath = $fileData->path;
        }

        $info['audioTime'] = $fileData->audio_time;
        $info['audioUrl'] = StrHelper::qualifyUrl($filePath, $audioConfig['audio_bucket_domain']);
        $info['transcodingState'] = $fileData->transcoding_state;

        return $info;
    }

    public function getDocumentMetaInfo()
    {
        $fileData = $this;

        $documentConfig = ConfigHelper::fresnsConfigByItemKeys([
            'document_bucket_domain',
            'document_filesystem_disk',
            'document_online_preview',
            'document_preview_ext',
        ]);

        if ($documentConfig['document_filesystem_disk'] == 'local') {
            $filePath = Storage::url($fileData->path);
        } else {
            $filePath = $fileData->path;
        }

        $info['documentUrl'] = StrHelper::qualifyUrl($filePath, $documentConfig['document_bucket_domain']);

        $documentPreviewUrl = null;
        if (! empty($documentConfig['document_online_preview']) && ! empty($documentConfig['document_preview_ext'])) {
            $previewExtArr = explode(',', $documentConfig['document_preview_ext']);
            if (in_array($fileData->extension, $previewExtArr)) {
                $replaceUrl = str_replace('{docurl}', $info['documentUrl'], $documentConfig['document_online_preview']);
                $documentPreviewUrl = str_replace('{fid}', $fileData->fid, $replaceUrl);
            }
        }

        $info['documentPreviewUrl'] = $documentPreviewUrl;

        return $info;
    }
}
