<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Editor;

use App\Helpers\StrHelper;
use App\Http\Center\Common\GlobalService;
use App\Http\Center\Common\LogService;
use App\Http\Center\Helper\CmdRpcHelper;
use App\Http\Center\Helper\PluginHelper;
use App\Http\Center\Scene\FileSceneService;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsCmd\FresnsCmdWordsConfig;
use App\Http\FresnsDb\FresnsCommentAppends\FresnsCommentAppends;
use App\Http\FresnsDb\FresnsCommentLogs\FresnsCommentLogs;
use App\Http\FresnsDb\FresnsComments\FresnsComments;
use App\Http\FresnsDb\FresnsExtendLinkeds\FresnsExtendLinkedsConfig;
use App\Http\FresnsDb\FresnsExtends\FresnsExtends;
use App\Http\FresnsDb\FresnsFileAppends\FresnsFileAppends;
use App\Http\FresnsDb\FresnsFiles\FresnsFiles;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRoles;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsPostAllows\FresnsPostAllowsConfig;
use App\Http\FresnsDb\FresnsPostAppends\FresnsPostAppends;
use App\Http\FresnsDb\FresnsPostLogs\FresnsPostLogs;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsConfig;
use App\Http\FresnsDb\FresnsStopWords\FresnsStopWords;
use Illuminate\Support\Facades\DB;

