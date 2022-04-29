<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsComments;

use App\Fresns\Api\Center\Common\LogService;
use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\FsCmd\FresnsSubPlugin;
use App\Fresns\Api\FsCmd\FresnsSubPluginConfig;
use App\Fresns\Api\FsDb\FresnsBlockWords\FresnsBlockWords;
use App\Fresns\Api\FsDb\FresnsCommentAppends\FresnsCommentAppends;
use App\Fresns\Api\FsDb\FresnsCommentAppends\FresnsCommentAppendsConfig;
use App\Fresns\Api\FsDb\FresnsCommentLogs\FresnsCommentLogs;
use App\Fresns\Api\FsDb\FresnsComments\FresnsComments;
use App\Fresns\Api\FsDb\FresnsDomainLinks\FresnsDomainLinks;
use App\Fresns\Api\FsDb\FresnsDomainLinks\FresnsDomainLinksConfig;
use App\Fresns\Api\FsDb\FresnsDomains\FresnsDomains;
use App\Fresns\Api\FsDb\FresnsExtendLinkeds\FresnsExtendLinkedsConfig;
use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtends;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFiles;
use App\Fresns\Api\FsDb\FresnsHashtagLinkeds\FresnsHashtagLinkeds;
use App\Fresns\Api\FsDb\FresnsHashtagLinkeds\FresnsHashtagLinkedsConfig;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtags;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPosts;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsService;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogs;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsersConfig;
use App\Fresns\Api\FsDb\FresnsUserStats\FresnsUserStats;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\StrHelper;
use App\Fresns\Api\Http\Content\FresnsPostsResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FresnsCommentsService extends FsService
{
    public function getCommentPreviewList($comment_id, $limit, $uid)
    {
        $FsService = new FsService();
        request()->offsetSet('id', $comment_id);
        $data = $FsService->listTreeNoRankNum();
        $data = $FsService->treeData();
        // get childrenIdArr
        $childrenIdArr = [];
        if ($data) {
            foreach ($data as $v) {
                $this->getChildrenIds($v, $childrenIdArr);
            }
        }
        array_unshift($childrenIdArr, $comment_id);
        request()->offsetUnset('id');
        // $query->where('comment.id','=',$comments['id']);

        $userArr = FresnsUsers::where('deleted_at', null)->pluck('id')->toArray();
        $comments = FresnsComments::whereIn('user_id', $userArr)->whereIn('id', $childrenIdArr)->where('parent_id', '!=', 0)->orderBy('like_count', 'desc')->limit($limit)->get();
        $result = [];
        if ($comments) {
            foreach ($comments as $v) {
                $userInfo = FresnsUsers::find($v['user_id']);
                $arr = [];
                $arr['anonymous'] = $v['is_anonymous'];
                $arr['isAuthor'] = null;
                $arr['uid'] = null;
                $arr['username'] = null;
                $arr['nickname'] = null;
                if ($v['is_anonymous'] == 0) {
                    $arr['isAuthor'] = $v['user_id'] == $uid ? true : false;
                    $arr['uid'] = $userInfo['uid'];
                    $arr['username'] = $userInfo['username'];
                    $arr['nickname'] = $userInfo['nickname'];
                    $arr['cid'] = $v['cid'];
                    $arr['content'] = FresnsPostsResource::getContentView($v['content'], $comment_id, 2);
                    $attachCount = [];
                    $attachCount['images'] = 0;
                    $attachCount['videos'] = 0;
                    $attachCount['audios'] = 0;
                    $attachCount['documents'] = 0;
                    $attachCount['extends'] = DB::table(FresnsExtendLinkedsConfig::CFG_TABLE)->where('linked_type', 2)->where('linked_id', $v['id'])->count();
                    $more_json_decode = json_decode($v['more_json'], true);
                    if ($more_json_decode) {
                        if (isset($more_json_decode['files'])) {
                            foreach ($more_json_decode['files'] as $m) {
                                if ($m['type'] == 1) {
                                    $attachCount['images']++;
                                }
                                if ($m['type'] == 2) {
                                    $attachCount['videos']++;
                                }
                                if ($m['type'] == 3) {
                                    $attachCount['audios']++;
                                }
                                if ($m['type'] == 4) {
                                    $attachCount['documents']++;
                                }
                            }
                        }
                    }
                    $arr['attachCount'] = $attachCount;

                    // replyTo
                    // The following information is not output if the parent_id of the reply = the ID of the current comment.
                    // The current comment ID then represents a secondary comment.
                    $replyTo = [];
                    $replyComment = FresnsComments::where('id', $v['parent_id'])->orderBy('like_count', 'desc')->first();
                    // Respondent Information
                    if (! empty($replyComment) && ($v['parent_id'] != $comment_id)) {
                        $replyUserInfo = FresnsUsers::find($replyComment['user_id']);
                        $replyTo['cid'] = $replyComment['cid'] ?? null;
                        $replyTo['anonymous'] = $replyComment['is_anonymous'] ?? null;
                        $replyTo['deactivate'] = $replyComment['deleted_at'] == null ? true : false;
                        $replyTo['uid'] = $replyUserInfo['uid'] ?? null;
                        $replyTo['username'] = $replyUserInfo['username'] ?? null;
                        $replyTo['nickname'] = $replyUserInfo['nickname'] ?? null;
                        $arr['replyTo'] = $replyTo;
                    }
                    $result[] = $arr;
                }
            }
        }

        return $result;
    }

    // get replty
    public function getReplyToPreviewList($comment_id, $uid)
    {
        $searchCid = request()->input('searchCid');
        $commentCid = FresnsComments::where('cid', $searchCid)->first();
        $FsService = new FsService();
        request()->offsetSet('id', $comment_id);
        $data = $FsService->listTreeNoRankNum();
        $data = $FsService->treeData();
        // get childrenIdArr
        $childrenIdArr = [];
        if ($data) {
            foreach ($data as $v) {
                $this->getChildrenIds($v, $childrenIdArr);
            }
        }
        array_unshift($childrenIdArr, $comment_id);
        request()->offsetUnset('id');
        $replyTo = [];
        $comments = FresnsComments::whereIn('id', $childrenIdArr)->where('parent_id', '!=', $commentCid['id'])->where('parent_id', '!=', 0)->orderBy('like_count', 'desc')->get();
        if ($comments) {
            foreach ($comments as $c) {
                $reply = [];
                $reply['deactivate'] = false;
                if ($c['parent_id'] != $comment_id) {
                    $parentCommentInfo = FresnsComments::find($c['parent_id']);
                    if ($parentCommentInfo) {
                        $parentUserInfo = DB::table(FresnsUsersConfig::CFG_TABLE)->where('id', $parentCommentInfo['user_id'])->first();
                        if (! $parentUserInfo) {
                            $reply['deactivate'] = true;
                        } else {
                            if ($parentUserInfo->deleted_at != null) {
                                $reply['deactivate'] = true;
                            }
                        }
                    }
                    $reply['cid'] = $parentCommentInfo['cid'] ?? null;
                    $reply['anonymous'] = $parentCommentInfo['is_anonymous'];
                    $reply['uid'] = null;
                    $reply['username'] = null;
                    $reply['nickname'] = null;
                    if ($parentCommentInfo['is_anonymous'] == 0) {
                        if ($parentUserInfo->deleted_at == null) {
                            $reply['deactivate'] = false;
                            $reply['uid'] = $parentUserInfo->uid ?? null;
                            $reply['username'] = $parentUserInfo->username ?? null;
                            $reply['nickname'] = $parentUserInfo->nickname ?? null;
                        } else {
                            $reply['deactivate'] = true;
                        }
                    }
                    $replyTo = $reply;
                }
            }
        }

        return $replyTo;
    }

    // Publish comment
    public function releaseByDraft($draftId, $commentCid = 0, $sessionLodsId = 0)
    {
        // Direct Publishing
        $releaseResult = $this->doRelease($draftId, $commentCid, $sessionLodsId);
        if (! $releaseResult) {
            LogService::formatInfo('Comment Publish Exception');

            return false;
        }

        return $releaseResult;
    }

    // Publish Type
    public function doRelease($draftId, $commentCid = 0, $sessionLodsId)
    {
        // Determine if it is an update or a new addition
        $draftComment = FresnsCommentLogs::find($draftId);
        if (! $draftComment) {
            LogService::formatInfo('Comment log does not exist');

            return false;
        }
        // Type
        if (! $draftComment['comment_id']) {
            // add
            $res = $this->storeToDb($draftId, $commentCid, $sessionLodsId);
        } else {
            // edit
            $res = $this->updateTob($draftId, $sessionLodsId);
        }

        return true;
    }

    // Insert main table (add)
    public function storeToDb($draftId, $commentCid = 0, $sessionLodsId = 0)
    {
        // Parsing basic information
        $draftComment = FresnsCommentLogs::find($draftId);
        $fsid = strtolower(StrHelper::randString(8));

        // Parse content information (determine whether the content needs to be truncated)
        $contentBrief = $this->parseDraftContent($draftId);
        // Removing html tags
        $contentBrief = strip_tags($contentBrief);
        // Get the number of words in the brief of the comment
        $commentEditorBriefCount = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_EDITOR_BRIEF_COUNT) ?? 280;
        if (mb_strlen($draftComment['content']) > $commentEditorBriefCount) {
            $isBrief = 1;
        } else {
            $isBrief = 0;
        }

        $locationJson = json_decode($draftComment['location_json'], true);
        $isLbs = $locationJson['isLbs'] ?? 0;

        $more_json = [];
        $more_json['files'] = json_decode($draftComment['files_json'], true);
        LogService::info('draftComment', $draftComment);
        LogService::info('more_json', $more_json);

        $postInput = [
            'cid' => $fsid,
            'user_id' => $draftComment['user_id'],
            'post_id' => $draftComment['post_id'],
            'types' => $draftComment['types'],
            'content' => $contentBrief,
            'is_brief' => $isBrief,
            'parent_id' => $commentCid,
            'is_anonymous' => $draftComment['is_anonymous'],
            'is_lbs' => $isLbs,
            'more_json' => json_encode($more_json),
        ];
        LogService::info('postInput', $postInput);

        $commentId = (new FresnsComments())->store($postInput);
        $AppendStore = $this->commentAppendStore($commentId, $draftId);
        if ($AppendStore) {
            FresnsSessionLogs::where('id', $sessionLodsId)->update([
                'object_result' => 2,
                'object_order_id' => $commentId,
            ]);
            // Perform the corresponding operation after inserting into the main table
            $this->afterStoreToDb($commentId, $draftId);
        }
    }

    // Insert main table (edit)
    public function updateTob($draftId, $sessionLodsId)
    {
        $draftComment = FresnsCommentLogs::find($draftId);
        $comment = FresnsComments::find($draftComment['comment_id']);
        FresnsSessionLogs::where('id', $sessionLodsId)->update([
            'object_result' => 2,
            'object_order_id' => $draftComment['comment_id'],
        ]);

        // Parse content information (determine whether the content needs to be truncated)
        $contentBrief = $this->parseDraftContent($draftId);
        // Removing html tags
        $contentBrief = strip_tags($contentBrief);
        // Get the number of words in the brief of the comment
        $commentEditorBriefCount = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_EDITOR_WORD_COUNT) ?? 280;
        if (mb_strlen($draftComment['content']) > $commentEditorBriefCount) {
            $isBrief = 1;
        } else {
            $isBrief = 0;
        }

        // Location Information
        $locationJson = json_decode($draftComment['location_json'], true);
        $isLbs = $locationJson['isLbs'] ?? null;

        $more_json = [];
        $more_json['files'] = json_decode($draftComment['files_json'], true);

        $commentInput = [
            'types' => $draftComment['types'],
            'content' => $contentBrief,
            'is_brief' => $isBrief,
            'is_anonymous' => $draftComment['is_anonymous'],
            'is_lbs' => $isLbs,
            'latest_edit_at' => date('Y-m-d H:i:s'),
            'more_json' => $more_json,
        ];
        FresnsComments::where('id', $draftComment['comment_id'])->update($commentInput);
        $AppendStore = $this->commentAppendUpdate($draftComment['comment_id'], $draftId);
        if ($AppendStore) {
            // Perform the corresponding operation after inserting into the main table
            $this->afterUpdateToDb($draftComment['comment_id'], $draftId);
        }
    }

    // comment_appends (add)
    public function commentAppendStore($commentId, $draftId)
    {
        $draftComment = FresnsCommentLogs::find($draftId);
        // Editor Config
        $isPluginEditor = $draftComment['is_plugin_editor'];
        $editorUnikey = $draftComment['editor_unikey'];
        // Location Config
        $locationJson = json_decode($draftComment['location_json'], true);
        $mapId = $locationJson['mapId'] ?? null;
        $latitude = $locationJson['latitude'] ?? null;
        $longitude = $locationJson['longitude'] ?? null;
        $scale = $locationJson['scale'] ?? null;
        $poi = $locationJson['poi'] ?? null;
        $poiId = $locationJson['poiId'] ?? null;
        $nation = $locationJson['nation'] ?? null;
        $province = $locationJson['province'] ?? null;
        $city = $locationJson['city'] ?? null;
        $district = $locationJson['district'] ?? null;
        $adcode = $locationJson['adcode'] ?? null;
        $address = $locationJson['address'] ?? null;
        // Extends
        $extendsJson = json_decode($draftComment['extends_json'], true);
        if ($extendsJson) {
            foreach ($extendsJson as $e) {
                $extend = FresnsExtends::where('eid', $e['eid'])->first();
                if ($extend) {
                    $input = [
                        'linked_type' => 2,
                        'linked_id' => $commentId,
                        'extend_id' => $extend['id'],
                        'plugin_unikey' => $extend['plugin_unikey'] ?? null,
                        'rank_num' => $e['rankNum'],
                    ];
                    Db::table('extend_linkeds')->insert($input);
                }
            }
        }
        $content = $draftComment['content'];
        $content = $this->blockWords($content);

        $commentAppendInput = [
            'comment_id' => $commentId,
            'platform_id' => $draftComment['platform_id'],
            'content' => $content,
            'is_markdown' => $draftComment['is_markdown'],
            'is_plugin_editor' => $isPluginEditor,
            'editor_unikey' => $editorUnikey,
            'map_id' => $mapId,
            'map_latitude' => $latitude,
            'map_longitude' => $longitude,
            'map_scale' => $scale,
            'map_poi' => $poi,
            'map_poi_id' => $poiId,
            'map_nation' => $nation,
            'map_province' => $province,
            'map_city' => $city,
            'map_district' => $district,
            'map_adcode' => $adcode,
            'map_address' => $address,
        ];
        DB::table(FresnsCommentAppendsConfig::CFG_TABLE)->insert($commentAppendInput);

        return true;
    }

    // comment_appends (edit)
    public function commentAppendUpdate($commentId, $draftId)
    {
        $draftComment = FresnsCommentLogs::find($draftId);
        // Editor Config
        $isPluginEditor = $draftComment['is_plugin_editor'];
        $editorUnikey = $draftComment['editor_unikey'];
        // Location Config
        $locationJson = json_decode($draftComment['location_json'], true);
        $mapId = $locationJson['mapId'] ?? null;
        $latitude = $locationJson['latitude'] ?? null;
        $longitude = $locationJson['longitude'] ?? null;
        $scale = $locationJson['scale'] ?? null;
        $poi = $locationJson['poi'] ?? null;
        $poiId = $locationJson['poiId'] ?? null;
        $nation = $locationJson['nation'] ?? null;
        $province = $locationJson['province'] ?? null;
        $city = $locationJson['city'] ?? null;
        $district = $locationJson['district'] ?? null;
        $adcode = $locationJson['adcode'] ?? null;
        $address = $locationJson['address'] ?? null;
        // Extends
        $extendsJson = json_decode($draftComment['extends_json'], true);
        if ($extendsJson) {
            // Empty first, then enter a new one
            Db::table('extend_linkeds')->where('linked_type', 2)->where('linked_id', $commentId)->delete();
            foreach ($extendsJson as $e) {
                $extend = FresnsExtends::where('eid', $e['eid'])->first();
                if ($extend) {
                    $input = [
                        'linked_type' => 2,
                        'linked_id' => $commentId,
                        'extend_id' => $extend['id'],
                        'plugin_unikey' => $extend['plugin_unikey'] ?? null,
                        'rank_num' => $e['rankNum'] ?? 9,
                    ];
                    Db::table('extend_linkeds')->insert($input);
                }
            }
        }
        $content = $draftComment['content'];
        $content = $this->blockWords($content);

        $commentAppendInput = [
            'platform_id' => $draftComment['platform_id'],
            'content' => $content,
            'is_plugin_editor' => $isPluginEditor,
            'editor_unikey' => $editorUnikey,
            'map_id' => $mapId,
            'map_latitude' => $latitude,
            'map_longitude' => $longitude,
            'map_scale' => $scale,
            'map_poi' => $poi,
            'map_poi_id' => $poiId,
            'map_nation' => $nation,
            'map_province' => $province,
            'map_city' => $city,
            'map_district' => $district,
            'map_adcode' => $adcode,
            'map_address' => $address,
        ];
        FresnsCommentAppends::where('comment_id', $commentId)->update($commentAppendInput);

        return true;
    }

    // Perform the corresponding operation after inserting into the main table (add)
    public function afterStoreToDb($commentId, $draftId)
    {
        // Call the plugin to subscribe to the command word
        // $cmd = FresnsSubPluginConfig::FRESNS_CMD_SUB_ADD_TABLE;
        // $input = [
        //     'tableName' => FresnsCommentsConfig::CFG_TABLE,
        //     'insertId' => $commentId,
        // ];
        // LogService::info('table_input', $input);
        // CmdRpcHelper::call(FresnsSubPlugin::class, $cmd, $input);
        $draftComment = FresnsCommentLogs::find($draftId);
        $content = $this->blockWords($draftComment['content']);

        // Log updated to published
        FresnsCommentLogs::where('id', $draftId)->update(['state' => 3, 'comment_id' => $commentId, 'content' => $content]);
        // Notification
        $this->sendAtMessages($commentId, $draftId);
        $this->sendCommentMessages($commentId, $draftId, 1);
        // Add stats: user_stats > post_publish_count
        $this->userStats($draftId);
        // Analyze the hashtag and domain
        $this->analisisHashtag($draftId, 1);
        $this->domainStore($commentId, $draftId);

        return true;
    }

    // Perform the corresponding operation after inserting into the main table (edit)
    public function afterUpdateToDb($commentId, $draftId)
    {
        // Call the plugin to subscribe to the command word
        // $cmd = FresnsSubPluginConfig::FRESNS_CMD_SUB_ADD_TABLE;
        // $input = [
        //     'tableName' => FresnsCommentsConfig::CFG_TABLE,
        //     'insertId' => $commentId,
        // ];
        // LogService::info('table_input', $input);
        // CmdRpcHelper::call(FresnsSubPlugin::class, $cmd, $input);
        $draftComment = FresnsCommentLogs::find($draftId);
        $content = $this->blockWords($draftComment['content']);

        // Log updated to published
        FresnsCommentLogs::where('id', $draftId)->update(['state' => 3, 'content'=> $content]);
        FresnsCommentAppends::where('comment_id', $commentId)->increment('edit_count');
        // Notification
        $this->sendAtMessages($commentId, $draftId, 2);
        $this->sendCommentMessages($commentId, $draftId, 1);
        // Add stats: user_stats > post_publish_count
        // Analyze the hashtag
        $this->analisisHashtag($draftId, 2);
        $this->domainStore($commentId, $draftId, 2);

        return true;
    }

    // Notifications (Call MessageService to handle)
    // Can't @ self, @ others generate a notification message to each other.
    public function sendAtMessages($commentId, $draftId, $updateType = 1)
    {
        $draftComment = FresnsCommentLogs::find($draftId);
        $commentInfo = FresnsComments::find($commentId);
        preg_match_all("/@.*?\s/", $draftComment['content'], $atMatches);
        // Presence send message
        if ($atMatches[0]) {
            foreach ($atMatches[0] as $s) {
                // Query accept user id
                $name = trim(ltrim($s, '@'));
                $userInfo = FresnsUsers::where('name', $name)->first();
                if ($userInfo && $userInfo['id'] != $draftComment['user_id']) {
                    $input = [
                        'source_id' => $commentId,
                        'source_brief' => $commentInfo['content'],
                        'user_id' => $userInfo['id'],
                        'source_user_id' => $commentInfo['user_id'],
                        'source_type' => 5,
                        'source_class' => 2,
                    ];
                    DB::table('notifies')->insert($input);
                    // @ Record table
                    $mentions = [
                        'user_id' => $commentInfo['user_id'],
                        'linked_type' => 2,
                        'linked_id' => $commentId,
                        'mention_user_id' => $userInfo['id'],
                    ];
                    $count = DB::table('mentions')->where($mentions)->count();
                    if ($count == 0) {
                        DB::table('mentions')->insert($mentions);
                    }
                }
            }
        }

        return true;
    }

    // After successful posting, the primary key ID of the comment is generated, and then the ID is filled into the files > table_id field to perfection the information.
    public function fillDbInfo($draftId)
    {
        $draftComment = FresnsCommentLogs::find($draftId);
        $fileArr = json_decode($draftComment['files_json'], true);
        if ($fileArr) {
            foreach ($fileArr as $f) {
                $fileCount = FresnsFiles::where('fid', $f['fid'])->count();
                if ($fileCount > 0) {
                    // FresnsFiles::where('fid', $f['fid'])->update(['table_id' => $draftComment['comment_id']]);
                    FresnsFiles::where('fid', $f['fid'])->update(['table_id' => $draftId]);
                }
            }
        }

        return true;
    }

    // Add stats: user_stats > comment_publish_count
    public function userStats($draftId)
    {
        $draftComment = FresnsCommentLogs::find($draftId);
        $userStats = FresnsUserStats::where('user_id', $draftComment['user_id'])->first();
        if ($userStats) {
            FresnsUserStats::where('id', $userStats['id'])->increment('comment_publish_count');
        } else {
            (new FresnsUserStats())->store(['user_id' => $draftComment['user_id'], 'comment_publish_count' => 1]);
        }

        return true;
    }

    // The comment then determines whether the parent is itself, and generates a notification for the other party if it is not itself.
    // A first-level comment generates a notification for the author of the post (the author of the post is not himself).
    // Call MessageService to process
    public function sendCommentMessages($commentId, $draftId, $type = 1)
    {
        $draftComment = FresnsCommentLogs::find($draftId);
        $postInfo = FresnsPosts::find($draftComment['post_id']);
        if ($type == 1) {
            FresnsPosts::where('id', $draftComment['post_id'])->increment('comment_count');
        }
        $comment = FresnsComments::where('id', $draftComment['comment_id'])->first();
        if ($comment && $comment['parent_id'] != 0) {
            FresnsComments::where('id', $comment['parent_id'])->increment('comment_count');
        }
        // First-level comments to post authors (post authors who are not themselves) generate notifications.
        if (($draftComment['user_id'] != $postInfo['user_id']) && $comment['parent_id'] == 0) {
            $input = [
                'source_id' => $commentId,
                'source_brief' => $draftComment['content'],
                'user_id' => $postInfo['user_id'],
                'source_user_id' => $draftComment['user_id'],
                'source_type' => 4,
                'source_class' => 1,
            ];
            DB::table('notifies')->insert($input);
        }
        // The comment determines whether the parent is itself, and if not, generates a notification for the other party
        if ($comment['parent_id'] != 0 && ($comment['parent_id'] != $draftComment['user_id'])) {
            $input = [
                'source_id' => $commentId,
                'source_brief' => $draftComment['content'],
                'user_id' => $postInfo['user_id'],
                'source_user_id' => $draftComment['user_id'],
                'source_type' => 4,
                'source_class' => 2,
            ];
            DB::table('notifies')->insert($input);
        }

        return true;
    }

    // Domain Link Table
    public function domainStore($commentId, $draftId, $updateType = 1)
    {
        $draftComment = FresnsCommentLogs::find($draftId);
        if ($updateType == 2) {
            $domainLinksIdArr = FresnsDomainLinks::where('linked_type', 1)->where('linked_id', $commentId)->pluck('domain_id')->toArray();
            FresnsDomains::where('id', $domainLinksIdArr)->decrement('post_count');
            DB::table(FresnsDomainLinksConfig::CFG_TABLE)->where('linked_type', 2)->where('linked_id', $commentId)->delete();
        }
        preg_match_all("/http[s]{0,1}:\/\/.*?\s/", $draftComment['content'], $hrefMatches);
        if ($hrefMatches[0]) {
            foreach ($hrefMatches[0] as $h) {
                // Top level domains
                $firstDomain = $this->top_domain(trim($h));
                // Second level domain name
                $domain = $this->regular_domain(trim($h));
                preg_match('/(.*\.)?\w+\.\w+$/', $domain, $secDomain);
                // Does the domain table exist
                $domain_input = [
                    'domain' => $firstDomain,
                    'sld' => $secDomain[0],
                ];
                $domainInfo = FresnsDomains::where($domain_input)->first();
                if ($domainInfo) {
                    $domainId = $domainInfo['id'];
                    FresnsDomains::where('id', $domainId)->increment('comment_count');
                } else {
                    $domainId = (new FresnsDomains())->store($domain_input);
                    FresnsDomains::where('id', $domainId)->increment('comment_count');
                }
                $input = [
                    'linked_type' => 2,
                    'linked_id' => $commentId,
                    'link_url' => trim($h),
                    'domain_id' => $domainId,
                ];
                $domainLinkCount = DB::table('domain_links')->where($input)->count();
                if ($domainLinkCount == 0) {
                    DB::table('domain_links')->insert($input);
                }
            }
        }

        return true;
    }

    // Parsing Hashtag (insert hashtags table)
    public function analisisHashtag($draftId, $type = 1)
    {
        $draftComment = FresnsCommentLogs::find($draftId);
        // The currently configured Hashtag display mode
        $hashtagShow = ApiConfigHelper::getConfigByItemKey(FsConfig::HASHTAG_SHOW) ?? 2;
        if ($hashtagShow == 1) {
            preg_match_all("/#[\S].*?\s/", $draftComment['content'], $singlePoundMatches);
        } else {
            preg_match_all('/#[\S].*?[\S]#/', $draftComment['content'], $singlePoundMatches);
        }
        if ($type == 2) {
            // Removing Hashtag Associations
            // DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_type', 2)->where('linked_id',$draftComment['comment_id'])->delete();
            $hashtagIdArr = FresnsHashtagLinkeds::where('linked_type', 2)->where('linked_id', $draftComment['comment_id'])->pluck('hashtag_id')->toArray();
            FresnsHashtags::whereIn('id', $hashtagIdArr)->decrement('comment_count');
            FresnsHashtagLinkeds::where('linked_type', 2)->where('linked_id', $draftComment['post_id'])->delete();
        }
        if ($singlePoundMatches[0]) {
            foreach ($singlePoundMatches[0] as $s) {
                // Double #: single space allowed in between (no consecutive spaces)
                if ($hashtagShow == 2) {
                    preg_match_all("/\s(?=\s)/", $s, $spaceMatchArr);
                    if (count($spaceMatchArr) > 0 && is_array($spaceMatchArr[0]) && count($spaceMatchArr[0]) > 0) {
                        continue;
                    }
                }
                // Hashtag do not support punctuation
                // English punctuation
                $topic = trim($s, '#');
                $removePunctEnglish = preg_replace('#[[:punct:]]#', '', $topic);
                $data['topic_a'] = $topic;
                if (strlen($topic) != strlen($removePunctEnglish)) {
                    continue;
                }
                // Chinese punctuation
                $removePunctChinese = str_replace(['？', '，'], '', $topic);
                if (strlen($topic) != strlen($removePunctChinese)) {
                    continue;
                }
                // Remove the # sign from Hashtag
                $s = trim(str_replace('#', '', $s));
                if (empty($s)) {
                    continue;
                }
                // Existence of Hashtag
                $hashInfo = FresnsHashtags::where('name', $s)->first();
                if ($hashInfo) {
                    // hashtags table: comment_count +1
                    FresnsHashtags::where('id', $hashInfo['id'])->increment('comment_count');
                    // Establishing Affiliations
                    $res = DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->insert([
                        'linked_type' => 2,
                        'linked_id' => $draftComment['comment_id'],
                        'hashtag_id' => $hashInfo['id'],
                    ]);
                } else {
                    // New Hashtag and Hashtag Association
                    $slug = urlencode($s);
                    $input = [
                        'slug' => $slug,
                        'name' => $s,
                        'comment_count' => 1,
                    ];
                    $hashtagId = (new FresnsHashtags())->store($input);
                    // Establishing Affiliations
                    $res = DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->insert([
                        'linked_type' => 2,
                        'linked_id' => $draftComment['comment_id'],
                        'hashtag_id' => $hashtagId,
                    ]);
                }
            }
        }

        return true;
    }

    // Parsing truncated content information
    public function parseDraftContent($draftId)
    {
        $draftComment = FresnsCommentLogs::find($draftId);
        $content = $draftComment['content'];

        // Get the maximum number of words for the comment brief
        $commentEditorBriefCount = ApiConfigHelper::getConfigByItemKey(FsConfig::COMMENT_EDITOR_BRIEF_COUNT) ?? 280;
        if (mb_strlen($draftComment['content']) > $commentEditorBriefCount) {
            $contentInfo = $this->truncatedContentInfo($content, $commentEditorBriefCount);
            $content = $contentInfo['truncated_content'];
        } else {
            $content = $draftComment['content'];
        }
        $content = $this->blockWords($content);

        return $content;
    }

    // "@", "#", "Link" Location information of the three in the full text

    // If the content exceeds the set number of words, the brief is stored.
    // If the last content of the brief is "@", "#", and "Link", it should be kept in full and not truncated.
    // The maximum number of words in the brief can be exceeded when preserving.
    public function truncatedContentInfo($content, $wordCount = 280)
    {
        // The currently configured Hashtag display mode
        $hashtagShow = ApiConfigHelper::getConfigByItemKey(FsConfig::HASHTAG_SHOW) ?? 2;
        // Match the location information in $content, where the rule is placed in the configuration file
        if ($hashtagShow == 1) {
            preg_match("/#.*?\s/", $content, $singlePoundMatches, PREG_OFFSET_CAPTURE);
        } else {
            preg_match('/#[\S].*?[\S]#/', $content, $singlePoundMatches, PREG_OFFSET_CAPTURE);
        }
        /**
         * preg_match("/<a .*?>.*?<\/a>/",$content,$hrefMatches,PREG_OFFSET_CAPTURE);.
         *  */
        preg_match("/http[s]:\/\/.*?\s/", $content, $hrefMatches, PREG_OFFSET_CAPTURE);

        // preg_match("/<a href=.*?}></a>/", $content, $hrefMatches,PREG_OFFSET_CAPTURE);
        preg_match("/@.*?\s/", $content, $atMatches, PREG_OFFSET_CAPTURE);
        $truncatedPos = ceil($wordCount);
        $findTruncatedPos = false;
        // Get the number of characters corresponding to the matched data (the match is bytes)
        $contentArr = FresnsPostsService::getString($content);
        $charCounts = FresnsPostsService::getChar($contentArr, $truncatedPos);
        // Determine the position of the interval where this wordCount falls.
        // If there is a hit, find the corresponding truncation position and execute the truncation
        // https://www.php.net/manual/en/function.preg-match.php
        foreach ($singlePoundMatches as $currMatch) {
            $matchStr = $currMatch[0];
            $matchStrStartPosition = $currMatch[1];
            $matchStrEndPosition = $currMatch[1] + strlen($matchStr);
            // Hit
            if ($matchStrStartPosition <= $charCounts && $matchStrEndPosition >= $charCounts) {
                $findTruncatedPos = true;
                $truncatedPos = $matchStrEndPosition;
            }
        }

        if (! $findTruncatedPos) {
            foreach ($hrefMatches as $currMatch) {
                $matchStr = $currMatch[0];
                $matchStrStartPosition = $currMatch[1];
                $matchStrEndPosition = $currMatch[1] + strlen($matchStr);
                // Hit
                if ($matchStrStartPosition <= $charCounts && $matchStrEndPosition >= $charCounts) {
                    $findTruncatedPos = true;
                    $truncatedPos = $matchStrEndPosition;
                }
            }
        }
        if (! $findTruncatedPos) {
            foreach ($atMatches as $currMatch) {
                $matchStr = $currMatch[0];
                $matchStrStartPosition = $currMatch[1];
                $matchStrEndPosition = $currMatch[1] + strlen($matchStr);
                // Hit
                if ($matchStrStartPosition <= $charCounts && $matchStrEndPosition >= $charCounts) {
                    $findTruncatedPos = true;
                    $truncatedPos = $matchStrEndPosition;
                }
            }
        }

        // Execute the operation
        $info = [];
        $info['find_truncated_pos'] = $findTruncatedPos;
        $info['truncated_pos'] = $truncatedPos;  // Truncation position
        if ($findTruncatedPos) {
            // Byte count to word count
            $chars = FresnsPostsService::getChars($content);
            $strLen = FresnsPostsService::getStrLen($chars, $truncatedPos);
        } else {
            $strLen = $truncatedPos;
        }
        $info['truncated_content'] = Str::substr($content, 0, $strLen); // Final content
        // $info['double_pound_arr'] = $doublePoundMatches;
        $info['single_pound_arr'] = $singlePoundMatches;
        $info['link_pound_arr'] = $hrefMatches;
        $info['at_arr'] = $atMatches;

        return $info;
    }

    public function regular_domain($domain)
    {
        if (substr($domain, 0, 7) == 'http://') {
            $domain = substr($domain, 7);
        }
        if (substr($domain, 0, 8) == 'https://') {
            $domain = substr($domain, 8);
        }
        if (strpos($domain, '/') !== false) {
            $domain = substr($domain, 0, strpos($domain, '/'));
        }

        return strtolower($domain);
    }

    public function top_domain($domain)
    {
        $domain = $this->regular_domain($domain);
        // Domain name suffix
        $iana_root = [
            // gTLDs
            'com', 'net', 'org', 'edu', 'gov', 'int', 'mil', 'arpa', 'biz', 'info', 'pro', 'name', 'coop', 'travel', 'xxx', 'idv', 'aero', 'museum', 'mobi', 'asia', 'tel', 'post', 'jobs', 'cat',
            // ccTLDs
            'ad', 'ae', 'af', 'ag', 'ai', 'al', 'am', 'an', 'ao', 'aq', 'ar', 'as', 'at', 'au', 'aw', 'az', 'ba', 'bb', 'bd', 'be', 'bf', 'bg', 'bh', 'bi', 'bj', 'bm', 'bn', 'bo', 'br', 'bs', 'bt', 'bv', 'bw', 'by', 'bz', 'ca', 'cc', 'cd', 'cf', 'cg', 'ch', 'ci', 'ck', 'cl', 'cm', 'cn', 'co', 'cr', 'cu', 'cv', 'cx', 'cy', 'cz', 'de', 'dj', 'dk', 'dm', 'do', 'dz', 'ec', 'ee', 'eg', 'eh', 'er', 'es', 'et', 'eu', 'fi', 'fj', 'fk', 'fm', 'fo', 'fr', 'ga', 'gd', 'ge', 'gf', 'gg', 'gh', 'gi', 'gl', 'gm', 'gn', 'gp', 'gq', 'gr', 'gs', 'gt', 'gu', 'gw', 'gy', 'hk', 'hm', 'hn', 'hr', 'ht', 'hu', 'id', 'ie', 'il', 'im', 'in', 'io', 'iq', 'ir', 'is', 'it', 'je', 'jm', 'jo', 'jp', 'ke', 'kg', 'kh', 'ki', 'km', 'kn', 'kp', 'kr', 'kw', 'ky', 'kz', 'la', 'lb', 'lc', 'li', 'lk', 'lr', 'ls', 'ma', 'mc', 'md', 'me', 'mg', 'mh', 'mk', 'ml', 'mm', 'mn', 'mo', 'mp', 'mq', 'mr', 'ms', 'mt', 'mu', 'mv', 'mw', 'mx', 'my', 'mz', 'na', 'nc', 'ne', 'nf', 'ng', 'ni', 'nl', 'no', 'np', 'nr', 'nu', 'nz', 'om', 'pa', 'pe', 'pf', 'pg', 'ph', 'pk', 'pl', 'pm', 'pn', 'pr', 'ps', 'pt', 'pw', 'py', 'qa', 're', 'ro', 'ru', 'rw', 'sa', 'sb', 'sc', 'sd', 'se', 'sg', 'sh', 'si', 'sj', 'sk', 'sm', 'sn', 'so', 'sr', 'st', 'sv', 'sy', 'sz', 'tc', 'td', 'tf', 'tg', 'th', 'tj', 'tk', 'tl', 'tm', 'tn', 'to', 'tp', 'tr', 'tt', 'tv', 'tw', 'tz', 'ua', 'ug', 'uk', 'um', 'us', 'uy', 'uz', 'va', 'vc', 've', 'vg', 'vi', 'vn', 'vu', 'wf', 'ws', 'ye', 'yt', 'yu', 'yr', 'za', 'zm', 'zw',
            // new gTLDs (Business)
            'accountant', 'club', 'coach', 'college', 'company', 'construction', 'consulting', 'contractors', 'cooking', 'corp', 'credit', 'creditcard', 'dance', 'dealer', 'democrat', 'dental', 'dentist', 'design', 'diamonds', 'direct', 'doctor', 'drive', 'eco', 'education', 'energy', 'engineer', 'engineering', 'equipment', 'events', 'exchange', 'expert', 'express', 'faith', 'farm', 'farmers', 'fashion', 'finance', 'financial', 'fish', 'fit', 'fitness', 'flights', 'florist', 'flowers', 'food', 'football', 'forsale', 'furniture', 'game', 'games', 'garden', 'gmbh', 'golf', 'health', 'healthcare', 'hockey', 'holdings', 'holiday', 'home', 'hospital', 'hotel', 'hotels', 'house', 'inc', 'industries', 'insurance', 'insure', 'investments', 'islam', 'jewelry', 'justforu', 'kid', 'kids', 'law', 'lawyer', 'legal', 'lighting', 'limited', 'live', 'llc', 'llp', 'loft', 'ltd', 'ltda', 'managment', 'marketing', 'media', 'medical', 'men', 'money', 'mortgage', 'moto', 'motorcycles', 'music', 'mutualfunds', 'ngo', 'partners', 'party', 'pharmacy', 'photo', 'photography', 'photos', 'physio', 'pizza', 'plumbing', 'press', 'prod', 'productions', 'radio', 'rehab', 'rent', 'repair', 'report', 'republican', 'restaurant', 'room', 'rugby', 'safe', 'sale', 'sarl', 'save', 'school', 'secure', 'security', 'services', 'shoes', 'show', 'soccer', 'spa', 'sport', 'sports', 'spot', 'srl', 'storage', 'studio', 'tattoo', 'taxi', 'team', 'tech', 'technology', 'thai', 'tips', 'tour', 'tours', 'toys', 'trade', 'trading', 'travelers', 'university', 'vacations', 'ventures', 'versicherung', 'versicherung', 'vet', 'wedding', 'wine', 'winners', 'work', 'works', 'yachts', 'zone',
            // new gTLDs (Construction & Real Estate)
            'archi', 'architect', 'casa', 'contruction', 'estate', 'haus', 'house', 'immo', 'immobilien', 'lighting', 'loft', 'mls', 'realty',
            // new gTLDs (Community & Religion)
            'academy', 'arab', 'bible', 'care', 'catholic', 'charity', 'christmas', 'church', 'college', 'community', 'contact', 'degree', 'education', 'faith', 'foundation', 'gay', 'halal', 'hiv', 'indiands', 'institute', 'irish', 'islam', 'kiwi', 'latino', 'mba', 'meet', 'memorial', 'ngo', 'phd', 'prof', 'school', 'schule', 'science', 'singles', 'social', 'swiss', 'thai', 'trust', 'university', 'uno',
            // new gTLDs (E-commerce & Shopping)
            'auction', 'best', 'bid', 'boutique', 'center', 'cheap', 'compare', 'coupon', 'coupons', 'deal', 'deals', 'diamonds', 'discount', 'fashion', 'forsale', 'free', 'gift', 'gold', 'gratis', 'hot', 'jewelry', 'kaufen', 'luxe', 'luxury', 'market', 'moda', 'pay', 'promo', 'qpon', 'review', 'reviews', 'rocks', 'sale', 'shoes', 'shop', 'shopping', 'store', 'tienda', 'top', 'toys', 'watch', 'zero',
            // new gTLDs (Dining)
            'bar', 'bio', 'cafe', 'catering', 'coffee', 'cooking', 'diet', 'eat', 'food', 'kitchen', 'menu', 'organic', 'pizza', 'pub', 'rest', 'restaurant', 'vodka', 'wine',
            // new gTLDs (Travel)
            'abudhabi', 'africa', 'alsace', 'amsterdam', 'barcelona', 'bayern', 'berlin', 'boats', 'booking', 'boston', 'brussels', 'budapest', 'caravan', 'casa', 'catalonia', 'city', 'club', 'cologne', 'corsica', 'country', 'cruise', 'cruises', 'deal', 'deals', 'doha', 'dubai', 'durban', 'earth', 'flights', 'fly', 'fun', 'gent', 'guide', 'hamburg', 'helsinki', 'holiday', 'hotel', 'hoteles', 'hotels', 'ist', 'istanbul', 'joburg', 'koeln', 'land', 'london', 'madrid', 'map', 'melbourne', 'miami', 'moscow', 'nagoya', 'nrw', 'nyc', 'osaka', 'paris', 'party', 'persiangulf', 'place', 'quebec', 'reise', 'reisen', 'rio', 'roma', 'room', 'ruhr', 'saarland', 'stockholm', 'swiss', 'sydney', 'taipei', 'tickets', 'tirol', 'tokyo', 'tour', 'tours', 'town', 'travelers', 'vacations', 'vegas', 'wales', 'wien', 'world', 'yokohama', 'zuerich',
            // new gTLDs (Sports & Hobbies)
            'art', 'auto', 'autos', 'baby', 'band', 'baseball', 'beats', 'beauty', 'beknown', 'bike', 'book', 'boutique', 'broadway', 'car', 'cars', 'club', 'coach', 'contact', 'cool', 'cricket', 'dad', 'dance', 'date', 'dating', 'design', 'dog', 'events', 'family', 'fan', 'fans', 'fashion', 'film', 'final', 'fishing', 'football', 'fun', 'furniture', 'futbol', 'gallery', 'game', 'games', 'garden', 'gay', 'golf', 'guru', 'hair', 'hiphop', 'hockey', 'home', 'horse', 'icu', 'joy', 'kid', 'kids', 'life', 'lifestyle', 'like', 'living', 'lol', 'makeup', 'meet', 'men', 'moda', 'moi', 'mom', 'movie', 'movistar', 'music', 'party', 'pet', 'pets', 'photo', 'photography', 'photos', 'pics', 'pictures', 'play', 'poker', 'rodeo', 'rugby', 'run', 'salon', 'singles', 'ski', 'skin', 'smile', 'soccer', 'social', 'song', 'soy', 'sport', 'sports', 'star', 'style', 'surf', 'tatoo', 'tennis', 'theater', 'theatre', 'tunes', 'vip', 'wed', 'wedding', 'winwinners', 'yoga', 'you',
            // new gTLDs (Network Technology)
            'analytics', 'antivirus', 'app', 'blog', 'call', 'camera', 'channel', 'chat', 'click', 'cloud', 'computer', 'contact', 'data', 'dev', 'digital', 'direct', 'docs', 'domains', 'dot', 'download', 'email', 'foo', 'forum', 'graphics', 'guide', 'help', 'home', 'host', 'hosting', 'idn', 'link', 'lol', 'mail', 'mobile', 'network', 'online', 'open', 'page', 'phone', 'pin', 'search', 'site', 'software', 'webcam',
            // new gTLDs (Other)
            'airforce', 'army', 'black', 'blue', 'box', 'buzz', 'casa', 'cool', 'day', 'discover', 'donuts', 'exposed', 'fast', 'finish', 'fire', 'fyi', 'global', 'green', 'help', 'here', 'how', 'international', 'ira', 'jetzt', 'jot', 'like', 'live', 'kim', 'navy', 'new', 'news', 'next', 'ninja', 'now', 'one', 'ooo', 'pink', 'plus', 'red', 'solar', 'tips', 'today', 'weather', 'wow', 'wtf', 'xyz', 'abogado', 'adult', 'anquan', 'aquitaine', 'attorney', 'audible', 'autoinsurance', 'banque', 'bargains', 'bcn', 'beer', 'bet', 'bingo', 'blackfriday', 'bom', 'boo', 'bot', 'broker', 'builders', 'business', 'bzh', 'cab', 'cal', 'cam', 'camp', 'cancerresearch', 'capetown', 'carinsurance', 'casino', 'ceo', 'cfp', 'circle', 'claims', 'cleaning', 'clothing', 'codes', 'condos', 'connectors', 'courses', 'cpa', 'cymru', 'dds', 'delivery', 'desi', 'directory', 'diy', 'dvr', 'ecom', 'enterprises', 'esq', 'eus', 'fail', 'feedback', 'financialaid', 'frontdoor', 'fund', 'gal', 'gifts', 'gives', 'giving', 'glass', 'gop', 'got', 'gripe', 'grocery', 'group', 'guitars', 'hangout', 'homegoods', 'homes', 'homesense', 'hotels', 'ing', 'ink', 'juegos', 'kinder', 'kosher', 'kyoto', 'lat', 'lease', 'lgbt', 'liason', 'loan', 'loans', 'locker', 'lotto', 'love', 'maison', 'markets', 'matrix', 'meme', 'mov', 'okinawa', 'ong', 'onl', 'origins', 'parts', 'patch', 'pid', 'ping', 'porn', 'progressive', 'properties', 'property', 'protection', 'racing', 'read', 'realestate', 'realtor', 'recipes', 'rentals', 'sex', 'sexy', 'shopyourway', 'shouji', 'silk', 'solutions', 'stroke', 'study', 'sucks', 'supplies', 'supply', 'tax', 'tires', 'total', 'training', 'translations', 'travelersinsurcance', 'ventures', 'viajes', 'villas', 'vin', 'vivo', 'voyage', 'vuelos', 'wang', 'watches',
        ];
        $sub_domain = explode('.', $domain);
        $top_domain = '';
        $top_domain_count = 0;
        for ($i = count($sub_domain) - 1; $i >= 0; $i--) {
            if ($i == 0) {
                // just in case of something like NAME.COM
                break;
            }
            if (in_array($sub_domain [$i], $iana_root)) {
                $top_domain_count++;
                $top_domain = '.'.$sub_domain [$i].$top_domain;
                if ($top_domain_count >= 2) {
                    break;
                }
            }
        }
        $top_domain = $sub_domain [count($sub_domain) - $top_domain_count - 1].$top_domain;

        return $top_domain;
    }

    // get childrenIds
    public function getChildrenIds($categoryItem, &$childrenIdArr)
    {
        if (key_exists('children', $categoryItem)) {
            $childrenArr = $categoryItem['children'];
            foreach ($childrenArr as $children) {
                $childrenIdArr[] = $children['value'];
                $this->getChildrenIds($children, $childrenIdArr);
            }
        }
    }

    // Check: Block Word
    public function blockWords($text)
    {
        $blockWordsArr = FresnsBlockWords::get()->toArray();

        foreach ($blockWordsArr as $v) {
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
}
