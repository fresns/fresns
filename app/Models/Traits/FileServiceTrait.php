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

        if ($fileData->disk == 'local') {
            $fileOriginalPath = Storage::url($fileData->original_path);
            $filePath = Storage::url($fileData->path);

            $bucketDomain = ConfigHelper::fresnsConfigByItemKey('system_url');
        } else {
            $fileOriginalPath = $fileData->original_path;
            $filePath = $fileData->path;

            $fileConfigInfo = FileHelper::fresnsFileStorageConfigByType($fileData->type);
            $bucketDomain = $fileConfigInfo['bucketDomain'];
        }

        $path = $fileOriginalPath ?: $filePath;

        $originalUrl = StrHelper::qualifyUrl($path, $bucketDomain);

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
        $info['sensitive'] = (bool) $fileData->is_sensitive;
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
            'image_handle_position',
            'image_thumb_config',
            'image_thumb_avatar',
            'image_thumb_ratio',
            'image_thumb_square',
            'image_thumb_big',
        ]);

        $imageHandlePosition = $fileData->image_handle_position ?? $imageConfig['image_handle_position'];

        if ($fileData->disk == 'local') {
            $filePath = Storage::url($fileData->path);

            $bucketDomain = ConfigHelper::fresnsConfigByItemKey('system_url');
        } else {
            $filePath = $fileData->path;

            $bucketDomain = $imageConfig['image_bucket_domain'];
        }

        $info['imageWidth'] = $fileData->image_width;
        $info['imageHeight'] = $fileData->image_height;
        $info['imageLong'] = (bool) $fileData->image_is_long;

        // imageHandlePosition = end
        if ($imageHandlePosition == 'end') {
            $imageUrl = StrHelper::qualifyUrl($filePath, $bucketDomain);

            $info['imageConfigUrl'] = $imageUrl.$imageConfig['image_thumb_config'];
            $info['imageAvatarUrl'] = $imageUrl.$imageConfig['image_thumb_avatar'];
            $info['imageRatioUrl'] = $imageUrl.$imageConfig['image_thumb_ratio'];
            $info['imageSquareUrl'] = $imageUrl.$imageConfig['image_thumb_square'];
            $info['imageBigUrl'] = $imageUrl.$imageConfig['image_thumb_big'];

            return $info;
        }

        // imageHandlePosition = start
        $imageConfigPath = $imageConfig['image_thumb_config'].$filePath;
        $imageAvatarPath = $imageConfig['image_thumb_avatar'].$filePath;
        $imageRatioPath = $imageConfig['image_thumb_ratio'].$filePath;
        $imageSquarePath = $imageConfig['image_thumb_square'].$filePath;
        $imageBigPath = $imageConfig['image_thumb_big'].$filePath;

        // imageHandlePosition = name-start && name-end
        if ($imageHandlePosition == 'name-start' || $imageHandlePosition == 'name-end') {
            $handlePath = FileHelper::fresnsFilePathForImage($imageHandlePosition, $filePath);

            $imageConfigPath = $handlePath['configPath'];
            $imageAvatarPath = $handlePath['avatarPath'];
            $imageRatioPath = $handlePath['ratioPath'];
            $imageSquarePath = $handlePath['squarePath'];
            $imageBigPath = $handlePath['bigPath'];
        }

        $info['imageConfigUrl'] = StrHelper::qualifyUrl($imageConfigPath, $bucketDomain);
        $info['imageAvatarUrl'] = StrHelper::qualifyUrl($imageAvatarPath, $bucketDomain);
        $info['imageRatioUrl'] = StrHelper::qualifyUrl($imageRatioPath, $bucketDomain);
        $info['imageSquareUrl'] = StrHelper::qualifyUrl($imageSquarePath, $bucketDomain);
        $info['imageBigUrl'] = StrHelper::qualifyUrl($imageBigPath, $bucketDomain);

        return $info;
    }

    public function getVideoMetaInfo()
    {
        $fileData = $this;

        if ($fileData->disk == 'local') {
            $videoCoverPath = Storage::url($fileData->video_cover_path);
            $videoGifPath = Storage::url($fileData->video_gif_path);
            $filePath = Storage::url($fileData->path);

            $bucketDomain = ConfigHelper::fresnsConfigByItemKey('system_url');
        } else {
            $videoCoverPath = $fileData->video_cover_path;
            $videoGifPath = $fileData->video_gif_path;
            $filePath = $fileData->path;

            $bucketDomain = ConfigHelper::fresnsConfigByItemKey('video_bucket_domain');
        }

        $info['videoTime'] = $fileData->video_time;
        $info['videoCoverUrl'] = StrHelper::qualifyUrl($videoCoverPath, $bucketDomain);
        $info['videoGifUrl'] = StrHelper::qualifyUrl($videoGifPath, $bucketDomain);
        $info['videoUrl'] = StrHelper::qualifyUrl($filePath, $bucketDomain);
        $info['transcodingState'] = $fileData->transcoding_state;

        return $info;
    }

    public function getAudioMetaInfo()
    {
        $fileData = $this;

        if ($fileData->disk == 'local') {
            $filePath = Storage::url($fileData->path);

            $bucketDomain = ConfigHelper::fresnsConfigByItemKey('system_url');
        } else {
            $filePath = $fileData->path;

            $bucketDomain = ConfigHelper::fresnsConfigByItemKey('audio_bucket_domain');
        }

        $info['audioTime'] = $fileData->audio_time;
        $info['audioUrl'] = StrHelper::qualifyUrl($filePath, $bucketDomain);
        $info['transcodingState'] = $fileData->transcoding_state;

        return $info;
    }

    public function getDocumentMetaInfo()
    {
        $fileData = $this;

        $documentConfig = ConfigHelper::fresnsConfigByItemKeys([
            'document_bucket_domain',
            'document_online_preview',
            'document_preview_extension_names',
        ]);

        if ($fileData->disk == 'local') {
            $filePath = Storage::url($fileData->path);

            $bucketDomain = ConfigHelper::fresnsConfigByItemKey('system_url');
        } else {
            $filePath = $fileData->path;

            $bucketDomain = $documentConfig['document_bucket_domain'];
        }

        $info['documentUrl'] = StrHelper::qualifyUrl($filePath, $bucketDomain);

        $documentPreviewUrl = null;
        if (! empty($documentConfig['document_online_preview']) && ! empty($documentConfig['document_preview_extension_names'])) {
            $previewExtArr = explode(',', $documentConfig['document_preview_extension_names']);
            if (in_array($fileData->extension, $previewExtArr)) {
                $replaceUrl = str_replace('{docurl}', $info['documentUrl'], $documentConfig['document_online_preview']);
                $documentPreviewUrl = str_replace('{fid}', $fileData->fid, $replaceUrl);
            }
        }

        $info['documentPreviewUrl'] = $documentPreviewUrl;

        return $info;
    }
}
