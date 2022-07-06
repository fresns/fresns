<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait FsidTrait
{
    public static function bootFsidTrait()
    {
        static::creating(function ($model) {
            $model->{$this->getFsidKey()} = $model->{$this->getFsidKey()} ?? static::generateFsid(8);
        });
    }

    // generate fsid
    public static function generateFsid($digit): string
    {
        $fsid = Str::random($digit);

        $checkFsid = static::fsid($fsid)->first();

        if (! $checkFsid) {
            return $fsid;
        } else {
            $newFsid = Str::random($digit);
            $checkNewFsid = static::fsid($newFsid)->first();
            if (! $checkNewFsid) {
                return $newFsid;
            }
        }

        return static::generateFsid($digit + 1);
    }

    public function scopeFsid($query, string $fsid)
    {
        return $query->where($this->getFsidKey(), $fsid);
    }
}
