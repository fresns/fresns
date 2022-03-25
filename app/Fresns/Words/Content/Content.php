<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Words\Content;

use App\Fresns\Api\Http\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\Http\FsCmd\FresnsSubPlugin;
use App\Fresns\Api\Http\FsDb\FresnsComments\FresnsCommentsService;
use App\Fresns\Words\File\DTO\PhysicalDeletionFileDTO;
use App\Fresns\Words\Service\PostsService;
use App\Models\CommentLog;
use App\Models\PostLog;

class Content
{
    /**
     * @param  PhysicalDeletionFileDTO  $wordBody
     * @return array
     *
     * @throws \Throwable
     */
    public function physicalDeletionContent(PhysicalDeletionFileDTO $wordBody)
    {
        $dtoWordBody = new PhysicalDeletionFileDTO($wordBody);
        $fid = $dtoWordBody->fid;
        $files = File::where('uuid', $fid)->first();
        if (empty($files)) {
            return ['code'=>30808, 'message'=>'FILE_EXIST_ERROR'];
        }

        $basePath = base_path().'/storage/app/public'.$files['file_path'];

        if (file_exists($basePath)) {
            unlink($basePath);
        }

        return ['code'=>0, 'message'=>'success'];
    }
}