class ContentLogsService
{
    // Get the existing content of the post to create a draft.
    public static function postLogInsert($uuid, $mid)
    {
        $postInfo = FresnsPosts::where('uuid', $uuid)->first();
        $postAppend = FresnsPostAppends::findAppend('post_id', $postInfo['id']);

        // Get editor config
        $is_plugin_editor = $postAppend['is_plugin_editor'];
        $editor_unikey = $postAppend['editor_unikey'];

        // Members Settings
        $member_list_json = [];
        if ($postAppend['member_list_status'] == 1) {
            $member_list_json['memberListStatus'] = $postAppend['member_list_status'];
            $member_list_json['pluginUnikey'] = $postAppend['member_list_plugin_unikey'];
            // member_list_name Multilingual
            $memberListName = ApiLanguageHelper::getAllLanguages(FresnsPostsConfig::CFG_TABLE, 'member_list_name',
                $postInfo['id']);
            if ($memberListName) {
                $memberListName1 = [];
                foreach ($memberListName as $m) {
                    $memberNameArr = [];
                    $memberNameArr['langTag'] = $m['lang_tag'];
                    $memberNameArr['name'] = $m['lang_content'];
                    $memberListName1[] = $memberNameArr;
                }
            }
            $member_list_json['memberListName'] = $memberListName1;
        }

        // Comment Settings
        $comment_set_json = [];
        if ($postAppend['comment_btn_status'] == 1) {
            $comment_set_json['btnStatus'] = $postAppend['comment_btn_status'];
            $comment_set_json['pluginUnikey'] = $postAppend['comment_btn_plugin_unikey'];
            // btnName Multilingual
            $btnName = ApiLanguageHelper::getAllLanguages(FresnsPostsConfig::CFG_TABLE, 'comment_btn_name', $postInfo['id']);
            if ($btnName) {
                $btnName1 = [];
                foreach ($btnName as $f) {
                    $btnNameArr = [];
                    $btnNameArr['langTag'] = $f['lang_tag'];
                    $btnNameArr['name'] = $f['lang_content'];
                    $btnName1[] = $btnNameArr;
                }
            }
            $comment_set_json['btnName'] = $btnName1;
        }

        // Read Permission Settings
        $allow_json = [];
        if ($postInfo['is_allow'] == 1) {
            $allow_json['isAllow'] = $postInfo['is_allow'];
            $allow_json['pluginUnikey'] = $postAppend['allow_plugin_unikey'];
            // btnName Multilingual
            $btnName = ApiLanguageHelper::getAllLanguages(FresnsPostsConfig::CFG_TABLE, 'allow_btn_name', $postInfo['id']);
            if ($btnName) {
                $btnName1 = [];
                foreach ($btnName as $f) {
                    $btnNameArr = [];
                    $editStatus = [];
                    $btnNameArr['langTag'] = $f['lang_tag'];
                    $btnNameArr['name'] = $f['lang_content'];
                    $btnName1[] = $btnNameArr;
                }
            }
            $allow_json['btnName'] = $btnName1;
            $allow_json['proportion'] = $postAppend['allow_proportion'];
            $allow_json['permission'] = [];
            $allowMemberInfo = DB::table(FresnsPostAllowsConfig::CFG_TABLE)->where('type', 1)->where('post_id', $postInfo['id'])->pluck('object_id')->toArray();
            $result = [];
            if ($allowMemberInfo) {
                $memberInfo = FresnsMembers::whereIn('id', $allowMemberInfo)->get();
                foreach ($memberInfo as $m) {
                    $arr = [];
                    $arr['mid'] = $m['uuid'];
                    $arr['membername'] = $m['name'];
                    $arr['nickname'] = $m['nickname'];
                    $result[] = $arr;
                }
            }
            $allow_json['permission']['members'] = $result;

            // member_role_rels
            $roleRels = DB::table(FresnsPostAllowsConfig::CFG_TABLE)->where('type', 2)->where('post_id', $postInfo['id'])->pluck('object_id')->toArray();
            // member_roles
            $result = [];
            if ($roleRels) {
                $memberRole = FresnsMemberRoles::whereIn('id', $roleRels)->get();
                foreach ($memberRole as $m) {
                    $arr = [];
                    $arr['rid'] = $m['id'];
                    $arr['name'] = $m['name'];
                    $result[] = $arr;
                }
            }
            $allow_json['permission']['roles'] = $result;
        }

        // Location Settings
        $location_json = [];
        $location_json['isLbs'] = $postInfo['is_lbs'] ?? 0;
        $location_json['mapId'] = $postInfo['map_id'] ?? null;
        $location_json['latitude'] = $postInfo['map_latitude'] ?? null;
        $location_json['longitude'] = $postInfo['map_longitude'] ?? null;
        $location_json['scale'] = $postAppend['map_scale'] ?? null;
        $location_json['poi'] = $postAppend['map_poi'] ?? null;
        $location_json['poiId'] = $postAppend['map_poi_id'] ?? null;
        $location_json['nation'] = $postAppend['map_nation'] ?? null;
        $location_json['province'] = $postAppend['map_province'] ?? null;
        $location_json['city'] = $postAppend['map_city'] ?? null;
        $location_json['district'] = $postAppend['map_district'] ?? null;
        $location_json['adcode'] = $postAppend['map_adcode'] ?? null;
        $location_json['address'] = $postAppend['map_address'] ?? null;

        // Files Settings
        $more_json = json_decode($postInfo['more_json'], true);
        $files = null;
        if (isset($more_json['files'])) {
            $files = $more_json['files'];
        }

        // Extends Settings
        $extends_json = [];
        $result = [];
        $extendLink = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('linked_type', 1)->where('linked_id', $postInfo['id'])->get()->toArray();
        if ($extendLink) {
            $arr = [];
            foreach ($extendLink as $e) {
                $extend = FresnsExtends::where('id', $e->extend_id)->first();
                if ($extend) {
                    $arr['eid'] = $extend['uuid'];
                    $arr['canDelete'] = $extend['post_id'] ? 'false' : 'true';
                    $arr['rankNum'] = $e->rank_num ?? 9;
                }
                $result[] = $arr;
            }
        }

        $extends_json = $result;
        if (! empty($member_list_json)) {
            $member_list_json = json_encode($member_list_json);
        } else {
            $member_list_json = null;
        }
        if (! empty($comment_set_json)) {
            $comment_set_json = json_encode($comment_set_json);
        } else {
            $comment_set_json = null;
        }
        if (! empty($allow_json)) {
            $allow_json = json_encode($allow_json);
        } else {
            $allow_json = null;
        }
        if (! empty($location_json)) {
            $location_json = json_encode($location_json);
        } else {
            $location_json = null;
        }
        if (! empty($files)) {
            $files = json_encode($files);
        } else {
            $files = null;
        }
        if (! empty($extends_json)) {
            $extends_json = json_encode($extends_json);
        } else {
            $extends_json = null;
        }
        $postInput = [
            'member_id' => $mid,
            'post_id' => $postInfo['id'],
            'platform_id' => $postAppend['platform_id'],
            'group_id' => $postInfo['group_id'],
            'types' => $postInfo['types'],
            'title' => $postInfo['title'],
            'content' => $postAppend['content'],
            'is_markdown' => $postAppend['is_markdown'],
            'is_anonymous' => $postInfo['is_anonymous'],
            'is_plugin_editor' => $is_plugin_editor,
            'editor_unikey' => $editor_unikey,
            'member_list_json' => $member_list_json,
            'comment_set_json' => $comment_set_json,
            'allow_json' => $allow_json,
            'location_json' => $location_json,
            'files_json' => $files,
            'extends_json' => $extends_json,
        ];
        $FresnsPostLogsService = new FresnsPostLogs();
        $postLogId = $FresnsPostLogsService->store($postInput);

        return $postLogId;
    }

