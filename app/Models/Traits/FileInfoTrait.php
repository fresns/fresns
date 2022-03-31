<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Helpers\ConfigHelper;
use App\Helpers\StrHelper;
use App\Models\File;

trait FileInfoTrait
{
    public function getImageThumbData()
    {
        return ConfigHelper::fresnsConfigByItemKeys([
            'image_thumb_config',
            'image_thumb_avatar',
            'image_thumb_ratio',
            'image_thumb_square',
            'image_thumb_big',
        ]);
    }

    public function getFileInfo()
    {
        return array_merge([
            'fid' => $this->fid,
            'type' => $this->file_type,
            'rankNum' => $this->rank_num,
            'name' => $this->file_name,
            'extension' => $this->file_extension,
        ], $this->getFileAppendInfo());
    }

    public function getFileAppendInfo()
    {
        $append = $this->fileAppend;

        $data = match ($this->file_type) {
            File::TYPE_IMAGE => $this->getImageAppendInfo(),
            File::TYPE_VIDEO => $this->getVideoAppendInfo(),
            File::TYPE_AUDIO => $this->getAudioAppendInfo(),
            File::TYPE_DOCUMENT => $this->getDocumentAppendInfo(),
            default => throw new \LogicException('unknown file_type '.$this->file_type),
        };

        return array_merge([
            'mime' => $append->file_mime,
            'size' => $append->file_size,
            'moreJson' => $append->more_json,
        ], $data);
    }

    public function getImageAppendInfo()
    {
        $file = $this;
        $append = $this->fileAppend;

        $image_bucket_domain = ConfigHelper::fresnsConfigByItemKey('image_bucket_domain');

        $thumbData = $this->getImageThumbData();

        $imageDefaultUrl = StrHelper::qualifyUrl($file->file_path, $image_bucket_domain);

        return [
            'imageWidth' => $append->image_width,
            'imageHeight' => $append->image_height,
            'imageLong' => $append->image_is_long,
            'imageDefaultUrl' => $imageDefaultUrl,
            'imageConfigUrl' => $imageDefaultUrl.$thumbData['image_thumb_config'],
            'imageAvatarUrl' => $imageDefaultUrl.$thumbData['image_thumb_avatar'],
            'imageRatioUrl' => $imageDefaultUrl.$thumbData['image_thumb_ratio'],
            'imageSquareUrl' => $imageDefaultUrl.$thumbData['image_thumb_square'],
            'imageBigUrl' => $imageDefaultUrl.$thumbData['image_thumb_big'],
            'imageOriginalUrl' => StrHelper::qualifyUrl($append->file_original_path, $image_bucket_domain),
        ];
    }

    public function getVideoAppendInfo()
    {
        $file = $this;
        $append = $this->fileAppend;

        $video_bucket_domain = ConfigHelper::fresnsConfigByItemKey('video_bucket_domain');

        return [
            'videoTime' => $append->video_time,
            'videoCover' => StrHelper::qualifyUrl($append->video_cover, $video_bucket_domain),
            'videoGif' => StrHelper::qualifyUrl($append->video_gif, $video_bucket_domain),
            'videoUrl' => StrHelper::qualifyUrl($file->file_path, $video_bucket_domain),
            'videoOriginalUrl' => StrHelper::qualifyUrl($append->file_original_path, $video_bucket_domain),
            'transcodingState' => $append->transcoding_state,
        ];
    }

    public function getAudioAppendInfo()
    {
        $file = $this;
        $append = $this->fileAppend;

        $audio_bucket_domain = ConfigHelper::fresnsConfigByItemKey('audio_bucket_domain');

        return [
            'audioTime' => $append->audio_time,
            'audioUrl' => StrHelper::qualifyUrl($file->file_path, $audio_bucket_domain),
            'audioOriginalUrl' => StrHelper::qualifyUrl($append->file_original_path, $audio_bucket_domain),
            'transcodingState' => $append->transcoding_state,
        ];
    }

    public function getDocumentAppendInfo()
    {
        $file = $this;
        $append = $this->fileAppend;

        $document_bucket_domain = ConfigHelper::fresnsConfigByItemKey('document_bucket_domain');

        return [
            'documentUrl' => StrHelper::qualifyUrl($file->file_path, $document_bucket_domain),
            'documentOriginalUrl' => StrHelper::qualifyUrl($append->file_original_path, $document_bucket_domain),
        ];
    }
}
