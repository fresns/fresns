<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Helpers;

use App\Http\Center\Helper\CmdRpcHelper;
use App\Http\FresnsApi\Content\FsConfig as ContentConfig;
use App\Http\FresnsCmd\FresnsCmdWords;
use App\Http\FresnsCmd\FresnsCmdWordsConfig;
use App\Http\FresnsDb\FresnsDialogMessages\FresnsDialogMessages;
use App\Http\FresnsDb\FresnsFileAppends\FresnsFileAppends;
use App\Http\FresnsDb\FresnsFiles\FresnsFiles;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMembers\FresnsMembersConfig;
use App\Http\FresnsDb\FresnsPlugins\FresnsPlugins;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsages;
use Illuminate\Support\Facades\DB;

class ApiFileHelper
{
    // Dialog File Message
    public static function getFileInfo($messageId, $file_id, $mid)
    {
        $messageInfo = FresnsDialogMessages::find($messageId);
        $fileInfo = FresnsFiles::find($file_id);
        $fileAppend = FresnsFileAppends::findAppend('file_id', $file_id);

        $fileArr['messageId'] = $messageInfo['id'];
        $fileArr['isMe'] = $messageInfo['send_member_id'] == $mid ? true : false;
        $fileArr['type'] = 2;
        $file = [];
        if ($fileInfo) {
            $file['fileId'] = $file_id;
            $file['fileType'] = $fileInfo['file_type'];
            $file['fileName'] = $fileInfo['file_name'];
            $file['fileExtension'] = $fileInfo['file_extension'];
            $file['fileSize'] = $fileAppend['file_extension'] ?? '';

            // Image Type
            $file['imageWidth'] = $fileAppend['image_width'] ?? '';
            $file['imageHeight'] = $fileAppend['image_height'] ?? '';
            $file['imageThumbUrl'] = '';
            $file['imageSquareUrl'] = '';
            $file['imageBigUrl'] = '';
            // Image Config
            $imagesHost = ApiConfigHelper::getConfigByItemKey('images_bucket_domain');
            $imagesRatio = ApiConfigHelper::getConfigByItemKey('images_thumb_ratio');
            $imagesSquare = ApiConfigHelper::getConfigByItemKey('images_thumb_square');
            $imagesBig = ApiConfigHelper::getConfigByItemKey('images_thumb_big');
            // Image Properties
            if ($fileInfo['file_type'] == 1) {
                $file['imageLong'] = $fileAppend['image_is_long'] ?? '';
                $file['imageThumbUrl'] = $imagesHost.$fileInfo['file_path'].$imagesRatio;
                $file['imageSquareUrl'] = $imagesRatio.$fileInfo['file_path'].$imagesSquare;
                $file['imageBigUrl'] = $imagesSquare.$fileInfo['file_path'].$imagesBig;
            }
            
            // Video Type
            $video_setting = ApiConfigHelper::getConfigByItemTag(FsConfig::VIDEO_SETTING);
            // Video Properties
            if ($fileInfo['file_type'] == 2) {
                $file['videoTime'] = $fileInfo['video_time'];
                $file['videoCover'] = $fileInfo['video_cover'];
                $file['videoGif'] = $fileInfo['video_gif'];
                $file['videoUrl'] = $video_setting['videos_bucket_domain'].$fileInfo['file_path'];
            }

            // Audio Type
            $audio_setting = ApiConfigHelper::getConfigByItemTag(FsConfig::AUDIO_SETTING);
            // Audio Properties
            if ($fileInfo['file_type'] == 3) {
                $file['audioTime'] = $fileInfo['audio_time'];
                $file['audioUrl'] = $audio_setting['audios_bucket_domain'].$fileInfo['file_path'];
            }

            // Doc Type
            $doc_setting = ApiConfigHelper::getConfigByItemTag(FsConfig::DOC_SETTING);
            // Doc Properties
            if ($fileInfo['file_type'] == 4) {
                $file['docPreviewUrl'] = $doc_setting['docs_online_preview'].$doc_setting['docs_bucket_domain'].$fileInfo['file_path'];
                $file['docUrl'] = $doc_setting['docs_bucket_domain'].$fileInfo['file_path'];
            }

            $file['moreJson'] = [];
        }
        $fileArr['file'] = $file;
        $memberInfo = DB::table(FresnsMembersConfig::CFG_TABLE)->where('id', $messageInfo['send_member_id'])->first();
        $sendDeactivate = true;
        $sendMid = $messageInfo['send_member_id'];
        if (($memberInfo->deleted_at != null)) {
            $sendMid = '';
            $sendDeactivate = false;
        }
        $sendMemberInfo = FresnsMembers::find($sendMid);
        $fileArr['sendDeactivate'] = $sendDeactivate;
        $fileArr['sendMid'] = $sendMemberInfo['uuid'] ?? '';
        $fileArr['sendAvatar'] = $memberInfo->avatar_file_url;

        // Default avatar when members have no avatar
        if (empty($memberInfo->avatar_file_url)) {
            $defaultIcon = ApiConfigHelper::getConfigByItemKey(ContentConfig::DEFAULT_AVATAR);
            $fileArr['sendAvatar'] = $defaultIcon;
        }
        // The avatar displayed when a member has been deleted
        if ($memberInfo) {
            if ($memberInfo->deleted_at != null) {
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

    // File Data Table Info
    public static function getFileInfoByTable($table, $table_id)
    {
        $fileIdArr = FresnsFiles::where('table_name', $table)->where('table_id', $table_id)->get()->toArray();
        $result = [];
        if ($fileIdArr) {
            $file = [];
            foreach ($fileIdArr as $v) {
                $fileAppend = FresnsFileAppends::findAppend('file_id', $v['id']);
                $file['fid'] = $v['uuid'];
                $file['type'] = $v['file_type'];
                $file['name'] = $v['file_name'];
                $file['extension'] = $v['file_extension'];
                $file['fileSize'] = $fileAppend['file_extension'] ?? '';

                // Image Type
                $file['imageWidth'] = $fileAppend['image_width'] ?? '';
                $file['imageHeight'] = $fileAppend['image_height'] ?? '';
                $image_setting = ApiConfigHelper::getConfigByItemTag(FsConfig::IMAGE_SETTING);
                if ($v['file_type'] == 1) {
                    $file['imageLong'] = $fileAppend['image_is_long'] ?? '';
                    $file['imageThumbUrl'] = $image_setting['images_bucket_domain'].$v['file_path'].$image_setting['images_thumb_ratio'];
                    $file['imageSquareUrl'] = $image_setting['images_bucket_domain'].$v['file_path'].$image_setting['images_thumb_square'];
                    $file['imageBigUrl'] = $image_setting['images_bucket_domain'].$v['file_path'].$image_setting['images_thumb_big'];
                }

                // Video Type
                $video_setting = ApiConfigHelper::getConfigByItemTag(FsConfig::VIDEO_SETTING);
                if ($v['file_type'] == 2) {
                    $file['videoTime'] = $fileAppend['video_time'];
                    $file['videoCover'] = $fileAppend['video_cover'];
                    $file['videoGif'] = $fileAppend['video_gif'];
                    $file['videoUrl'] = $video_setting['videos_bucket_domain'].$v['file_path'];
                }

                // Audio Type
                $audio_setting = ApiConfigHelper::getConfigByItemTag(FsConfig::AUDIO_SETTING);
                if ($v['file_type'] == 3) {
                    $file['audioTime'] = $fileAppend['audio_time'];
                    $file['audioUrl'] = $audio_setting['audios_bucket_domain'].$v['file_path'];
                }

                // Doc Type
                $doc_setting = ApiConfigHelper::getConfigByItemTag(FsConfig::DOC_SETTING);
                if ($v['file_type'] == 4) {
                    $file['docPreviewUrl'] = $doc_setting['docs_online_preview'].$doc_setting['docs_bucket_domain'].$v['file_path'];
                    $file['docUrl'] = $doc_setting['docs_bucket_domain'].$v['file_path'];
                }

                $file['more_json'] = [];
                $result[] = $file;
            }
        }

        return $result;
    }

    // Anti Hotlinking (images)
    public static function antiTheftFile($fileInfo)
    {
        if ($fileInfo) {
            $files = [];
            foreach ($fileInfo as $f) {
                $imagesHost = ApiConfigHelper::getConfigByItemKey('images_bucket_domain');
                $imagesRatio = ApiConfigHelper::getConfigByItemKey('images_thumb_ratio');
                $imagesSquare = ApiConfigHelper::getConfigByItemKey('images_thumb_square');
                $imagesBig = ApiConfigHelper::getConfigByItemKey('images_thumb_big');
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
            $uuid = FresnsFiles::where('id', $url)->value('uuid');
            $cmd = FresnsCmdWordsConfig::PLG_CMD_ANTI_LINK_IMAGE;
            $input['fid'] = $uuid;
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                return false;
            }
            $singUrl = $resp['output']['imageDefaultUrl'];
        }

        return $singUrl;
    }

    // Get image link by fid
    public static function getImageSignUrlByFileId($fileId)
    {
        $uuid = FresnsFiles::where('id', $fileId)->value('uuid');
        $cmd = FresnsCmdWordsConfig::PLG_CMD_ANTI_LINK_IMAGE;
        $input['fid'] = $uuid;
        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
        if (CmdRpcHelper::isErrorCmdResp($resp)) {
            return false;
        }
        $singUrl = $resp['output']['imageDefaultUrl'];

        return $singUrl;
    }

    /**
     * Anti Hotlinking
     * https://fresns.org/extensions/anti-hotlinking.html
     */
    public static function getImageSignUrlByFileIdUrl($fileId, $fileUrl)
    {
        // Determine whether to open the anti-hotlinking chain
        $imageStatus = ApiConfigHelper::getConfigByItemKey('images_url_status');
        if ($imageStatus == true) {
            if (empty($fileId)) {
                return $fileUrl;
            }
            $uuid = FresnsFiles::where('id', $fileId)->value('uuid');
            $cmd = FresnsCmdWordsConfig::PLG_CMD_ANTI_LINK_IMAGE;
            $input['fid'] = $uuid;
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                return false;
            }

            return $resp['output']['imageDefaultUrl'];
        } else {
            return $fileUrl;
        }
    }

    // Handling Custom Parameters for Plugin Usages
    public static function getPluginUsagesUrl($pluginUnikey, $pluginUsagesid)
    {
        $bucketDomain = ApiConfigHelper::getConfigByItemKey(FsConfig::BACKEND_DOMAIN);
        $pluginUsages = FresnsPluginUsages::find($pluginUsagesid);
        $plugin = FresnsPlugins::where('unikey', $pluginUnikey)->first();
        $url = '';
        if (! $plugin || ! $pluginUsages) {
            return $url;
        }
        $access_path = $plugin['access_path'];
        $str = strstr($access_path, '{parameter}');
        if ($str) {
            $uri = str_replace('{parameter}', $pluginUsages['parameter'], $access_path);
        } else {
            $uri = $access_path;
        }
        if (empty($plugin['plugin_url'])) {
            $url = $bucketDomain.$uri;
        } else {
            $url = $plugin['plugin_domain'].$uri;
        }
        $url = self::getImageSignUrl($url);

        return $url;
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
                        $cmd = FresnsCmdWordsConfig::PLG_CMD_ANTI_LINK_IMAGE;
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
                        $cmd = FresnsCmdWordsConfig::PLG_CMD_ANTI_LINK_VIDEO;
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
                        $cmd = FresnsCmdWordsConfig::PLG_CMD_ANTI_LINK_AUDIO;
                        $input['fid'] = $m['fid'];
                        $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
                        if (CmdRpcHelper::isErrorCmdResp($resp)) {
                            return false;
                        }
                        $m['audioUrl'] = $resp['output']['audioUrl'];
                    }
                    // Doc
                    if (isset($m['docUrl'])) {
                        $cmd = FresnsCmdWordsConfig::PLG_CMD_ANTI_LINK_DOC;
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
}