    // Get the existing content of the comment to create a draft.
    public static function commentLogInsert($uuid, $mid)
    {
        $commentInfo = FresnsComments::where('uuid', $uuid)->first();
        $commentAppend = FresnsCommentAppends::findAppend('comment_id', $commentInfo['id']);
        $postInfo = FresnsPosts::find($commentInfo['post_id']);

        // Get editor config
        $is_plugin_editor = $commentAppend['is_plugin_editor'];
        $editor_unikey = $commentAppend['editor_unikey'];

        // Location Settings
        $location_json = [];
        $location_json['isLbs'] = $commentInfo['is_lbs'] ?? 0;
        $location_json['mapId'] = $commentAppend['map_id'] ?? null;
        $location_json['latitude'] = $commentAppend['map_latitude'] ?? null;
        $location_json['longitude'] = $commentAppend['map_longitude'] ?? null;
        $location_json['scale'] = $commentAppend['map_scale'] ?? null;
        $location_json['poi'] = $commentAppend['map_poi'] ?? null;
        $location_json['poiId'] = $commentAppend['map_poi_id'] ?? null;
        $location_json['nation'] = $commentAppend['map_nation'] ?? null;
        $location_json['province'] = $commentAppend['map_province'] ?? null;
        $location_json['city'] = $commentAppend['map_city'] ?? null;
        $location_json['district'] = $commentAppend['map_district'] ?? null;
        $location_json['adcode'] = $commentAppend['map_adcode'] ?? null;
        $location_json['address'] = $commentAppend['map_address'] ?? null;

        // Files Settings
        $more_json = json_decode($commentInfo['more_json'], true);
        $files = $more_json['files'];

        // Extends Settings
        $extends_json = [];
        $result = [];
        $extendLink = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('linked_type', 2)->where('linked_id', $commentInfo['id'])->get()->toArray();
        if ($extendLink) {
            $arr = [];
            foreach ($extendLink as $e) {
                $extend = FresnsExtends::where('id', $e->extend_id)->first();
                if ($extend) {
                    $arr['eid'] = $extend['uuid'];
                    $arr['canDelete'] = $extend['post_id'] ? 'false' : 'true';
                    $arr['rankNum'] = $e->rank_num ?? 9;
                }
                $result[] = $arr;
            }
        }

        $extends_json = $result;
        if (! empty($location_json)) {
            $location_json = json_encode($location_json);
        } else {
            $location_json = null;
        }
        if (! empty($files)) {
            $files = json_encode($files);
        } else {
            $files = null;
        }
        if (! empty($extends_json)) {
            $extends_json = json_encode($extends_json);
        } else {
            $extends_json = null;
        }
        $commentInput = [
            'member_id' => $mid,
            'comment_id' => $commentInfo['id'],
            'post_id' => $commentInfo['post_id'],
            'platform_id' => $commentAppend['platform_id'],
            'types' => $commentInfo['types'],
            'content' => $commentAppend['content'],
            'is_markdown' => $commentAppend['is_markdown'],
            'is_anonymous' => $commentInfo['is_anonymous'],
            'is_plugin_editor' => $is_plugin_editor,
            'editor_unikey' => $editor_unikey,
            'location_json' => $location_json,
            'files_json' => $files,
            'extends_json' => $extends_json,
        ];
        $FresnsCommentLogsService = new FresnsCommentLogs();
        $commentLogId = $FresnsCommentLogsService->store($commentInput);

        return $commentLogId;
    }

