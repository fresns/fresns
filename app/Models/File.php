<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    use Traits\FileTypeTrait;
    use Traits\FileServiceInfoTrait;
    use Traits\FileInfoTrait;
    use Traits\FileStorageTrait;
    
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

    protected $guarded = [];

    public function scopeIdOrFid($query, $params)
    {
        return $query
            ->when(!empty($params['id']), function ($query) use ($params) {
                $query->where('id', $params['id']);
            })
            ->when(!empty($params['fid']), function ($query) use ($params) {
                $query->where('fid', $params['fid']);
            });
    }

    public function appends()
    {
        return $this->hasOne(FileAppend::class);
    }

    public function fileAppend()
    {
        return $this->hasOne(FileAppend::class);
    }

    public function getTypeKey()
    {
        return match($this->file_type) {
            default => throw new \RuntimeException("unknown file_type of {$this->file_type}"),
            File::TYPE_IMAGE => 'image',
            File::TYPE_VIDEO => 'video',
            File::TYPE_AUDIO => 'audio',
            File::TYPE_DOCUMENT => 'document',
        };
    }

    public function getDestinationPath()
    {
        $fileType = $this->file_type;
        $tableType = $this->table_type;

        $fileTypeDir = match ($fileType) {
            1 => 'images',
            2 => 'videos',
            3 => 'audios',
            4 => 'documents',
            default => throw new \LogicException("unknown file_type $fileType"),
        };

        $tableTypeDir = match ($tableType) {
            1 => '/mores/',
            2 => '/configs/system/',
            3 => '/configs/operating/',
            4 => '/configs/sticker/',
            5 => '/configs/user/',
            6 => '/avatars/{YYYYMM}/{DD}/',
            7 => '/dialogs/{YYYYMM}/{DD}/',
            8 => '/posts/{YYYYMM}/{DD}/',
            9 => '/comments/{YYYYMM}/{DD}/',
            10 => '/extends/{YYYYMM}/{DD}/',
            11 => '/plugins/{YYYYMM}/{DD}/',
            default => throw new \LogicException("unknown table_type $tableType"),
        };

        $replaceTableTypeDir = str_replace(
            ['{YYYYMM}', '{DD}'],
            [date('Ym'), date('d')],
            $tableTypeDir
        );

        if (in_array($tableType, range(1, 6))) {
            return sprintf('%s', trim($replaceTableTypeDir, '/'));
        }

        return sprintf('%s/%s', trim($fileTypeDir, '/'), trim($replaceTableTypeDir, '/'));
    }
}
