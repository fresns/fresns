<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Member;

use App\Helpers\DateHelper;
use App\Http\Center\Common\ErrorCodeService;
use App\Http\Center\Common\GlobalService;
use App\Http\Center\Common\ValidateService;
use App\Http\Center\Helper\CmdRpcHelper;
use App\Http\FresnsApi\Base\FresnsBaseApiController;
use App\Http\FresnsApi\Content\FresnsCommentsResource;
use App\Http\FresnsApi\Content\FresnsGroupsResource;
use App\Http\FresnsApi\Content\FresnsHashtagsResource;
use App\Http\FresnsApi\Content\FresnsPostsResource;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsApi\Helpers\ApiLanguageHelper;
use App\Http\FresnsCmd\FresnsCmdWords;
use App\Http\FresnsCmd\FresnsCmdWordsConfig;
use App\Http\FresnsDb\FresnsCommentAppends\FresnsCommentAppends;
use App\Http\FresnsDb\FresnsComments\FresnsComments;
use App\Http\FresnsDb\FresnsComments\FresnsCommentsService;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigs;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Http\FresnsDb\FresnsConfigs\FresnsConfigsService;
use App\Http\FresnsDb\FresnsFileLogs\FresnsFileLogsConfig;
use App\Http\FresnsDb\FresnsFiles\FresnsFiles;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsGroups\FresnsGroupsConfig;
use App\Http\FresnsDb\FresnsGroups\FresnsGroupsService;
use App\Http\FresnsDb\FresnsHashtags\FresnsHashtags;
use App\Http\FresnsDb\FresnsHashtags\FresnsHashtagsService;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollowsService;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikes;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikesService;
use App\Http\FresnsDb\FresnsMemberRoles\FresnsMemberRolesService;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShields;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShieldsService;
use App\Http\FresnsDb\FresnsMemberStats\FresnsMemberStats;
use App\Http\FresnsDb\FresnsMemberStats\FresnsMemberStatsConfig;
use App\Http\FresnsDb\FresnsNotifies\FresnsNotifiesService;
use App\Http\FresnsDb\FresnsPostAppends\FresnsPostAppends;
use App\Http\FresnsDb\FresnsPostMembers\FresnsPostMembersConfig;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsService;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogs;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogsConfig;
use App\Http\FresnsDb\FresnsSessionLogs\FresnsSessionLogsService;
use App\Http\FresnsDb\FresnsStopWords\FresnsStopWords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FsControllerApi extends FresnsBaseApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->service = new FsService();
        $this->initData();
    }

    // Member Login
    public function auth(Request $request)
    {
        $rule = [
            'mid' => 'required|numeric',
        ];
        ValidateService::validateRule($request, $rule);

        $uid = GlobalService::getGlobalKey('user_id');

        $token = $request->header('token');

        $platform = $request->header('platform');
        $request->offsetSet('platform', $platform);
        $passwordBase64 = $request->input('password');

        if ($passwordBase64) {
            $password = base64_decode($passwordBase64, true);
            if ($password == false) {
                $password = $passwordBase64;
            }
        } else {
            $password = null;
        }

        $mid = $request->input('mid');
        $mid = FresnsMembers::where('uuid', $mid)->value('id');

        $checkMember = FsChecker::checkUserMember($mid, $uid);
        if ($checkMember == false) {
            $this->error(ErrorCodeService::MEMBER_FAIL);
        }

        $sessionLogId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionLogId) {
            $sessionInput = [
                'object_order_id' => $mid,
                'user_id' => $uid,
                'member_id' => $mid,
            ];
            FresnsSessionLogs::where('id', $sessionLogId)->update($sessionInput);
        }

        // Check the number of login password errors in the last 1 hour for the member to whom the email or cell phone number belongs.
        // If it reaches 5 times, the login will be restricted.
        // session_logs > object_type=7
        $startTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $sessionCount = FresnsSessionLogs::where('created_at', '>=', $startTime)
        ->where('user_id', $uid)
        ->where('member_id', $mid)
        ->where('object_result', FresnsSessionLogsConfig::OBJECT_RESULT_ERROR)
        ->where('object_type', FresnsSessionLogsConfig::OBJECT_TYPE_MEMBER_LOGIN)
        ->count();

        if ($sessionCount >= 5) {
            $this->error(ErrorCodeService::ACCOUNT_COUNT_ERROR);
        }

        $member = FresnsMembers::where('id', $mid)->first();
        if (! empty($member['password'])) {
            if (! Hash::check($password, $member['password'])) {
                $this->error(ErrorCodeService::MEMBER_PASSWORD_INVALID);
            }
        }
        $langTag = ApiLanguageHelper::getLangTagByHeader();

        $request->offsetSet('langTag', $langTag);
        $request->offsetSet('mid', $mid);

        $data = $this->service->getMemberDetail($mid, $mid, true, $langTag);
        if ($data) {
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_CREATE_SESSION_TOKEN;
            $input['uid'] = $request->header('uid');
            $input['platform'] = $request->header('platform');
            $input['mid'] = $member['uuid'];
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                $this->errorCheckInfo($resp);
            }
            $output = $resp['output'];
            $data['token'] = $output['token'] ?? '';
            $data['tokenExpiredTime'] = $output['tokenExpiredTime'] ?? '';
        }

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $uid, $mid, $mid);
        }

        $this->success($data);
    }

    // Member Detail
    public function detail(Request $request)
    {
        $mid = GlobalService::getGlobalKey('member_id');
        $viewMid = $request->input('viewMid');
        $viewMname = $request->input('viewMname');
        $langTag = ApiLanguageHelper::getLangTagByHeader();

        $request->offsetSet('langTag', $langTag);
        $request->offsetSet('mid', $mid);
        if (empty($viewMid)) {
            $viewMid = FresnsMembers::where('name', $viewMname)->value('id');
        } else {
            $viewMid = FresnsMembers::where('uuid', $viewMid)->value('id');
        }

        if (empty($viewMid)) {
            $this->error(ErrorCodeService::USER_CHECK_ERROR);
        }

        // Is it me
        $isMe = false;
        if ($mid == $viewMid) {
            $isMe = true;
        }

        $data['common'] = $this->service->common($viewMid, $langTag, $isMe, $mid);
        $data['detail'] = $this->service->getMemberDetail($mid, $viewMid, $isMe, $langTag);

        $this->success($data);
    }

    // Member List
    public function lists(Request $request)
    {
        $rule = [
            'gender' => 'numeric|in:0,1,2',
            'sortDirection' => 'numeric|in:1,2',
            'pageSize' => 'numeric',
            'page' => 'numeric',
        ];
        ValidateService::validateRule($request, $rule);

        $searchKey = $request->input('searchKey');
        $gender = $request->input('gender', 3);
        $sortType = $request->input('sortType', 'follow');
        $createdTimeGt = $request->input('createdTimeGt');
        $createdTimeLt = $request->input('createdTimeLt');
        $sortDirection = $request->input('sortDirection', 2);
        $pageSize = $request->input('pageSize', 20);
        $page = $request->input('page', 1);
        if ($pageSize > 50) {
            $pageSize = 50;
        }
        $query = DB::table('members as me');
        $query = $query->select('me.*')->leftJoin('member_stats as st', 'me.id', '=', 'st.member_id');

        if ($searchKey) {
            $memberIdArr1 = FresnsMembers::where('name', 'LIKE', "%$searchKey%")->pluck('id')->toArray();
            $memberIdArr2 = FresnsMembers::where('nickname', 'LIKE', "%$searchKey%")->pluck('id')->toArray();
            $idArr = array_unique(array_merge($memberIdArr1, $memberIdArr2));
            $query->whereIn('me.id', $idArr);
        }
        if ($createdTimeGt) {
            $createdTimeGt = DateHelper::fresnsInputTimeToTimezone($createdTimeGt);
            $query->where('st.created_at', '>', $createdTimeGt);
        }
        if ($createdTimeLt) {
            $createdTimeLt = DateHelper::fresnsInputTimeToTimezone($createdTimeLt);
            $query->where('st.created_at', '<', $createdTimeLt);
        }

        if (in_array($gender, [0, 1, 2])) {
            $query->where('me.gender', $gender);
        }

        $sortDirection = $sortDirection == 1 ? 'ASC' : 'DESC';
        switch ($sortType) {
            case 'like':
                $query->orderBy('st.like_me_count', $sortDirection);
                break;
            case 'follow':
                $query->orderBy('st.follow_me_count', $sortDirection);
                break;
            case 'shield':
                $query->orderBy('st.shield_me_count', $sortDirection);
                break;
            case 'post':
                $query->orderBy('st.post_publish_count', $sortDirection);
                break;
            case 'comment':
                $query->orderBy('st.comment_publish_count', $sortDirection);
                break;
            case 'postLike':
                $query->orderBy('st.post_like_count', $sortDirection);
                break;
            case 'commentLike':
                $query->orderBy('st.comment_like_count', $sortDirection);
                break;
            case 'extcredits1':
                $query->orderBy('st.extcredits1', $sortDirection);
                break;
            case 'extcredits2':
                $query->orderBy('st.extcredits2', $sortDirection);
                break;
            case 'extcredits3':
                $query->orderBy('st.extcredits3', $sortDirection);
                break;
            case 'extcredits4':
                $query->orderBy('st.extcredits4', $sortDirection);
                break;
            default:
                $query->orderBy('st.extcredits5', $sortDirection);
                break;
        }

        $item = $query->paginate($pageSize, ['*'], 'page', $page);
        $data = [];
        $data['list'] = FresnsMemberListsResource::collection($item->items())->toArray($item->items());
        $pagination['total'] = $item->total();
        $pagination['current'] = $page;
        $pagination['pageSize'] = $pageSize;
        $pagination['lastPage'] = $item->lastPage();

        $data['pagination'] = $pagination;
        $this->success($data);
    }

    // Member Edit Profile
    public function edit(Request $request)
    {
        $rule = [
            'gender' => 'numeric|in:0,1,2',
            'dialogLimit' => 'numeric',
            'birthday' => 'date_format:"Y-m-d H:i:s"',
        ];
        ValidateService::validateRule($request, $rule);

        $uid = GlobalService::getGlobalKey('user_id');
        $mid = GlobalService::getGlobalKey('member_id');

        $checkMember = FsChecker::checkUserMember($mid, $uid);
        if ($checkMember == false) {
            $this->error(ErrorCodeService::MEMBER_FAIL);
        }

        $mname = $request->input('mname');
        $nickname = $request->input('nickname');
        $avatarFid = $request->input('avatarFid');
        $bio = $request->input('bio');
        $member = FresnsMembers::where('id', $mid)->first();
        if (empty($member)) {
            $this->error(ErrorCodeService::MEMBER_CHECK_ERROR);
        }

        $last_name_at = $member['last_name_at'];
        if ($mname) {
            $itemValue = FresnsConfigs::where('item_key', FresnsConfigsConfig::MNAME_EDIT)->value('item_value');
            if ($itemValue > 0) {
                if ($last_name_at) {
                    $begin_date = strtotime($last_name_at);
                    $end_date = strtotime(date('Y-m-d', time()));
                    $days = round(($end_date - $begin_date) / 3600 / 24);
                    if ($days <= $itemValue) {
                        $this->error(ErrorCodeService::UPDATE_TIME_ERROR);
                    }
                }
            }
            $disableNames = FresnsConfigs::where('item_key', 'disable_names')->value('item_value');
            $disableNamesArr = json_decode($disableNames, true);
            if (in_array($mname, $disableNamesArr)) {
                $this->error(ErrorCodeService::DISABLE_NAME_ERROR);
            }
            // Determine if the name is duplicated
            $memberCount = FresnsMembers::where('name', $mname)->count();
            if ($memberCount > 0) {
                $this->error(ErrorCodeService::MEMBER_NAME_ERROR);
            }
        }

        $last_nickname_at = $member['last_nickname_at'];
        if ($nickname) {
            $itemValue = FresnsConfigs::where('item_key', FresnsConfigsConfig::NICKNAME_EDIT)->value('item_value');
            if ($itemValue > 0) {
                if ($last_nickname_at) {
                    $begin_date = strtotime($last_nickname_at);
                    $end_date = strtotime(date('Y-m-d', time()));
                    $days = round(($end_date - $begin_date) / 3600 / 24);
                    if ($days <= $itemValue) {
                        $this->error(ErrorCodeService::UPDATE_TIME_ERROR);
                    }
                }
            }

            $stopWordsArr = FresnsStopWords::get()->toArray();

            foreach ($stopWordsArr as $v) {
                $str = strstr($nickname, $v['word']);
                if ($str != false) {
                    if ($v['member_mode'] == 2) {
                        $nickname = str_replace($v['word'], $v['replace_word'], $nickname);
                        $request->offsetSet('nickname', $nickname);
                    }
                    if ($v['member_mode'] == 3) {
                        $this->error(ErrorCodeService::UPDATE_TIME_ERROR);
                    }
                }
            }
        }

        if ($avatarFid) {
            $avatarFileId = FresnsFiles::where('uuid', $avatarFid)->value('id');
            FresnsMembers::where('id', $mid)->update(['avatar_file_id' => $avatarFileId]);
        }

        if ($bio) {
            $stopWordsArr = FresnsStopWords::get()->toArray();

            foreach ($stopWordsArr as $v) {
                $str = strstr($bio, $v['word']);
                if ($str != false) {
                    if ($v['member_mode'] == 2) {
                        $bio = str_replace($v['word'], $v['replace_word'], $bio);
                        $request->offsetSet('bio', $bio);
                    }
                    if ($v['member_mode'] == 3) {
                        $this->error(ErrorCodeService::UPDATE_TIME_ERROR);
                    }
                }
            }
        }
        $map = FsConfig::MEMBER_EDIT;

        $itemArr = [];
        foreach ($map as $k => $v) {
            $req = $request->input($k);
            if ($req) {
                $itemArr[$v] = $req;
            }
        }

        if ($itemArr) {
            FresnsMembers::where('id', $mid)->update($itemArr);
        }

        if ($mname) {
            $input = [
                'last_name_at' => date('Y-m-d H:i:s', time()),
            ];
            FresnsMembers::where('id', $mid)->update($input);
        }

        if ($nickname) {
            $input = [
                'last_nickname_at' => date('Y-m-d H:i:s', time()),
            ];
            FresnsMembers::where('id', $mid)->update($input);
        }

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $uid, $mid, $mid);
        }

        $this->success((object) []);
    }

    // Get Member Role List
    public function roles(Request $request)
    {
        $rule = [
            'type' => 'in:1,2,3',
            'pageSize' => 'numeric',
            'page' => 'numeric',
        ];

        ValidateService::validateRule($request, $rule);
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 30);
        $fresnsMemberRolesService = new FresnsMemberRolesService();
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('pageSize', $pageSize);
        $data = $fresnsMemberRolesService->searchData();
        $this->success($data);
    }

    // Get Member Interactions Data
    public function interactions(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2,3,4,5',
            'objectType' => 'numeric|in:1,2,3,4,5',
            'objectId' => 'required',
            'sortDirection' => 'numeric',
            'pageSize' => 'numeric',
            'page' => 'numeric',
        ];
        ValidateService::validateRule($request, $rule);

        $type = $request->input('type');
        $objectType = $request->input('objectType', 1);
        $objectId = $request->input('objectId');
        $sortDirection = $request->input('sortDirection');
        $pageSize = $request->input('pageSize', 30);
        $page = $request->input('page', 1);

        /**
         * Whether to output data when viewing other people's information
         * https://fresns.org/database/keyname/interactives.html
         * View other people's content settings.
         */
        $typeArr = [4, 5];
        if (! in_array($type, $typeArr)) {
            $isMarkLists = FsChecker::checkMarkLists($type, $objectType);
            if ($isMarkLists == false) {
                $this->error(ErrorCodeService::MEMBER_NO_PERMISSION);
            }
        }

        $idArr = [];

        /**
         * Whether to output data when viewing other people's information
         * https://fresns.org/database/keyname/interactives.html
         * View other people's content settings.
         *
         * type=1 Get a list of all members liked by objectType > objectId (query member_likes table)
         * type=2 Get a list of all members followed by objectType > objectId (query member_follows table)
         * type=3 Get a list of all members blocked by objectType > objectId (query member_shields table)
         */
        switch ($type) {
            case 1:
                $likeId = 0;
                switch ($objectType) {
                    case 1:
                        $it_likers = ApiConfigHelper::getConfigByItemKey('it_likers');
                        if ($it_likers == true) {
                            $likeId = FresnsMembers::where('uuid', $objectId)->value('id');
                        }
                        break;
                    case 2:
                        $it_like_groups = ApiConfigHelper::getConfigByItemKey('it_like_groups');
                        if ($it_like_groups == true) {
                            $likeId = FresnsGroups::where('uuid', $objectId)->value('id');
                        }
                        break;
                    case 3:
                        $it_like_hashtags = ApiConfigHelper::getConfigByItemKey('it_like_hashtags');
                        if ($it_like_hashtags == true) {
                            $likeId = FresnsHashtags::where('slug', $objectId)->value('id');
                        }
                        break;
                    case 4:
                        $it_like_posts = ApiConfigHelper::getConfigByItemKey('it_like_posts');
                        if ($it_like_posts == true) {
                            $likeId = FresnsPosts::where('uuid', $objectId)->value('id');
                        }
                        break;
                    default:
                        $it_like_comments = ApiConfigHelper::getConfigByItemKey('it_like_comments');
                        if ($it_like_comments == true) {
                            $likeId = FresnsComments::where('uuid', $objectId)->value('id');
                        }
                        break;
                }
                $idArr = FresnsMemberLikes::where('like_type', $objectType)->where('like_id', $likeId)->pluck('member_id')->toArray();
                break;
            case 2:
                $followId = 0;
                switch ($objectType) {
                    case 1:
                        $it_followers = ApiConfigHelper::getConfigByItemKey('it_followers');
                        if ($it_followers == true) {
                            $followId = FresnsMembers::where('uuid', $objectId)->value('id');
                        }
                        break;
                    case 2:
                        $it_follow_groups = ApiConfigHelper::getConfigByItemKey('it_follow_groups');
                        if ($it_follow_groups == true) {
                            $followId = FresnsGroups::where('uuid', $objectId)->value('id');
                        }
                        break;
                    case 3:
                        $it_follow_hashtags = ApiConfigHelper::getConfigByItemKey('it_follow_hashtags');
                        if ($it_follow_hashtags == true) {
                            $followId = FresnsHashtags::where('slug', $objectId)->value('id');
                        }
                        break;
                    case 4:
                        $it_follow_posts = ApiConfigHelper::getConfigByItemKey('it_follow_posts');
                        if ($it_follow_posts == true) {
                            $followId = FresnsPosts::where('uuid', $objectId)->value('id');
                        }
                        break;
                    default:
                        $it_follow_comments = ApiConfigHelper::getConfigByItemKey('it_follow_comments');
                        if ($it_follow_comments == true) {
                            $followId = FresnsComments::where('uuid', $objectId)->value('id');
                        }
                        break;
                }
                $idArr = FresnsMemberFollows::where('follow_type', $objectType)->where('follow_id', $followId)->pluck('member_id')->toArray();
                break;
            case 3:
                $shieldId = 0;
                switch ($objectType) {
                    case 1:
                        $it_shielders = ApiConfigHelper::getConfigByItemKey('it_shielders');
                        if ($it_shielders == true) {
                            $shieldId = FresnsMembers::where('uuid', $objectId)->value('id');
                        }
                        break;
                    case 2:
                        $it_shield_groups = ApiConfigHelper::getConfigByItemKey('it_shield_groups');
                        if ($it_shield_groups == true) {
                            $shieldId = FresnsGroups::where('uuid', $objectId)->value('id');
                        }
                        break;
                    case 3:
                        $it_shield_hashtags = ApiConfigHelper::getConfigByItemKey('it_shield_hashtags');
                        if ($it_shield_hashtags == true) {
                            $shieldId = FresnsHashtags::where('slug', $objectId)->value('id');
                        }
                        break;
                    case 4:
                        $it_shield_posts = ApiConfigHelper::getConfigByItemKey('it_shield_posts');
                        if ($it_shield_posts == true) {
                            $shieldId = FresnsPosts::where('uuid', $objectId)->value('id');
                        }
                        break;
                    default:
                        $it_shield_comments = ApiConfigHelper::getConfigByItemKey('it_shield_comments');
                        if ($it_shield_comments == true) {
                            $shieldId = FresnsComments::where('uuid', $objectId)->value('id');
                        }
                        break;
                }
                $idArr = FresnsMemberShields::where('shield_type', $objectType)->where('shield_id', $shieldId)->pluck('member_id')->toArray();
                break;
            case 4:
                if ($objectType == 4) {
                    $postUuidArr = FresnsPosts::where('uuid', $objectId)->pluck('id')->toArray();
                    $idArr = DB::table(FresnsPostMembersConfig::CFG_TABLE)->whereIn('post_id', $postUuidArr)->pluck('member_id')->toArray();
                }
                break;
            default:
                $idArr = DB::table(FresnsFileLogsConfig::CFG_TABLE)->where('file_id', $objectId)->pluck('member_id')->toArray();
                break;
        }

        $query = DB::table('members as me');
        $query = $query->select('me.*')->leftJoin('member_stats as st', 'me.id', '=', 'st.member_id');

        $query->whereIn('me.id', $idArr);

        $sortDirection = $sortDirection == 1 ? 'ASC' : 'DESC';
        $query->orderBy('me.created_at', $sortDirection);

        $item = $query->paginate($pageSize, ['*'], 'page', $page);
        $list = FresnsMemberListsResource::collection($item->items())->toArray($item->items());
        $data = [];
        $data['list'] = $list;
        $pagination['total'] = $item->total();
        $pagination['current'] = $page;
        $pagination['pageSize'] = $pageSize;
        $pagination['lastPage'] = $item->lastPage();

        $data['pagination'] = $pagination;
        $this->success($data);
    }

    // Operation Mark
    public function mark(Request $request)
    {
        $rule = [
            'type' => 'required|numeric|in:1,2',
            'markType' => 'required|numeric|in:1,2,3',
            'markTarget' => 'required|numeric|in:1,2,3,4,5',
            'markId' => 'required',
        ];
        ValidateService::validateRule($request, $rule);

        $type = $request->input('type');
        $markType = $request->input('markType');
        $markTarget = $request->input('markTarget');
        $markId = $request->input('markId');
        $mid = GlobalService::getGlobalKey('member_id');
        // Private mode, if the member has expired, no operation is allowed
        $siteMode = ApiConfigHelper::getConfigByItemKey('site_mode');
        if ($siteMode == 'private') {
            $midMember = FresnsMembers::where('id', $mid)->first();
            if (! empty($midMember['expired_at'])) {
                $time = date('Y-m-d H:i:s', time());
                if ($time > $midMember['expired_at']) {
                    $this->error(ErrorCodeService::MEMBER_EXPIRED_ERROR);
                }
            }
        }

        /**
         * Whether the right to operate
         * https://fresns.org/database/keyname/interactives.html
         * Interactive behavior settings.
         *
         * Tag members can not be themselves, as well as their own published posts, comments
         */
        $checkerApi = FsChecker::checkMarkApi($markType, $markTarget);
        if ($checkerApi == false) {
            $this->error(ErrorCodeService::MARK_NOT_ENABLE);
        }

        // Member
        if ($markTarget == 1) {
            $markId = FresnsMembers::where('uuid', $markId)->where('is_enable', 1)->value('id');
            if (empty($markId)) {
                $info = [
                    'markId' => 'null',
                ];
                $this->error(ErrorCodeService::MEMBER_CHECK_ERROR, $info);
            }
            if ($markId == $mid) {
                $this->error(ErrorCodeService::MARK_FOLLOW_ERROR);
            }
        }

        // Group
        // If groups > type_follow = 2, you cannot create a following by this function.
        if ($markTarget == 2) {
            $groups = FresnsGroups::where('uuid', $markId)->where('is_enable', 1)->first();
            if (empty($groups)) {
                $info = [
                    'markId' => 'null',
                ];
                $this->error(ErrorCodeService::GROUP_EXIST_ERROR, $info);
            }
            if ($groups['type_follow'] == 2) {
                $this->error(ErrorCodeService::GROUP_MARK_FOLLOW_ERROR);
            }
            $markId = $groups['id'];
        }

        // Hashtag
        if ($markTarget == 3) {
            $markId = FresnsHashtags::where('slug', $markId)->where('is_enable', 1)->value('id');
            if (empty($markId)) {
                $info = [
                    'markId' => 'null',
                ];
                $this->error(ErrorCodeService::HASHTAG_EXIST_ERROR, $info);
            }
        }

        // Post
        if ($markTarget == 4) {
            $posts = FresnsPosts::where('uuid', $markId)->where('is_enable', 1)->first();
            if (empty($posts)) {
                $info = [
                    'markId' => 'null',
                ];
                $this->error(ErrorCodeService::POST_EXIST_ERROR, $info);
            }
            $memberId = $posts['member_id'];
            $markId = $posts['id'];
            if ($memberId == $mid) {
                $this->error(ErrorCodeService::MARK_FOLLOW_ERROR);
            }
        }

        // Comment
        if ($markTarget == 5) {
            $comment = FresnsComments::where('uuid', $markId)->where('is_enable', 1)->first();
            if (empty($comment)) {
                $info = [
                    'markId' => 'null',
                ];
                $this->error(ErrorCodeService::COMMENT_EXIST_ERROR, $info);
            }
            $memberId = $comment['member_id'];
            $markId = $comment['id'];
            if ($memberId == $mid) {
                $this->error(ErrorCodeService::MARK_FOLLOW_ERROR);
            }
        }

        // Checking for duplicate operations
        switch ($type) {
            case 1:
                $checkMark = FsChecker::checkMark($markType, $markTarget, $mid, $markId);
                if ($checkMark === true) {
                    $this->error(ErrorCodeService::MARK_REPEAT_ERROR);
                }
                break;

            default:
                $checkMark = FsChecker::checkMark($markType, $markTarget, $mid, $markId);
                if ($checkMark === false) {
                    $this->error(ErrorCodeService::MARK_REPEAT_ERROR);
                }
                break;
        }

        switch ($markTarget) {
            case 1:
                switch ($markType) {
                    case 1:
                        if ($type == 1) {
                            FresnsMemberLikesService::addMemberLike($mid, $markTarget, $markId, 'like_member_count', 'like_me_count');
                            // Enter a notification to the other party
                            FresnsNotifiesService::markNotifies($markId, $mid, 3, $markTarget, 'Like');
                        } else {
                            FresnsMemberLikesService::deleMemberLike($mid, $markTarget, $markId);
                            DB::table(FresnsMemberStatsConfig::CFG_TABLE)->where('member_id', $mid)->decrement('like_member_count');
                            DB::table(FresnsMemberStatsConfig::CFG_TABLE)->where('member_id', $markId)->decrement('like_me_count');
                        }
                        break;
                    case 2:
                        $memberFollows = FresnsMemberFollows::where('follow_id', $mid)->where('member_id', $markId)->first();
                        if ($type == 1) {
                            FresnsMemberFollowsService::addMemberFollow($mid, $markTarget, $markId);
                            if ($memberFollows) {
                                FresnsMemberFollows::where('id', $memberFollows['id'])->update(['is_mutual' => 1]);
                                FresnsMemberFollows::where('member_id', $mid)->where('follow_type', $markTarget)->where('follow_id', $markId)->update(['is_mutual' => 1]);
                            }

                            DB::table(FresnsMemberStatsConfig::CFG_TABLE)->where('member_id', $mid)->increment('follow_member_count');
                            DB::table(FresnsMemberStatsConfig::CFG_TABLE)->where('member_id', $markId)->increment('follow_me_count');
                            // Enter a notification to the other party
                            FresnsNotifiesService::markNotifies($markId, $mid, 2, $markTarget, 'Follow');
                        } else {
                            FresnsMemberFollowsService::deleMemberFollow($mid, $markTarget, $markId);
                            FresnsMemberFollows::where('member_id', $markId)->where('follow_type', $markTarget)->where('follow_id', $mid)->update(['is_mutual' => 0]);
                            DB::table(FresnsMemberStatsConfig::CFG_TABLE)->where('member_id', $mid)->decrement('follow_member_count');
                            DB::table(FresnsMemberStatsConfig::CFG_TABLE)->where('member_id', $markId)->decrement('follow_me_count');
                        }
                        break;
                    default:
                        if ($type == 1) {
                            FresnsMemberShieldsService::addMemberShield($mid, $markTarget, $markId);
                            DB::table(FresnsMemberStatsConfig::CFG_TABLE)->where('member_id', $mid)->increment('shield_member_count');
                            DB::table(FresnsMemberStatsConfig::CFG_TABLE)->where('member_id', $markId)->increment('shield_me_count');
                        } else {
                            FresnsMemberShieldsService::deleMemberShield($mid, $markTarget, $markId);
                            DB::table(FresnsMemberStatsConfig::CFG_TABLE)->where('member_id', $mid)->decrement('shield_member_count');
                            DB::table(FresnsMemberStatsConfig::CFG_TABLE)->where('member_id', $markId)->decrement('shield_me_count');
                        }
                        break;
                }

                break;
            case 2:
                switch ($markType) {
                    case 1:
                        if ($type == 1) {
                            FresnsMemberLikesService::addMemberLike($mid, $markTarget, $markId);
                            FresnsGroups::where('id', $markId)->increment('like_count');
                        } else {
                            FresnsMemberLikesService::deleMemberLike($mid, $markTarget, $markId);
                            FresnsGroups::where('id', $markId)->decrement('like_count');
                        }
                        break;
                    case 2:
                        if ($type == 1) {
                            FresnsMemberFollowsService::addMemberFollow($mid, $markTarget, $markId);
                            FresnsGroups::where('id', $markId)->increment('follow_count');
                        } else {
                            FresnsMemberFollowsService::deleMemberFollow($mid, $markTarget, $markId);
                            FresnsGroups::where('id', $markId)->decrement('follow_count');
                        }

                        break;
                    default:
                        if ($type == 1) {
                            FresnsMemberShieldsService::addMemberShield($mid, $markTarget, $markId);
                            DB::table(FresnsGroupsConfig::CFG_TABLE)->where('id', $markId)->increment('shield_count');
                        } else {
                            FresnsMemberShieldsService::deleMemberShield($mid, $markTarget, $markId);
                            DB::table(FresnsGroupsConfig::CFG_TABLE)->where('id', $markId)->decrement('shield_count');
                        }
                        break;
                }
                break;
            case 3:
                switch ($markType) {
                    case 1:
                        if ($type == 1) {
                            FresnsMemberLikesService::addMemberLike($mid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->increment('like_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::addMarkCounts('hashtag_like_counts');
                        } else {
                            FresnsMemberLikesService::deleMemberLike($mid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->decrement('like_count');
                            FresnsConfigsService::minusMarkCounts('hashtag_like_counts');
                        }
                        break;
                    case 2:
                        if ($type == 1) {
                            FresnsMemberFollowsService::addMemberFollow($mid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->increment('follow_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::addMarkCounts('hashtag_follow_counts');
                        } else {
                            FresnsMemberFollowsService::deleMemberFollow($mid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->decrement('follow_count');
                            FresnsConfigsService::minusMarkCounts('hashtag_follow_counts');
                        }
                        break;
                    default:
                        if ($type == 1) {
                            FresnsMemberShieldsService::addMemberShield($mid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->increment('shield_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::addMarkCounts('hashtag_shield_counts');
                        } else {
                            FresnsMemberShieldsService::deleMemberShield($mid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->decrement('shield_count');
                            FresnsConfigsService::minusMarkCounts('hashtag_shield_counts');
                        }
                        break;
                }
                break;
            case 4:
                switch ($markType) {
                    case 1:
                        if ($type == 1) {
                            FresnsMemberLikesService::addMemberLike($mid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->increment('like_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::addMarkCounts('post_like_counts');
                            // Insert a notice
                            $post = FresnsPosts::where('id', $markId)->first();
                            FresnsNotifiesService::markNotifies($post['member_id'], $mid, 3, $markTarget, $post['title'], 1, $markId);
                        } else {
                            FresnsMemberLikesService::deleMemberLike($mid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->decrement('like_count');
                            FresnsConfigsService::minusMarkCounts('post_like_counts');
                        }
                        break;
                    case 2:
                        if ($type == 1) {
                            FresnsMemberFollowsService::addMemberFollow($mid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->increment('follow_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::addMarkCounts('post_follow_counts');
                            // Insert a notice
                            $post = FresnsPosts::where('id', $markId)->first();
                            FresnsNotifiesService::markNotifies($post['member_id'], $mid, 2, $markTarget, $post['title'], 1, $markId);
                        } else {
                            FresnsMemberFollowsService::deleMemberFollow($mid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->decrement('follow_count');
                            FresnsConfigsService::minusMarkCounts('post_follow_counts');
                        }
                        break;
                    default:
                        if ($type == 1) {
                            FresnsMemberShieldsService::addMemberShield($mid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->increment('shield_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::addMarkCounts('post_shield_counts');
                        } else {
                            FresnsMemberShieldsService::deleMemberShield($mid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->decrement('shield_count');
                            FresnsConfigsService::minusMarkCounts('post_shield_counts');
                        }
                        break;
                }
                break;
            default:
                $comment = FresnsComments::where('id', $markId)->first();
                switch ($markType) {
                    case 1:
                        if ($type == 1) {
                            FresnsMemberLikesService::addMemberLike($mid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->increment('like_count');
                            FresnsPosts::where('id', $comment['post_id'])->increment('comment_like_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::addMarkCounts('comment_like_counts');
                            if ($comment['parent_id'] > 0) {
                                FresnsComments::where('id', $comment['parent_id'])->increment('comment_like_count');
                            }
                            // Insert a notice
                            FresnsNotifiesService::markNotifies($comment['member_id'], $mid, 3, $markTarget,
                                $comment['content'], 2, $markId);
                        } else {
                            FresnsMemberLikesService::deleMemberLike($mid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->decrement('like_count');
                            FresnsPosts::where('id', $comment['post_id'])->decrement('comment_like_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::minusMarkCounts('comment_like_counts');
                            if ($comment['parent_id'] > 0) {
                                FresnsComments::where('id', $comment['parent_id'])->decrement('comment_like_count');
                            }
                        }
                        break;
                    case 2:
                        if ($type == 1) {
                            FresnsMemberFollowsService::addMemberFollow($mid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->increment('follow_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::addMarkCounts('comment_follow_counts');
                            // Insert a notice
                            FresnsNotifiesService::markNotifies($comment['member_id'], $mid, 2, $markTarget,
                                $comment['content'], 2, $markId);
                        } else {
                            FresnsMemberFollowsService::deleMemberFollow($mid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->decrement('follow_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::minusMarkCounts('comment_follow_counts');
                        }
                        break;
                    default:
                        if ($type == 1) {
                            FresnsMemberShieldsService::addMemberShield($mid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->increment('shield_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::addMarkCounts('comment_shield_counts');
                        } else {
                            FresnsMemberShieldsService::deleMemberShield($mid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->decrement('shield_count');
                            // Inserting data into the configs table
                            FresnsConfigsService::minusMarkCounts('comment_shield_counts');
                        }
                        break;
                }
                break;
        }

        $this->success();
    }

    // Member Mark Data List
    public function markLists(Request $request)
    {
        $rule = [
            'viewType' => 'required|numeric|in:1,2,3',
            'viewTarget' => 'required|numeric|in:1,2,3,4,5',
            'pageSize' => 'numeric',
            'page' => 'numeric',
        ];
        ValidateService::validateRule($request, $rule);

        $viewTarget = $request->input('viewTarget');
        $pageSize = $request->input('pageSize', 30);
        $page = $request->input('page', 1);
        $viewMid = $request->input('viewMid');
        $viewMname = $request->input('viewMname');
        $viewType = $request->input('viewType');

        $data = [];
        if (empty($viewMid) && empty($viewMname)) {
            $info = [
                'null body' => 'mid or mname is empty',
            ];
            $this->error(ErrorCodeService::MEMBER_CHECK_ERROR, $info);
        }
        if (empty($viewMid)) {
            $mid = FresnsMembers::where('name', $viewMname)->value('id');
        } else {
            $mid = FresnsMembers::where('uuid', $viewMid)->value('id');
        }

        if (empty($mid)) {
            $info = [
                'null member' => 'mid or mname',
            ];
            $this->error(ErrorCodeService::MEMBER_CHECK_ERROR, $info);
        }

        $authMemberId = GlobalService::getGlobalKey('member_id');
        /**
         * Whether to output data when viewing other people's information
         * https://fresns.org/database/keyname/interactives.html
         * View other people's content settings.
         */
        if ($mid != $authMemberId) {
            $isMarkLists = FsChecker::checkMarkLists($viewType, $viewTarget);
            if ($isMarkLists == false) {
                $this->error(ErrorCodeService::POST_BROWSE_ERROR);
            }
        }

        $request->offsetSet('viewMid', $mid);

        switch ($viewTarget) {
            case 1:
                $itLikeMembers = ApiConfigHelper::getConfigByItemKey('it_like_members');
                $data = FsService::getMemberList($request);
                if ($itLikeMembers == false) {
                    $data['list'] = [];
                    $data['pagination']['current'] = 1;
                    $data['pagination']['lastPage'] = 1;
                    $data['pagination']['total'] = 0;
                }
                break;
            case 2:
                $groupArr = FsService::getGroupList($request);
                $groupIds = implode(',', $groupArr);

                $FresnsDialogsService = new FresnsGroupsService();
                $request->offsetSet('ids', $groupIds);
                $request->offsetSet('currentPage', $page);
                $request->offsetSet('pageSize', $pageSize);
                $FresnsDialogsService->setResource(FresnsGroupsResource::class);
                $data = $FresnsDialogsService->searchData();

                break;
            case 3:
                $hashtagArr = FsService::getHashtagList($request);
                $hashtagIds = implode(',', $hashtagArr);
                $FresnsHashtagsService = new FresnsHashtagsService();
                $request->offsetSet('ids', $hashtagIds);
                $request->offsetSet('currentPage', $page);
                $request->offsetSet('pageSize', $pageSize);
                $FresnsHashtagsService->setResource(FresnsHashtagsResource::class);
                $data = $FresnsHashtagsService->searchData();
                break;
            case 4:
                $postArr = FsService::getPostList($request);
                $postIds = implode(',', $postArr);
                $FresnsPostsService = new FresnsPostsService();
                $request->offsetSet('ids', $postIds);
                $request->offsetSet('currentPage', $page);
                $request->offsetSet('pageSize', $pageSize);
                $FresnsPostsService->setResource(FresnsPostsResource::class);
                $data = $FresnsPostsService->searchData();
                break;
            default:
                $commentArr = FsService::getCommentList($request);
                $commentIds = implode(',', $commentArr);
                $FresnsCommentsService = new FresnsCommentsService();
                $request->offsetSet('ids', $commentIds);
                $request->offsetSet('currentPage', $page);
                $request->offsetSet('pageSize', $pageSize);
                $FresnsCommentsService->setResource(FresnsCommentsResource::class);
                $data = $FresnsCommentsService->searchData();
                break;
        }

        $this->success($data);
    }

    /**
     * Member Operation Delete Content
     * Delete all to verify that the author of the post or comment is me
     * You need to verify that the member has the right to delete, there are some contents that may not be allowed to be deleted, query the can_delete field in the dependent information table.
     */
    public function delete(Request $request)
    {
        $rule = [
            'type' => 'required|numeric|in:1,2',
            'uuid' => 'required',
        ];
        ValidateService::validateRule($request, $rule);

        $mid = GlobalService::getGlobalKey('member_id');

        $uuid = $request->input('uuid');
        $type = $request->input('type');
        switch ($type) {
            case 1:
                $posts = FresnsPosts::where('uuid', $uuid)->first();
                if (empty($posts)) {
                    $this->error(ErrorCodeService::DELETE_POST_ERROR);
                }
                if ($posts['member_id'] != $mid) {
                    $this->error(ErrorCodeService::MEMBER_NO_PERMISSION);
                }
                $postsAppend = FresnsPostAppends::where('post_id', $posts['id'])->first();
                if ($postsAppend['can_delete'] == 0) {
                    $this->error(ErrorCodeService::MEMBER_NO_PERMISSION);
                }
                FresnsPosts::where('id', $posts['id'])->delete();
                FresnsPostAppends::where('id', $postsAppend['id'])->delete();
                break;

            default:
                $comments = FresnsComments::where('uuid', $uuid)->first();

                if (empty($comments)) {
                    $this->error(ErrorCodeService::DELETE_COMMENT_ERROR);
                }
                if ($comments['member_id'] != $mid) {
                    $this->error(ErrorCodeService::MEMBER_NO_PERMISSION);
                }
                $commentsAppend = FresnsCommentAppends::where('comment_id', $comments['id'])->first();
                if (! empty($commentsAppend)) {
                    if ($commentsAppend['can_delete'] == 0) {
                        $this->error(ErrorCodeService::MEMBER_NO_PERMISSION);
                    }
                }

                FresnsComments::where('id', $comments['id'])->delete();
                FresnsMemberStats::where('member_id', $mid)->decrement('comment_publish_count');
                FresnsConfigs::where('item_key', 'comment_counts')->decrement('item_value');
                if (! empty($commentsAppend['id'])) {
                    FresnsCommentAppends::where('id', $commentsAppend['id'])->delete();
                }
                break;
        }

        $this->success();
    }
}