    // Update Post Log
    public static function updatePostLog($mid)
    {
        $request = request();
        $logId = $request->input('logId');
        $input = self::convertFormRequestToInput();
        if ($input) {
            foreach ($input as $k => &$i) {
                if ($k == 'group_id') {
                    $gid = $request->input('gid');
                    $groupInfo = FresnsGroups::where('uuid', $gid)->first();
                    $i = $groupInfo['id'];
                }
                if ($k == 'extends_json') {
                    $extends_json = json_decode($request->input('extendsJson'), true);
                    $extends = [];
                    if ($extends_json) {
                        $arr = [];
                        foreach ($extends_json as $v) {
                            $arr['eid'] = $v['eid'];
                            $arr['rankNum'] = $v['rankNum'] ?? 9;
                            $arr['canDelete'] = $v['canDelete'] ?? true;
                            $extends[] = $arr;
                        }
                    }
                    $i = json_encode($extends);
                }
                if ($k == 'content') {
                    $content = $request->input('content');
                    $i = self::stopWords($content);
                }
            }
            FresnsPostLogs::where('id', $logId)->update($input);
        }

        return true;
    }

    // Update Comment Log
    public static function updateCommentLog($mid)
    {
        $request = request();
        $logId = $request->input('logId');
        $input = self::convertFormRequestToInput();
        if ($input) {
            foreach ($input as $k => &$i) {
                if ($k == 'extends_json') {
                    $extends_json = json_decode($request->input('extendsJson'), true);
                    $extends = [];
                    if ($extends_json) {
                        $arr = [];
                        foreach ($extends_json as $v) {
                            $arr['eid'] = $v['eid'];
                            $arr['rankNum'] = $v['rankNum'] ?? 9;
                            $arr['canDelete'] = $v['canDelete'] ?? true;
                            $extends[] = $arr;
                        }
                    }
                    $i = json_encode($extends);
                }
                if ($k == 'content') {
                    $content = $request->input('content');
                    $i = self::stopWords($content);
                }
            }
            FresnsCommentLogs::where('id', $logId)->update($input);
        }

        return true;
    }

