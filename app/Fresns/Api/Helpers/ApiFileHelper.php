<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Helpers;

use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\Http\Content\FsConfig as ContentConfig;
use App\Fresns\Api\FsCmd\FresnsCmdWords;
use App\Fresns\Api\FsCmd\FresnsCmdWordsConfig;
use App\Fresns\Api\FsDb\FresnsDialogMessages\FresnsDialogMessages;
use App\Fresns\Api\FsDb\FresnsFileAppends\FresnsFileAppends;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFiles;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use Illuminate\Support\Facades\DB;

class ApiFileHelper
{
    // Dialog File Message
    public static function getMessageFileInfo($messageId, $file_id, $uid)
    {
        $messageInfo = FresnsDialogMessages::find($messageId);
        $fileInfo = FresnsFiles::find($file_id);
        $fileAppend = FresnsFileAppends::findAppend('file_id', $file_id);

        $fileArr['messageId'] = $messageInfo['id'];
        $fileArr['isMe'] = $messageInfo['send_user_id'] == $uid ? true : false;
        $fileArr['type'] = 2;
        $file = [];
        if ($fileInfo) {
            $file['fileId'] = $file_id;
            $file['type'] = $fileInfo['file_type'];
            $file['name'] = $fileInfo['file_name'];
            $file['extension'] = $fileInfo['file_extension'];
            $file['mime'] = $fileAppend['file_mime'] ?? null;
            $file['size'] = $fileAppend['file_extension'] ?? null;
            // Image Config
            $imagesHost = ApiConfigHelper::getConfigByItemKey('image_bucket_domain');
            $imagesRatio = ApiConfigHelper::getConfigByItemKey('image_thumb_ratio');
            $imagesSquare = ApiConfigHelper::getConfigByItemKey('image_thumb_square');
            $imagesBig = ApiConfigHelper::getConfigByItemKey('image_thumb_big');
            // Image Type
            if ($fileInfo['file_type'] == 1) {
                $file['imageWidth'] = $fileAppend['image_width'] ?? null;
                $file['imageHeight'] = $fileAppend['image_height'] ?? null;
                $file['imageLong'] = $fileAppend['image_is_long'] ?? 0;
                $file['imageThumbUrl'] = $imagesHost.$fileInfo['file_path'].$imagesRatio;
                $file['imageSquareUrl'] = $imagesRatio.$fileInfo['file_path'].$imagesSquare;
                $file['imageBigUrl'] = $imagesSquare.$fileInfo['file_path'].$imagesBig;
            }
            // Video Type
            $videosHost = ApiConfigHelper::getConfigByItemKey('video_bucket_domain');
            if ($fileInfo['file_type'] == 2) {
                $file['videoTime'] = $fileInfo['video_time'];
                $file['videoCover'] = $fileInfo['video_cover'];
                $file['videoGif'] = $fileInfo['video_gif'];
                $file['videoUrl'] = $videosHost.$fileInfo['file_path'];
                $file['transcodingState'] = $fileAppend['transcoding_state'] ?? 1;
            }
            // Audio Type
            $audiosHost = ApiConfigHelper::getConfigByItemKey('audio_bucket_domain');
            if ($fileInfo['file_type'] == 3) {
                $file['audioTime'] = $fileInfo['audio_time'];
                $file['audioUrl'] = $audiosHost.$fileInfo['file_path'];
                $file['transcodingState'] = $fileAppend['transcoding_state'] ?? 1;
            }
            // Doc Type
            $docsHost = ApiConfigHelper::getConfigByItemKey('document_bucket_domain');
            if ($fileInfo['file_type'] == 4) {
                $file['docUrl'] = $docsHost.$fileInfo['file_path'];
            }

            $file['moreJson'] = [];
        }
        $fileArr['file'] = $file;
        $userInfo = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $messageInfo['send_user_id'])->first();
        $sendDeactivate = true;
        $sendUid = $messageInfo['send_user_id'];
        if (($userInfo->deleted_at != null)) {
            $sendUid = null;
            $sendDeactivate = false;
        }
        $sendUserInfo = FresnsUsers::find($sendUid);
        $fileArr['sendDeactivate'] = $sendDeactivate;
        $fileArr['sendUid'] = $sendUserInfo['uid'] ?? null;
        $fileArr['sendAvatar'] = $userInfo->avatar_file_url;

