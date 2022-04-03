<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Helpers;

use App\Fresns\Api\FsDb\FresnsDialogMessages\FresnsDialogMessages;
use App\Fresns\Api\FsDb\FresnsFileAppends\FresnsFileAppends;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFiles;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\Http\Content\FsConfig as ContentConfig;
use App\Helpers\ConfigHelper;
use App\Helpers\DateHelper;
use App\Helpers\FileHelper;
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
            // Document Type
            $documentsHost = ApiConfigHelper::getConfigByItemKey('document_bucket_domain');
            if ($fileInfo['file_type'] == 4) {
                $file['documentUrl'] = $documentsHost.$fileInfo['file_path'];
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
        $fileArr['sendTime'] = DateHelper::fresnsFormatDateTime($messageInfo['created_at']);

        return $fileArr;
    }

    // Anti Hotlinking (images)
    public static function antiTheftFile($fileInfo)
    {
        if ($fileInfo) {
            $files = [];
            foreach ($fileInfo as $file) {
                $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink([
                    'fid' =>  $file->fid,
                ]);

                if ($fresnsResp->isErrorResponse()) {
                    return [];
                }

                $files[] = $fresnsResp->getData();
            }
        }

        return $files;
    }

    public static function getUserAvatar(int $uid)
    {
        $user = FresnsUsers::where('uid', $uid)->first(['avatar_file_id', 'avatar_file_url', 'deleted_at']);
        $defaultAvatar = ConfigHelper::fresnsConfigByItemKey('default_avatar');
        $deactivateAvatar = ConfigHelper::fresnsConfigByItemKey('deactivate_avatar');

        if (empty($user->deleted_at)) {
            if (empty($user->avatar_file_url) && empty($user->avatar_file_id)) {
                // default avatar
                if (ConfigHelper::fresnsConfigFileValueTypeByItemKey('default_avatar') == 'URL') {
                    $userAvatar = $defaultAvatar;
                } else {
                    $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink([
                        'fileId' => $defaultAvatar,
                    ]);
                    $userAvatar = $fresnsResp->getData('imageAvatarUrl');
                }
            } else {
                // user avatar
                $userAvatar = FileHelper::fresnsFileImageUrlByColumn($user->avatar_file_id, $user->avatar_file_url, 'imageAvatarUrl');
            }
        } else {
            // user deactivate avatar
            if (ConfigHelper::fresnsConfigFileValueTypeByItemKey('deactivate_avatar') === 'URL') {
                $userAvatar = $deactivateAvatar;
            } else {
                $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink([
                    'fileId' => $deactivateAvatar,
                ]);
                $userAvatar = $fresnsResp->getData('imageAvatarUrl');
            }
        }

        return $userAvatar;
    }

    // Get image link by fid
    public static function getImageSignUrlByFileId($fileId)
    {
        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink([
            'fileId' =>  $fileId,
        ]);

        if ($fresnsResp->isErrorResponse()) {
            return $singUrl = null;
        }

        $singUrl = $fresnsResp->getData('imageConfigUrl');

        return $singUrl;
    }

    /**
     * Anti Hotlinking
     * https://fresns.org/extensions/anti-hotlinking.html.
     */
    public static function getImageSignUrlByFileIdUrl($fileId, $fileUrl)
    {
        $fileUrl = FileHelper::fresnsFileImageUrlByColumn($fileId, $fileUrl, 'imageConfigUrl');

        return $fileUrl;
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
                        $input['fid'] = $m['fid'];
                        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink($input);
                        if ($fresnsResp->isErrorResponse()) {
                            // When an error is reported, the full amount of parameters is output
                            // code + message + data
                            return $fresnsResp->errorResponse();
                        }
                        $fresnsResp = $fresnsResp->getData();
                        $m['imageRatioUrl'] = $fresnsResp['imageRatioUrl'] ?? '';
                        $m['imageSquareUrl'] = $fresnsResp['imageSquareUrl'] ?? '';
                        $m['imageBigUrl'] = $fresnsResp['imageBigUrl'] ?? '';
                    }
                    // Video
                    if (isset($m['videoCover'])) {
                        $input['fid'] = $m['fid'];
                        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink($input);
                        if ($fresnsResp->isErrorResponse()) {
                            // When an error is reported, the full amount of parameters is output
                            // code + message + data
                            return $fresnsResp->errorResponse();
                        }
                        $fresnsResp = $fresnsResp->getData();
                        $m['videoCover'] = $fresnsResp['videoCover'] ?? '';
                        $m['videoGif'] = $fresnsResp['videoGif'] ?? '';
                        $m['videoUrl'] = $fresnsResp['videoUrl'] ?? '';
                    }
                    // Audio
                    if (isset($m['audioUrl'])) {
                        $input['fid'] = $m['fid'];
                        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink($input);
                        if ($fresnsResp->isErrorResponse()) {
                            // When an error is reported, the full amount of parameters is output
                            // code + message + data
                            return $fresnsResp->errorResponse();
                        }
                        $fresnsResp = $fresnsResp->getData();
                        $m['audioUrl'] = $fresnsResp['audioUrl'] ?? '';
                    }
                    // Document
                    if (isset($m['documentUrl'])) {
                        $input['fid'] = $m['fid'];
                        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink($input);
                        if ($fresnsResp->isErrorResponse()) {
                            // When an error is reported, the full amount of parameters is output
                            // code + message + data
                            return $fresnsResp->errorResponse();
                        }
                        $fresnsResp = $fresnsResp->getData();
                        $m['documentUrl'] = $fresnsResp['documentUrl'] ?? '';
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
                        $fid = FresnsFiles::where('id', $i['fileId'])->value('fid');
                        $input['fid'] = $fid;
                        $fresnsResp = \FresnsCmdWord::plugin('Fresns')->getFileUrlOfAntiLink($input);
                        if ($fresnsResp->isErrorResponse()) {
                            return $fresnsResp->errorResponse();
                        }
                        $fresnsResp = $fresnsResp->getData();
                        $i['fileUrl'] = $fresnsResp['imageConfigUrl'] ?? '';
                    }
                }
            }
        }

        return $icons;
    }
}