    // Publish Created (Post)
    public static function publishCreatedPost($request)
    {
        $member_id = GlobalService::getGlobalKey('member_id');
        $content = $request->input('content');
        $postGid = $request->input('postGid');
        $postTitle = $request->input('postTitle');
        $isMarkdown = $request->input('isMarkdown');
        $isAnonymous = $request->input('isAnonymous', 0);
        $file = request()->file('file');
        $fileInfo = $request->input('fileInfo');
        $eid = $request->input('eid');
        $extends = [];
        $pluginTypeArr = [];
        if ($eid) {
            $eid = json_decode($eid, true);
            foreach ($eid as $e) {
                $arr = [];
                $extendsInfo = FresnsExtends::where('uuid', $e)->first();
                if ($extendsInfo) {
                    $arr['eid'] = $e;
                    $arr['canDelete'] = $extendsInfo['post_id'] ? 'false' : 'true';
                    $arr['rankNum'] = $extendsInfo['rank_num'] ?? 9;
                    $pluginTypeArr[] = $extendsInfo['plugin_unikey'];
                    $extends[] = $arr;
                }
            }
        }
        $imageType = [];
        $idArr = [];
        if ($file) {
            $idArr = self::publishUpload(1);
            $imageType = ['image'];
        }
        if ($fileInfo) {
            $idArr = self::publishUploadFileInfo($fileInfo);
            $imageType = self::getFileType($fileInfo);
        }
        $fileArr = [];

        if (! empty($idArr)) {
            $fileArr = self::getFilesByIdArr($idArr);
        }
        $typeArr = array_unique(array_merge($pluginTypeArr, $imageType));
        if (empty($typeArr)) {
            $types = 'text';
        } else {
            $types = implode(',', $typeArr);
        }
        // Query Group
        $group_id = null;
        if ($postGid) {
            $group = FresnsGroups::where('uuid', $postGid)->first();
            $group_id = $group['id'] ?? null;
        }
        $content = self::stopWords($content);
        $rtrimContent = rtrim($content);
        if (mb_strlen($rtrimContent) != mb_strlen($content)) {
            $content = $rtrimContent.' ';
        }
        $input = [
            'group_id' => $group_id,
            'platform_id' => $request->header('platform'),
            'member_id' => $member_id,
            'title' => $postTitle,
            'content' => strip_tags(ltrim($content)),
            'types' => $types,
            'is_markdown' => $isMarkdown,
            'is_anonymous' => $isAnonymous,
            'files_json' => json_encode($fileArr),
            'extends_json' => json_encode($extends),
        ];
        // Insert post_logs table
        $draftId = (new FresnsPostLogs())->store($input);
        if (! empty($idArr)) {
            FresnsFiles::whereIn('id', $idArr)->update(['table_id'=>$draftId]);
        }

        return $draftId;
    }

    // Publish Created (Comment)
    public static function publishCreatedComment($request)
    {
        $member_id = GlobalService::getGlobalKey('member_id');
        $commentPid = $request->input('commentPid');
        $commentCid = $request->input('commentCid');
        $content = $request->input('content');
        $isAnonymous = $request->input('isAnonymous', 0);
        $isMarkdown = $request->input('isMarkdown');
        $file = request()->file('file');

        $fileInfo = $request->input('fileInfo');
        $eid = $request->input('eid');
        $extends = [];
        $postInfo = FresnsPosts::where('uuid', $commentPid)->first();
        $pluginTypeArr = [];
        if ($eid) {
            $eid = json_decode($eid, true);
            foreach ($eid as $e) {
                $arr = [];
                $extendsInfo = FresnsExtends::where('uuid', $e)->first();
                if ($extendsInfo) {
                    $arr['eid'] = $e;
                    $arr['canDelete'] = $extendsInfo['post_id'] ? 'false' : 'true';
                    $arr['rankNum'] = $extendsInfo['rank_num'] ?? 9;
                    $pluginTypeArr[] = $extendsInfo['plugin_unikey'];
                    $extends[] = $arr;
                }
            }
        }
        $imageType = [];
        $idArr = [];
        if ($file) {
            $idArr = self::publishUpload(2);
            $imageType = ['image'];
        }
        if ($fileInfo) {
            $idArr = self::publishUploadFileInfo($fileInfo);
            $imageType = self::getFileType($fileInfo);
        }
        $fileArr = [];

        if ($idArr) {
            $fileArr = self::getFilesByIdArr($idArr);
        }
        $typeArr = array_unique(array_merge($pluginTypeArr, $imageType));

        if (empty($typeArr)) {
            $types = 'text';
        } else {
            $types = implode(',', $typeArr);
        }
        $content = self::stopWords($content);
        $rtrimContent = rtrim($content);
        if (mb_strlen($rtrimContent) != mb_strlen($content)) {
            $content = $rtrimContent.' ';
        }
        $input = [
            'platform_id' => $request->header('platform'),
            'member_id' => $member_id,
            'types' => $types,
            'post_id' => $postInfo['id'],
            'content' => strip_tags(ltrim($content)),
            'is_markdown' => $isMarkdown,
            'is_anonymous' => $isAnonymous,
            'files_json' => json_encode($fileArr),
            'extends_json' => json_encode($extends),
        ];
        // Insert comment_logs table
        $draftId = (new FresnsCommentLogs())->store($input);
        if (! empty($idArr)) {
            FresnsFiles::whereIn('id', $idArr)->update(['table_id'=>$draftId]);
        }

        return $draftId;
    }

