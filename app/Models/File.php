<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jevan Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class File extends Model
{
    const TYPE_ALL = 1234;
    const TYPE_IMAGE = 1;
    const TYPE_VIDEO = 2;
    const TYPE_AUDIO = 3;
    const TYPE_DOCUMENT = 4;

    const TYPE_MAP = [
        File::TYPE_IMAGE => 'Image',
        File::TYPE_VIDEO => 'Video',
        File::TYPE_AUDIO => 'Audio',
        File::TYPE_DOCUMENT => 'Document',
    ];

    const TRANSCODING_STATE_WAIT = 1;
    const TRANSCODING_STATE_ING = 2;
    const TRANSCODING_STATE_DONE = 3;
    const TRANSCODING_STATE_FAILURE = 4;

    const STORAGE_UNKNOWN = 1;
    const STORAGE_LOCAL = 2;
    const STORAGE_WEB_DAV = 3;
    const STORAGE_AMAZON = 4;
    const STORAGE_BACKBLAZE = 5;
    const STORAGE_DROPBOX = 6;
    const STORAGE_ONE_DRIVE = 7;
    const STORAGE_AZURE = 8;
    const STORAGE_GOOGLE_CLOUD = 9;
    const STORAGE_GOOGLE_DRIVE = 10;
    const STORAGE_OPENSTACK_SWIFT = 11;
    const STORAGE_BOX = 12;
    const STORAGE_CLOUDINARY = 13;
    const STORAGE_DIGITAL_OCEAN = 14;
    const STORAGE_LINODE = 15;
    const STORAGE_VULTR = 16;
    const STORAGE_QINIU = 17;
    const STORAGE_UPYUN = 18;
    const STORAGE_ALIBABA_CLOUD = 19;
    const STORAGE_TENCENT_CLOUD = 20;
    const STORAGE_VOLC_ENGINE = 21;
    const STORAGE_NETEASE_CLOUD = 22;
    const STORAGE_UCLOUD = 23;
    const STORAGE_HUAWEI_CLOUD = 24;
    const STORAGE_KINGSOFT_CLOUD = 25;
    const STORAGE_HUAYUN = 26;
    const STORAGE_TELECOM_CLOUD = 27;
    const STORAGE_POLYV = 28;
    const STORAGE_FASTLY = 29;
    const STORAGE_CLOUDFLARE = 30;

    use Traits\FileServiceTrait;
    use Traits\IsEnabledTrait;
    use Traits\FsidTrait;

    protected $casts = [
        'more_info' => 'json',
    ];

    public function getFsidKey()
    {
        return 'fid';
    }

    public function getTypeKey()
    {
        return match ($this->type) {
            default => throw new \RuntimeException("unknown file type of {$this->type}"),
            File::TYPE_IMAGE => 'image',
            File::TYPE_VIDEO => 'video',
            File::TYPE_AUDIO => 'audio',
            File::TYPE_DOCUMENT => 'document',
        };
    }

    public function scopeType($query, int $type)
    {
        return $query->where('type', $type);
    }

    public function fileUsages()
    {
        return $this->hasMany(FileUsage::class);
    }

    public function isImage()
    {
        return $this->type === File::TYPE_IMAGE;
    }

    public function isVideo()
    {
        return $this->type === File::TYPE_VIDEO;
    }

    public function isAudio()
    {
        return $this->type === File::TYPE_AUDIO;
    }

    public function isDocument()
    {
        return $this->type === File::TYPE_DOCUMENT;
    }
}
