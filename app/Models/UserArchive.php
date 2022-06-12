<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

class UserArchive extends Model
{
    use Traits\IsEnableTrait;

    protected $guarded = [];

    public function getArchiveValueAttribute($value)
    {
        $value = match ($this->archive_type) {
            default => throw new \Exception("unknown archive type {$this->archive_type}"),
            'array', 'object' => json_decode($value, true) ?? [],
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => intval($value),
            'file' => $this->getArchiveFileUrl($value),
        };

        return $value;
    }

    public function getArchiveFileUrl($value)
    {
        if (! is_int($value)) {
            return $value;
        }

        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink([
            'fileId' => $value,
        ]);

        if ($fresnsResp->isSuccessResponse()) {
            return $fresnsResp->getData('imageConfigUrl');
        }

        return null;
    }
}
