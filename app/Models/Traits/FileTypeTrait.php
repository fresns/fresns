<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use App\Models\File;

trait FileTypeTrait
{
    public function isImage()
    {
        return $this->file_type === File::TYPE_IMAGE;
    }

    public function isVideo()
    {
        return $this->file_type === File::TYPE_VIDEO;
    }

    public function isAudio()
    {
        return $this->file_type === File::TYPE_AUDIO;
    }

    public function isDocument()
    {
        return $this->file_type === File::TYPE_DOCUMENT;
    }
}