    /**
     * Upload File.
     *
     * @param [type] $type 1-Post 2-Comment
     * @return void
     */
    public static function publishUpload($type)
    {
        $uid = GlobalService::getGlobalKey('user_id');
        $mid = GlobalService::getGlobalKey('member_id');
        $t1 = time();

        if ($type == 1) {
            $tableType = 8;
            $tableName = 'post_logs';
        } else {
            $tableType = 9;
            $tableName = 'comment_logs';
        }

        $unikey = ApiConfigHelper::getConfigByItemKey('images_service');

        $pluginUniKey = $unikey;
        // Perform Upload
        $pluginClass = PluginHelper::findPluginClass($pluginUniKey);

        $platformId = request()->header('platform');
        // Confirm Catalog
        $options['file_type'] = 1;
        $options['table_type'] = $tableType;
        $storePath = FileSceneService::getEditorPath($options);
        // Get an instance of UploadFile
        $uploadFile = request()->file('file');

        // Storage
        $path = $uploadFile->store($storePath);
        $basePath = base_path();
        $basePath = $basePath.'/storage/app/';
        $newPath = $storePath.'/'.StrHelper::createToken(8).'.'.$uploadFile->getClientOriginalExtension();
        copy($basePath.$path, $basePath.$newPath);
        unlink($basePath.$path);
        $file['file_name'] = $uploadFile->getClientOriginalName();
        $file['file_extension'] = $uploadFile->getClientOriginalExtension();
        $file['file_path'] = str_replace('public', '', $newPath);
        $file['rank_num'] = 9;
        $file['table_type'] = 8;
        $file['file_type'] = 1;
        $file['table_name'] = $tableName;
        $file['table_field'] = 'files_json';

        LogService::info('File Storage Local Success ', $file);
        $t2 = time();

        $uuid = StrHelper::createUuid();
        $file['uuid'] = $uuid;
        // Insert data
        $retId = FresnsFiles::insertGetId($file);

        $file['real_path'] = $newPath;
        $input = [
            'file_id' => $retId,
            'file_mime' => $uploadFile->getMimeType(),
            'file_size' => $uploadFile->getSize(),
            'platform_id' => $platformId,
            'transcoding_state' => 1,
            'user_id' => $uid,
            'member_id' => $mid,
            // 'file_original_path' => Storage::url($path),
        ];

        $imageSize = getimagesize($uploadFile);
        $input['image_width'] = $imageSize[0] ?? null;
        $input['image_height'] = $imageSize[1] ?? null;
        $input['image_is_long'] = 0;
        if (! empty($input['image_width']) && ! empty($input['image_height'])) {
            if ($input['image_width'] >= 700) {
                if ($input['image_height'] >= $input['image_width'] * 3) {
                    $input['image_is_long'] = 1;
                }
            }
        }

        $file['file_size'] = $input['file_size'];
        FresnsFileAppends::insert($input);

        if ($pluginClass) {
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_UPLOAD_FILE;
            $input = [];
            $input['fid'] = json_encode([$uuid]);
            $input['mode'] = 1;
            $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
            }
        }

