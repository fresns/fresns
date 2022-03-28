<?php

namespace App\Models\Traits;

use App\Models\File;
use App\Helpers\ConfigHelper;

trait FileInfoTrait
{
    public function getBucketDomains()
    {
        return ConfigHelper::fresnsConfigByItemKeys([
            'image_bucket_domain',
            'video_bucket_domain',
            'audio_bucket_domain',
            'document_bucket_domain',
        ]);
    }

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
        /** @var FileAppend */
        $append = $this->fileAppend;

        $data = match($this->file_type) {
            File::TYPE_IMAGE => $this->getImageAppendInfo(),
            File::TYPE_VIDEO => $this->getVideoAppendInfo(),
            File::TYPE_AUDIO => $this->getAudioAppendInfo(),
            File::TYPE_DOCUMENT => $this->getDocumentAppendInfo(),
            default => throw new \LogicException("unknown file_type ". $this->file_type),
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

        /** @var FileAppend */
        $append = $this->fileAppend;

        [
            'image_bucket_domain' => $image_bucket_domain,
        ] = $this->getBucketDomains();

        $thumbData = $this->getImageThumbData();

        $imageDefaultUrl = sprintf('%s/%s', rtrim($image_bucket_domain, '/'), ltrim($file->file_path, '/'));

        return [
            'imageWidth' => $append->image_width,
            'imageHeight' => $append->image_height,
            'imageLong' => $append->image_is_long,
            'imageDefaultUrl' => $imageDefaultUrl,
            'imageConfigUrl' => $imageDefaultUrl . $thumbData['image_thumb_config'],
            'imageAvatarUrl' => $imageDefaultUrl . $thumbData['image_thumb_avatar'],
            'imageRatioUrl' => $imageDefaultUrl . $thumbData['image_thumb_ratio'],
            'imageSquareUrl' => $imageDefaultUrl . $thumbData['image_thumb_square'],
            'imageBigUrl' => $imageDefaultUrl . $thumbData['image_thumb_big'],
        ];
    }

    public function getVideoAppendInfo()
    {
        $file = $this;

        /** @var FileAppend */
        $append = $this->fileAppend;

        [
            'video_bucket_domain' => $video_bucket_domain,
        ] = $this->getBucketDomains();

        return [
            'videoTime' => $append->video_time,
            'videoCover' => sprintf('%s%s', $video_bucket_domain, $append->video_cover),
            'videoGif' => sprintf('%s%s', $video_bucket_domain, $append->video_gif),
            'videoUrl' => sprintf('%s%s', $video_bucket_domain, $file->file_path),
            'transcodingState' => $append->transcoding_state,
        ];
    }

    public function getAudioAppendInfo()
    {
        $file = $this;

        /** @var FileAppend */
        $append = $this->fileAppend;

        [
            'audio_bucket_domain' => $audio_bucket_domain,
        ] = $this->getBucketDomains();

        return [
            'audioTime' => $append->audio_time,
            'audioUrl' => sprintf('%s%s', $audio_bucket_domain, $file->file_path),
            'transcodingState' => $append->transcoding_state,
        ];
    }

    public function getDocumentAppendInfo()
    {
        $file = $this;

        [
            'document_bucket_domain' => $document_bucket_domain,
        ] = $this->getBucketDomains();

        return [
            'documentUrl' => sprintf('%s%s', $document_bucket_domain, $file->file_path),
        ];
    }
}