        // Default Avatar
        if (empty($userInfo->avatar_file_url)) {
            $defaultIcon = ApiConfigHelper::getConfigByItemKey(ContentConfig::DEFAULT_AVATAR);
            $fileArr['sendAvatar'] = $defaultIcon;
        }
        // Deactivate Avatar
        if ($userInfo) {
            if ($userInfo->deleted_at != null) {
                $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(ContentConfig::DEACTIVATE_AVATAR);
                $fileArr['sendAvatar'] = $deactivateAvatar;
            }
        } else {
            $deactivateAvatar = ApiConfigHelper::getConfigByItemKey(ContentConfig::DEACTIVATE_AVATAR);
            $fileArr['sendAvatar'] = $deactivateAvatar;
        }
        $fileArr['sendTime'] = $messageInfo['created_at'];

        return $fileArr;
    }

    // Anti Hotlinking (images)
    public static function antiTheftFile($fileInfo)
    {
        if ($fileInfo) {
            $files = [];
            foreach ($fileInfo as $f) {
                $imagesHost = ApiConfigHelper::getConfigByItemKey('image_bucket_domain');
                $imagesRatio = ApiConfigHelper::getConfigByItemKey('image_thumb_ratio');
                $imagesSquare = ApiConfigHelper::getConfigByItemKey('image_thumb_square');
                $imagesBig = ApiConfigHelper::getConfigByItemKey('image_thumb_big');
                $file = [];
                $file['imageRatioUrl'] = $imagesHost.$f['file_path'].$imagesRatio;
                $file['imageSquareUrl'] = $imagesHost.$f['file_path'].$imagesSquare;
                $file['imageBigUrl'] = $imagesHost.$f['file_path'].$imagesBig;
                $file['imageRatioUrl'] = self::getImageSignUrl($file['imageRatioUrl']);
                $file['imageSquareUrl'] = self::getImageSignUrl($file['imageSquareUrl']);
                $file['imageBigUrl'] = self::getImageSignUrl($file['imageBigUrl']);
                $files[] = $file;
            }
        }

        return $files;
    }

    // Get single image link by fid
    public static function getImageSignUrl($url)
    {
        // determine whether it is id, if it is id then go to the database query, if not id then return directly
        if (! is_numeric($url)) {
            $singUrl = $url;
        } else {
            $fid = FresnsFiles::where('id', $url)->value('fid');
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
            $input['fid'] = $fid;
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                return $url;
            }
            $singUrl = $resp['output']['imageConfigUrl'];
        }

        return $singUrl;
    }

    // Get avatar image link by fid
    public static function getImageAvatarUrl($url)
    {
        if (! is_numeric($url)) {
            $avatarUrl = $url;
        } else {
            $fid = FresnsFiles::where('id', $url)->value('fid');
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
            $input['fid'] = $fid;
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                return $url;
            }
            $avatarUrl = $resp['output']['imageAvatarUrl'];
        }

        return $avatarUrl;
    }

    // Get image link by fid
    public static function getImageSignUrlByFileId($fileId)
    {
        $file = FresnsFiles::where('id', $fileId)->first();
        $fid = $file['fid'];
        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
        $input['fid'] = $fid;
        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            $domain = ApiConfigHelper::getConfigByItemKey('image_bucket_domain');

            return $domain.$file['file_path'];
        }
        $singUrl = $resp['output']['imageConfigUrl'];

        return $singUrl;
    }

    /**
     * Anti Hotlinking
     * https://fresns.org/extensions/anti-hotlinking.html.
     */
    public static function getImageSignUrlByFileIdUrl($fileId, $fileUrl)
    {
        // Determine whether to open the anti-hotlinking chain
        $imageStatus = ApiConfigHelper::getConfigByItemKey('image_url_status');
        if ($imageStatus == true) {
            if (empty($fileId)) {
                return $fileUrl;
            }
            $fid = FresnsFiles::where('id', $fileId)->value('fid');
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
            $input['fid'] = $fid;
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                return false;
            }

            return $resp['output']['imageConfigUrl'];
        } else {
            return $fileUrl;
        }
    }

    // imageAvatarUrl
    public static function getImageAvatarUrlByFileIdUrl($fileId, $fileUrl)
    {
        // Determine whether to open the anti-hotlinking chain
        $imageStatus = ApiConfigHelper::getConfigByItemKey('image_url_status');
        if ($imageStatus == true) {
            if (empty($fileId)) {
                return $fileUrl;
            }
            $fid = FresnsFiles::where('id', $fileId)->value('fid');
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
            $input['fid'] = $fid;
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                return false;
            }

            return $resp['output']['imageAvatarUrl'];
        } else {
            return $fileUrl;
        }
    }

    // Anti Hotlinking (Get the url of the file in the more_json field)
    public static function getMoreJsonSignUrl($moreJson)
    {
        if ($moreJson) {
            foreach ($moreJson as &$m) {
                $m['moreJson'] = empty($m['moreJson']) ? [] : $m['moreJson'];
                if ($m['fid']) {
                    // Image
                    if (isset($m['imageRatioUrl'])) {
                        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
                        $input['fid'] = $m['fid'];
                        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                        if (CmdRpcHelper::isErrorCmdResp($resp)) {
                            return false;
                        }
                        $m['imageRatioUrl'] = $resp['output']['imageRatioUrl'];
                        $m['imageSquareUrl'] = $resp['output']['imageSquareUrl'];
                        $m['imageBigUrl'] = $resp['output']['imageBigUrl'];
                    }
                    // Video
                    if (isset($m['videoCover'])) {
                        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_VIDEO;
                        $input['fid'] = $m['fid'];
                        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                        if (CmdRpcHelper::isErrorCmdResp($resp)) {
                            return false;
                        }
                        $m['videoCover'] = $resp['output']['videoCover'];
                        $m['videoGif'] = $resp['output']['videoGif'];
                        $m['videoUrl'] = $resp['output']['videoUrl'];
                    }
                    // Audio
                    if (isset($m['audioUrl'])) {
                        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_AUDIO;
                        $input['fid'] = $m['fid'];
                        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                        if (CmdRpcHelper::isErrorCmdResp($resp)) {
                            return false;
                        }
                        $m['audioUrl'] = $resp['output']['audioUrl'];
                    }
                    // Doc
                    if (isset($m['docUrl'])) {
                        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_DOC;
                        $input['fid'] = $m['fid'];
                        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                        if (CmdRpcHelper::isErrorCmdResp($resp)) {
                            return false;
                        }
                        $m['docUrl'] = $resp['output']['docUrl'];
                    }
                }
            }
        }

        return $moreJson;
    }

    // get Icons Anti Hotlinking
    public static function getIconsSignUrl($icons)
    {
        if ($icons) {
            foreach ($icons as &$i) {
                if (isset($i['fileId'])) {
                    if (! empty($i['fileId'])) {
                        $cmd = FresnsCmdWordsConfig::FRESNS_CMD_ANTI_LINK_IMAGE;
                        $fid = FresnsFiles::where('id', $i['fileId'])->value('fid');
                        $input['fid'] = $fid;
                        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                        if (CmdRpcHelper::isErrorCmdResp($resp)) {
                            return false;
                        }
                        $i['fileUrl'] = $resp['output']['imageConfigUrl'];
                    }
                }
            }
        }

        return $icons;
    }
}