        return [$retId];
    }

    // Upload fileInfo
    public static function publishUploadFileInfo($fileInfo)
    {
        $uid = GlobalService::getGlobalKey('user_id');
        $mid = GlobalService::getGlobalKey('member_id');
        $platformId = request()->header('platform');
        $fileInfo = json_decode($fileInfo, true);
        $unikey = ApiConfigHelper::getConfigByItemKey('images_service');

        $pluginUniKey = $unikey;
        // Perform Upload
        $pluginClass = PluginHelper::findPluginClass($pluginUniKey);

        $retIdArr = [];
        $uuidArr = [];
        if (is_array($fileInfo)) {
            foreach ($fileInfo as $v) {
                $item = [];
                $uuid = StrHelper::createUuid();
                $uuidArr[] = $uuid;
                $item['uuid'] = $uuid;
                $item['file_name'] = $v['name'];
                $item['file_type'] = $v['type'];
                $item['table_type'] = $v['tableType'];
                $item['table_name'] = $v['tableName'];
                $item['table_field'] = $v['tableField'];
                $item['file_extension'] = $v['extension'];
                $item['file_path'] = $v['path'];
                $item['rank_num'] = $v['rankNum'] ?? 9;
                $retId = FresnsFiles::insertGetId($item);
                $retIdArr[] = $retId;
                $append = [];
                $append['file_id'] = $retId;
                $append['user_id'] = $uid;
                $append['member_id'] = $mid;
                $append['file_id'] = $retId;
                $append['file_original_path'] = $v['originalPath'] ?? null;
                $append['file_mime'] = $v['mime'] ?? null;
                $append['file_size'] = $v['size'] ?? null;
                $append['file_md5'] = $v['md5'] ?? null;
                $append['file_sha1'] = $v['sha1'] ?? null;
                $append['image_width'] = empty($v['imageWidth']) ? null : $v['imageWidth'];
                $append['image_height'] = empty($v['imageHeight']) ? null : $v['imageHeight'];
                $imageLong = 0;
                if (! empty($fileInfo['imageLong'])) {
                    $length = strlen($fileInfo['imageLong']);
                    if ($length == 1) {
                        $imageLong = $fileInfo['imageLong'];
                    }
                }
                $append['image_is_long'] = $imageLong;
                $append['video_time'] = empty($v['videoTime']) ? null : $v['videoTime'];
                $append['video_cover'] = empty($v['videoCover']) ? null : $v['videoCover'];
                $append['video_gif'] = empty($v['videoGif']) ? null : $v['videoGif'];
                $append['audio_time'] = empty($v['audioTime']) ? null : $v['audioTime'];
                $append['transcoding_state'] = empty($v['transcodingState']) ? 1 : $v['transcodingState'];
                $append['platform_id'] = $platformId;
                FresnsFileAppends::insert($append);
            }
        }

        if ($pluginClass && $uuidArr) {
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_UPLOAD_FILE;
            $input = [];
            $input['fid'] = json_encode($uuidArr);
            $input['mode'] = 1;
            $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
            }
        }

        return $retIdArr;
    }

    /**
     * Look up the file information by the file->id array.
     */
    public static function getFilesByIdArr($idArr)
    {
        $filesArr = FresnsFiles::whereIn('id', $idArr)->get()->toArray();
        $imagesHost = ApiConfigHelper::getConfigByItemKey('images_bucket_domain');
        $imagesRatio = ApiConfigHelper::getConfigByItemKey('images_thumb_ratio');
        $imagesSquare = ApiConfigHelper::getConfigByItemKey('images_thumb_square');
        $imagesBig = ApiConfigHelper::getConfigByItemKey('images_thumb_big');
        $videosHost = ApiConfigHelper::getConfigByItemKey('videos_bucket_domain');
        $audiosHost = ApiConfigHelper::getConfigByItemKey('audios_bucket_domain');
        $docsHost = ApiConfigHelper::getConfigByItemKey('docs_bucket_domain');
        $docsOnlinePreview = ApiConfigHelper::getConfigByItemKey('docs_online_preview');
        $data = [];
        if ($filesArr) {
            foreach ($filesArr as $file) {
                $item = [];
                $append = FresnsFileAppends::where('file_id', $file['id'])->first();
                $type = $file['file_type'];
                $item['fid'] = $file['uuid'];
                $item['type'] = $file['file_type'];
                $item['name'] = $file['file_name'];
                $item['extension'] = $file['file_extension'];
                $item['mime'] = $append['file_mime'];
                $item['size'] = $append['file_size'];
                $item['rankNum'] = $file['rank_num'];
                if ($type == 1) {
                    $item['imageWidth'] = $append['image_width'] ?? '';
                    $item['imageHeight'] = $append['image_height'] ?? '';
                    $item['imageLong'] = $file['image_long'] ?? 0;
                    $item['imageRatioUrl'] = $imagesHost.$file['file_path'].$imagesRatio;
                    $item['imageSquareUrl'] = $imagesHost.$file['file_path'].$imagesSquare;
                    $item['imageBigUrl'] = $imagesHost.$file['file_path'].$imagesBig;
                }
                if ($type == 2) {
                    $item['videoTime'] = $append['video_time'] ?? '';
                    $item['videoCover'] = $append['video_cover'] ?? '';
                    $item['videoGif'] = $append['video_gif'] ?? '';
                    $item['videoUrl'] = $videosHost.$file['file_path'];
                    $item['transcodingState'] = $append['transcoding_state'];
                }
                if ($type == 3) {
                    $item['audioTime'] = $append['audio_time'] ?? '';
                    $item['audioUrl'] = $audiosHost.$file['file_path'];
                    $item['transcodingState'] = $append['transcoding_state'];
                }
                if ($type == 4) {
                    $item['docUrl'] = $docsHost.$file['file_path'];
                }
                $item['moreJson'] = json_decode($append['more_json'], true);

                $data[] = $item;
            }
        }

        return $data;
    }

    /**
     * Get type according to fileInfo.
     */
    public static function getFileType($fileInfo)
    {
        $fileInfo = json_decode($fileInfo, true);
        $res = [];
        if (is_array($fileInfo)) {
            foreach ($fileInfo as $f) {
                $arr = 'image';
                if ($f['type'] == 1) {
                    $arr = 'image';
                }
                if ($f['type'] == 2) {
                    $arr = 'video';
                }
                if ($f['type'] == 3) {
                    $arr = 'audio';
                }
                if ($f['type'] == 4) {
                    $arr = 'doc';
                }
                $res[] = $arr;
            }
        }

        return $res;
    }

    // Stop Word Rules
    public static function stopWords($text)
    {
        $stopWordsArr = FresnsStopWords::get()->toArray();

        foreach ($stopWordsArr as $v) {
            $str = strstr($text, $v['word']);
            if ($str != false) {
                if ($v['content_mode'] == 2) {
                    $text = str_replace($v['word'], $v['replace_word'], $text);

                    return $text;
                }
            }
        }

        return $text;
    }

    public static function convertFormRequestToInput()
    {
        $req = request();
        $fieldMap = FsConfig::FORM_FIELDS_UPDATE_LOGS_MAP;
        $input = [];
        foreach ($fieldMap as $inputField => $tbField) {
            if ($req->has($inputField)) {
                $srcValue = $req->input($inputField);
                if ($srcValue == 0 || $srcValue == '0') {
                    $input[$tbField] = $srcValue;
                }

                if ($srcValue === false || ! empty($req->input($inputField, ''))) {
                    $input[$tbField] = $req->input($inputField);
                }
            }
        }

        return $input;
    }
}
