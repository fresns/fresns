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
use App\Fresns\Words\File\DTO\PhysicalDeletionFile;
use App\Fresns\Words\Service\PostsService;
use App\Models\CommentLog;
use App\Models\PostLog;

class Content
{
    public function physicalDeletionContent(PhysicalDeletionFile $wordBody)
    {
//        $fid = $wordBody->fid;
//        $files = File::where('uuid', $fid)->first();
//        if (empty($files)) {
//            return ['code'=>30808,'message'=>'FILE_EXIST_ERROR'];
//        }
//
//        $basePath = base_path().'/storage/app/public'.$files['file_path'];
//
//        if (file_exists($basePath)) {
//            unlink($basePath);
//        }

        return ['code'=>200, 'message'=>'success'];
    }

    /**
     * @param $wordBody
     * @return array
     */
    public function releaseContent($wordBody)
    {
        $type = $wordBody['type'];
        $logId = $wordBody['logId'];
        $sessionLogsId = $wordBody['sessionLogsId'];
        $commentCid = $wordBody['commentCid'] ?? 0;
        $FresnsPostsService = new PostsService();
        $fresnsCommentService = new FresnsCommentsService();
        switch ($type) {
            case 1:
                $result = $FresnsPostsService->releaseByDraft($logId, $sessionLogsId);
                $postId = PostLog::find($logId);
                $cmd = 'fresns_cmd_sub_active_command_word';
                $wordBody = [
                    'tableName' => 'posts',
                    'insertId' => $postId['post_id'],
                    'commandWord' => 'fresns_cmd_direct_release_content',
                ];
                $resp = CmdRpcHelper::call(FresnsSubPlugin::class, $cmd, $wordBody);
                break;
            case 2:
                $result = $fresnsCommentService->releaseByDraft($logId, $commentCid, $sessionLogsId);
                $commentInfo = CommentLog::find($logId);
                $cmd = 'fresns_cmd_sub_active_command_word';
                $wordBody = [
                    'tableName' => 'comments',
                    'insertId' => $commentInfo['comment_id'],
                    'commandWord' => 'fresns_cmd_direct_release_content',
                ];
                $resp = CmdRpcHelper::call(FresnsSubPlugin::class, $cmd, $wordBody);
                break;
        }

        return ['code'=>200, 'message'=>'success'];
    }
}
