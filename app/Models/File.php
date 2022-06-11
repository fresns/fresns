<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class File extends Model
{
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

    use Traits\FileServiceTrait;
    use Traits\IsEnableTrait;

    protected $guarded = [];

    public function fileAppend()
    {
        return $this->hasOne(FileAppend::class);
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
