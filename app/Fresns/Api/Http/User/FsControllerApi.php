<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\User;

use App\Fresns\Api\Helpers\DateHelper;
use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\Center\Common\ValidateService;
use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\Http\Base\FsApiController;
use App\Fresns\Api\Http\Content\FresnsCommentsResource;
use App\Fresns\Api\Http\Content\FresnsGroupsResource;
use App\Fresns\Api\Http\Content\FresnsHashtagsResource;
use App\Fresns\Api\Http\Content\FresnsPostsResource;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Helpers\ApiLanguageHelper;
use App\Fresns\Api\FsCmd\FresnsCmdWords;
use App\Fresns\Api\FsCmd\FresnsCmdWordsConfig;
use App\Fresns\Api\FsDb\FresnsCommentAppends\FresnsCommentAppends;
use App\Fresns\Api\FsDb\FresnsComments\FresnsComments;
use App\Fresns\Api\FsDb\FresnsComments\FresnsCommentsService;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigs;
use App\Fresns\Api\FsDb\FresnsConfigs\FresnsConfigsConfig;
use App\Fresns\Api\FsDb\FresnsFileLogs\FresnsFileLogsConfig;
use App\Fresns\Api\FsDb\FresnsFiles\FresnsFiles;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroups;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroupsConfig;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroupsService;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtags;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtagsService;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollows;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollowsService;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikes;
use App\Fresns\Api\FsDb\FresnsUserLikes\FresnsUserLikesService;
use App\Fresns\Api\FsDb\FresnsRoles\FresnsRolesService;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocks;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocksService;
use App\Fresns\Api\FsDb\FresnsUserStats\FresnsUserStats;
use App\Fresns\Api\FsDb\FresnsUserStats\FresnsUserStatsConfig;
use App\Fresns\Api\FsDb\FresnsNotifies\FresnsNotifiesService;
use App\Fresns\Api\FsDb\FresnsPostAppends\FresnsPostAppends;
use App\Fresns\Api\FsDb\FresnsPostUsers\FresnsPostUsersConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPosts;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsService;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogs;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogsConfig;
use App\Fresns\Api\FsDb\FresnsSessionLogs\FresnsSessionLogsService;
use App\Fresns\Api\FsDb\FresnsBlockWords\FresnsBlockWords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FsControllerApi extends FsApiController
{
    public function __construct()
    {
        parent::__construct();

        $this->service = new FsService();
        $this->initData();
    }

    // User Login
    public function auth(Request $request)
    {
        $rule = [
            'uid' => 'required|numeric',
        ];
        ValidateService::validateRule($request, $rule);

        $aid = GlobalService::getGlobalKey('account_id');

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

        $uid = $request->input('uid');
        $uid = FresnsUsers::where('uid', $uid)->value('id');

        $checkUser = FsChecker::checkAccountUser($uid, $aid);
        if ($checkUser == false) {
            $this->error(ErrorCodeService::USER_FAIL);
        }

        $sessionLogId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionLogId) {
            $sessionInput = [
                'object_order_id' => $uid,
                'account_id' => $aid,
                'user_id' => $uid,
            ];
            FresnsSessionLogs::where('id', $sessionLogId)->update($sessionInput);
        }

        // Check the number of login password errors in the last 1 hour for the user to whom the email or cell phone number belongs.
        // If it reaches 5 times, the login will be restricted.
        // session_logs > object_type=7
        $startTime = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $sessionCount = FresnsSessionLogs::where('created_at', '>=', $startTime)
        ->where('account_id', $aid)
        ->where('user_id', $uid)
        ->where('object_result', FresnsSessionLogsConfig::OBJECT_RESULT_ERROR)
        ->where('object_type', FresnsSessionLogsConfig::OBJECT_TYPE_USER_LOGIN)
        ->count();

        if ($sessionCount >= 5) {
            $this->error(ErrorCodeService::ACCOUNT_COUNT_ERROR);
        }

        $user = FresnsUsers::where('id', $uid)->first();
        if (! empty($user['password'])) {
            if (! Hash::check($password, $user['password'])) {
                $this->error(ErrorCodeService::USER_PASSWORD_INVALID);
            }
        }
        $langTag = ApiLanguageHelper::getLangTagByHeader();

        $request->offsetSet('langTag', $langTag);
        $request->offsetSet('uid', $uid);

        $data = $this->service->getUserDetail($uid, $uid, true, $langTag);
        if ($data) {
            $cmd = FresnsCmdWordsConfig::FRESNS_CMD_CREATE_SESSION_TOKEN;
            $input['aid'] = $request->header('aid');
            $input['platform'] = $request->header('platform');
            $input['uid'] = $user['uid'];
            $resp = CmdRpcHelper::call(FresnsCmdWords::class, $cmd, $input);
            if (CmdRpcHelper::isErrorCmdResp($resp)) {
                $this->errorCheckInfo($resp);
            }
            $output = $resp['output'];
            $data['token'] = $output['token'] ?? null;
            $data['tokenExpiredTime'] = $output['tokenExpiredTime'] ?? null;
        }

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $aid, $uid, $uid);
        }

        $this->success($data);
    }

    // User Detail
    public function detail(Request $request)
    {
        $uid = GlobalService::getGlobalKey('user_id');
        $viewUid = $request->input('viewUid');
        $viewUsername = $request->input('viewUsername');
        $langTag = ApiLanguageHelper::getLangTagByHeader();

        $request->offsetSet('langTag', $langTag);
        $request->offsetSet('uid', $uid);
        if (empty($viewUid)) {
            $viewUid = FresnsUsers::where('username', $viewUsername)->value('id');
        } else {
            $viewUid = FresnsUsers::where('uid', $viewUid)->value('id');
        }

        if (empty($viewUid)) {
            $this->error(ErrorCodeService::ACCOUNT_CHECK_ERROR);
        }

        // Is it me
        $isMe = false;
        if ($uid == $viewUid) {
            $isMe = true;
        }

        $data['common'] = $this->service->common($viewUid, $langTag, $isMe, $uid);
        $data['detail'] = $this->service->getUserDetail($uid, $viewUid, $isMe, $langTag);

        $this->success($data);
    }

    // User List
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
        $query = DB::table('users as me');
        $query = $query->select('me.*')->leftJoin('user_stats as st', 'me.id', '=', 'st.user_id');

        if ($searchKey) {
            $userIdArr1 = FresnsUsers::where('name', 'LIKE', "%$searchKey%")->pluck('id')->toArray();
            $userIdArr2 = FresnsUsers::where('nickname', 'LIKE', "%$searchKey%")->pluck('id')->toArray();
            $idArr = array_unique(array_merge($userIdArr1, $userIdArr2));
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
            case 'block':
                $query->orderBy('st.block_me_count', $sortDirection);
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
        $data['list'] = FresnsUserListsResource::collection($item->items())->toArray($item->items());
        $pagination['total'] = $item->total();
        $pagination['current'] = $page;
        $pagination['pageSize'] = $pageSize;
        $pagination['lastPage'] = $item->lastPage();

        $data['pagination'] = $pagination;
        $this->success($data);
    }

    // User Edit Profile
    public function edit(Request $request)
    {
        $rule = [
            'gender' => 'numeric|in:0,1,2',
            'dialogLimit' => 'numeric',
            'birthday' => 'date_format:"Y-m-d H:i:s"',
        ];
        ValidateService::validateRule($request, $rule);

        $aid = GlobalService::getGlobalKey('account_id');
        $uid = GlobalService::getGlobalKey('user_id');

        $checkUser = FsChecker::checkAccountUser($uid, $aid);
        if ($checkUser == false) {
            $this->error(ErrorCodeService::USER_FAIL);
        }

        $username = $request->input('username');
        $nickname = $request->input('nickname');
        $avatarFid = $request->input('avatarFid');
        $bio = $request->input('bio');
        $user = FresnsUsers::where('id', $uid)->first();
        if (empty($user)) {
            $this->error(ErrorCodeService::USER_CHECK_ERROR);
        }

        $last_username_at = $user['last_username_at'];
        if ($username) {
            $itemValue = FresnsConfigs::where('item_key', FresnsConfigsConfig::USERNAME_EDIT)->value('item_value');
            if ($itemValue > 0) {
                if ($last_username_at) {
                    $begin_date = strtotime($last_username_at);
                    $end_date = strtotime(date('Y-m-d', time()));
                    $days = round(($end_date - $begin_date) / 3600 / 24);
                    if ($days <= $itemValue) {
                        $this->error(ErrorCodeService::UPDATE_TIME_ERROR);
                    }
                }
            }

            $isError = preg_match('/^[A-Za-z0-9-]+$/', $username);
            if ($isError == 0) {
                $this->error(ErrorCodeService::USER_NAME_ERROR);
            }

            $isNumeric = is_numeric($username);
            if ($isNumeric == true) {
                $this->error(ErrorCodeService::USER_NAME_ERROR);
            }

            $substrCount = substr_count($username, '-');
            if ($substrCount > 1) {
                $this->error(ErrorCodeService::USER_NAME_ERROR);
            }

            $usernameMin = FresnsConfigs::where('item_key', FresnsConfigsConfig::USERNAME_MIN)->value('item_value');
            $usernameMax = FresnsConfigs::where('item_key', FresnsConfigsConfig::USERNAME_MAX)->value('item_value');
            $count = strlen($username);
            if ($count < $usernameMin) {
                $this->error(ErrorCodeService::USER_NAME_LENGTH_ERROR);
            }
            if ($count > $usernameMax) {
                $this->error(ErrorCodeService::USER_NAME_LENGTH_ERROR);
            }

            $disableNames = FresnsConfigs::where('item_key', 'disable_names')->value('item_value');
            $disableNamesArr = json_decode($disableNames, true);
            if (in_array($username, $disableNamesArr)) {
                $this->error(ErrorCodeService::DISABLE_NAME_ERROR);
            }
            // Determine if the name is duplicated
            $userCount = FresnsUsers::where('username', $username)->count();

            if ($userCount > 0) {
                $this->error(ErrorCodeService::USER_NAME_USED_ERROR);
            }
        }

        $last_nickname_at = $user['last_nickname_at'];
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

            trim($nickname);
            $isError = preg_match('/^[\x{4e00}-\x{9fa5} A-Za-z0-9]+$/u', $nickname);
            if ($isError == 0) {
                $this->error(ErrorCodeService::USER_NICKNAME_ERROR);
            }
            $nicknameExplodeArr = explode(' ', $nickname);
            $nicknameArr = [];
            foreach ($nicknameExplodeArr as $v) {
                if (empty($v)) {
                    continue;
                }
                $nicknameArr[] = $v;
            }

            $nickname = implode(' ', $nicknameArr);

            $count = strlen($nickname);
            if ($count > 64) {
                $this->error(ErrorCodeService::USER_NICKNAME_LENGTH_ERROR);
            }

            $blockWordsArr = FresnsBlockWords::get()->toArray();

            foreach ($blockWordsArr as $v) {
                $str = strstr($nickname, $v['word']);
                if ($str != false) {
                    if ($v['user_mode'] == 2) {
                        $nickname = str_replace($v['word'], $v['replace_word'], $nickname);
                        $request->offsetSet('nickname', $nickname);
                    }
                    if ($v['user_mode'] == 3) {
                        $this->error(ErrorCodeService::UPDATE_TIME_ERROR);
                    }
                }
            }
        }

        if ($avatarFid) {
            $avatarFileId = FresnsFiles::where('fid', $avatarFid)->value('id');
            FresnsUsers::where('id', $uid)->update(['avatar_file_id' => $avatarFileId]);
        }

        if ($bio) {
            $blockWordsArr = FresnsBlockWords::get()->toArray();

            foreach ($blockWordsArr as $v) {
                $str = strstr($bio, $v['word']);
                if ($str != false) {
                    if ($v['user_mode'] == 2) {
                        $bio = str_replace($v['word'], $v['replace_word'], $bio);
                        $request->offsetSet('bio', $bio);
                    }
                    if ($v['user_mode'] == 3) {
                        $this->error(ErrorCodeService::UPDATE_TIME_ERROR);
                    }
                }
            }
        }
        $map = FsConfig::USER_EDIT;

        $itemArr = [];
        foreach ($map as $k => $v) {
            $req = $request->input($k);
            if ($req) {
                $itemArr[$v] = $req;
            }
        }

        if ($itemArr) {
            FresnsUsers::where('id', $uid)->update($itemArr);
        }

        if ($username) {
            $input = [
                'last_username_at' => date('Y-m-d H:i:s', time()),
            ];
            FresnsUsers::where('id', $uid)->update($input);
        }

        if ($nickname) {
            $input = [
                'last_nickname_at' => date('Y-m-d H:i:s', time()),
            ];
            FresnsUsers::where('id', $uid)->update($input);
        }

        $sessionId = GlobalService::getGlobalSessionKey('session_log_id');
        if ($sessionId) {
            FresnsSessionLogsService::updateSessionLogs($sessionId, 2, $aid, $uid, $uid);
        }

        $this->success((object) []);
    }

    // Get User Role List
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
        $fresnsRolesService = new FresnsRolesService();
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('pageSize', $pageSize);
        $data = $fresnsRolesService->searchData();
        $this->success($data);
    }

    // Get User Interactions Data
    public function interactions(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2,3,4,5',
            'objectType' => 'numeric|in:1,2,3,4,5',
            'objectFsid' => 'required',
            'sortDirection' => 'numeric',
            'pageSize' => 'numeric',
            'page' => 'numeric',
        ];
        ValidateService::validateRule($request, $rule);

        $type = $request->input('type');
        $objectType = $request->input('objectType', 1);
        $objectFsid = $request->input('objectFsid');
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
                $this->error(ErrorCodeService::USER_NO_PERMISSION);
            }
        }

        $idArr = [];

        /**
         * Whether to output data when viewing other people's information
         * https://fresns.org/database/keyname/interactives.html
         * View other people's content settings.
         *
         * type=1 Get a list of all users liked by objectType > objectFsid (query user_likes table)
         * type=2 Get a list of all users followed by objectType > objectFsid (query user_follows table)
         * type=3 Get a list of all users blocked by objectType > objectFsid (query user_blocks table)
         */
        switch ($type) {
            case 1:
                $likeId = 0;
                switch ($objectType) {
                    case 1:
                        $it_likers = ApiConfigHelper::getConfigByItemKey('it_likers');
                        if ($it_likers == true) {
                            $likeId = FresnsUsers::where('uid', $objectFsid)->value('id');
                        }
                        break;
                    case 2:
                        $it_like_groups = ApiConfigHelper::getConfigByItemKey('it_like_groups');
                        if ($it_like_groups == true) {
                            $likeId = FresnsGroups::where('gid', $objectFsid)->value('id');
                        }
                        break;
                    case 3:
                        $it_like_hashtags = ApiConfigHelper::getConfigByItemKey('it_like_hashtags');
                        if ($it_like_hashtags == true) {
                            $likeId = FresnsHashtags::where('slug', $objectFsid)->value('id');
                        }
                        break;
                    case 4:
                        $it_like_posts = ApiConfigHelper::getConfigByItemKey('it_like_posts');
                        if ($it_like_posts == true) {
                            $likeId = FresnsPosts::where('pid', $objectFsid)->value('id');
                        }
                        break;
                    default:
                        $it_like_comments = ApiConfigHelper::getConfigByItemKey('it_like_comments');
                        if ($it_like_comments == true) {
                            $likeId = FresnsComments::where('cid', $objectFsid)->value('id');
                        }
                        break;
                }
                $idArr = FresnsUserLikes::where('like_type', $objectType)->where('like_id', $likeId)->pluck('user_id')->toArray();
                break;
            case 2:
                $followId = 0;
                switch ($objectType) {
                    case 1:
                        $it_followers = ApiConfigHelper::getConfigByItemKey('it_followers');
                        if ($it_followers == true) {
                            $followId = FresnsUsers::where('uid', $objectFsid)->value('id');
                        }
                        break;
                    case 2:
                        $it_follow_groups = ApiConfigHelper::getConfigByItemKey('it_follow_groups');
                        if ($it_follow_groups == true) {
                            $followId = FresnsGroups::where('gid', $objectFsid)->value('id');
                        }
                        break;
                    case 3:
                        $it_follow_hashtags = ApiConfigHelper::getConfigByItemKey('it_follow_hashtags');
                        if ($it_follow_hashtags == true) {
                            $followId = FresnsHashtags::where('slug', $objectFsid)->value('id');
                        }
                        break;
                    case 4:
                        $it_follow_posts = ApiConfigHelper::getConfigByItemKey('it_follow_posts');
                        if ($it_follow_posts == true) {
                            $followId = FresnsPosts::where('pid', $objectFsid)->value('id');
                        }
                        break;
                    default:
                        $it_follow_comments = ApiConfigHelper::getConfigByItemKey('it_follow_comments');
                        if ($it_follow_comments == true) {
                            $followId = FresnsComments::where('cid', $objectFsid)->value('id');
                        }
                        break;
                }
                $idArr = FresnsUserFollows::where('follow_type', $objectType)->where('follow_id', $followId)->pluck('user_id')->toArray();
                break;
            case 3:
                $blockId = 0;
                switch ($objectType) {
                    case 1:
                        $it_blockers = ApiConfigHelper::getConfigByItemKey('it_blockers');
                        if ($it_blockers == true) {
                            $blockId = FresnsUsers::where('uid', $objectFsid)->value('id');
                        }
                        break;
                    case 2:
                        $it_block_groups = ApiConfigHelper::getConfigByItemKey('it_block_groups');
                        if ($it_block_groups == true) {
                            $blockId = FresnsGroups::where('gid', $objectFsid)->value('id');
                        }
                        break;
                    case 3:
                        $it_block_hashtags = ApiConfigHelper::getConfigByItemKey('it_block_hashtags');
                        if ($it_block_hashtags == true) {
                            $blockId = FresnsHashtags::where('slug', $objectFsid)->value('id');
                        }
                        break;
                    case 4:
                        $it_block_posts = ApiConfigHelper::getConfigByItemKey('it_block_posts');
                        if ($it_block_posts == true) {
                            $blockId = FresnsPosts::where('pid', $objectFsid)->value('id');
                        }
                        break;
                    default:
                        $it_block_comments = ApiConfigHelper::getConfigByItemKey('it_block_comments');
                        if ($it_block_comments == true) {
                            $blockId = FresnsComments::where('cid', $objectFsid)->value('id');
                        }
                        break;
                }
                $idArr = FresnsUserBlocks::where('block_type', $objectType)->where('block_id', $blockId)->pluck('user_id')->toArray();
                break;
            case 4:
                if ($objectType == 4) {
                    $postPidArr = FresnsPosts::where('pid', $objectFsid)->pluck('id')->toArray();
                    $idArr = DB::table(FresnsPostUsersConfig::CFG_TABLE)->whereIn('post_id', $postPidArr)->pluck('user_id')->toArray();
                }
                break;
            default:
                $idArr = DB::table(FresnsFileLogsConfig::CFG_TABLE)->where('file_id', $objectFsid)->pluck('user_id')->toArray();
                break;
        }

        $query = DB::table('users as me');
        $query = $query->select('me.*')->leftJoin('user_stats as st', 'me.id', '=', 'st.user_id');

        $query->whereIn('me.id', $idArr);

        $sortDirection = $sortDirection == 1 ? 'ASC' : 'DESC';
        $query->orderBy('me.created_at', $sortDirection);

        $item = $query->paginate($pageSize, ['*'], 'page', $page);
        $list = FresnsUserListsResource::collection($item->items())->toArray($item->items());
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
        $uid = GlobalService::getGlobalKey('user_id');
        // Private mode, if the user has expired, no operation is allowed
        $siteMode = ApiConfigHelper::getConfigByItemKey('site_mode');
        if ($siteMode == 'private') {
            $uidUser = FresnsUsers::where('id', $uid)->first();
            if (! empty($uidUser['expired_at'])) {
                $time = date('Y-m-d H:i:s', time());
                if ($time > $uidUser['expired_at']) {
                    $this->error(ErrorCodeService::USER_EXPIRED_ERROR);
                }
            }
        }

        /**
         * Whether the right to operate
         * https://fresns.org/database/keyname/interactives.html
         * Interactive behavior settings.
         *
         * Tag users can not be themselves, as well as their own published posts, comments
         */
        $checkerApi = FsChecker::checkMarkApi($markType, $markTarget);
        if ($checkerApi == false) {
            $this->error(ErrorCodeService::MARK_NOT_ENABLE);
        }

        // User
        if ($markTarget == 1) {
            $markId = FresnsUsers::where('uid', $markId)->where('is_enable', 1)->value('id');
            if (empty($markId)) {
                $info = [
                    'markId' => 'null',
                ];
                $this->error(ErrorCodeService::USER_CHECK_ERROR, $info);
            }
            if ($markId == $uid) {
                $this->error(ErrorCodeService::MARK_FOLLOW_ERROR);
            }
        }

        // Group
        // If groups > type_follow = 2, you cannot create a following by this function.
        if ($markTarget == 2) {
            $groups = FresnsGroups::where('gid', $markId)->where('is_enable', 1)->first();
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
            $posts = FresnsPosts::where('pid', $markId)->where('is_enable', 1)->first();
            if (empty($posts)) {
                $info = [
                    'markId' => 'null',
                ];
                $this->error(ErrorCodeService::POST_EXIST_ERROR, $info);
            }
            $userId = $posts['user_id'];
            $markId = $posts['id'];
            if ($userId == $uid) {
                $this->error(ErrorCodeService::MARK_FOLLOW_ERROR);
            }
        }

        // Comment
        if ($markTarget == 5) {
            $comment = FresnsComments::where('cid', $markId)->where('is_enable', 1)->first();
            if (empty($comment)) {
                $info = [
                    'markId' => 'null',
                ];
                $this->error(ErrorCodeService::COMMENT_EXIST_ERROR, $info);
            }
            $userId = $comment['user_id'];
            $markId = $comment['id'];
            if ($userId == $uid) {
                $this->error(ErrorCodeService::MARK_FOLLOW_ERROR);
            }
        }

        // Checking for duplicate operations
        switch ($type) {
            case 1:
                $checkMark = FsChecker::checkMark($markType, $markTarget, $uid, $markId);
                if ($checkMark === true) {
                    $this->error(ErrorCodeService::MARK_REPEAT_ERROR);
                }
                break;

            default:
                $checkMark = FsChecker::checkMark($markType, $markTarget, $uid, $markId);
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
                            FresnsUserLikesService::addUserLike($uid, $markTarget, $markId, 'like_user_count', 'like_me_count');
                            // Enter a notification to the other party
                            FresnsNotifiesService::markNotifies($markId, $uid, 3, $markTarget, 'Like');
                        } else {
                            FresnsUserLikesService::deleUserLike($uid, $markTarget, $markId);
                            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $uid)->decrement('like_user_count');
                            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $markId)->decrement('like_me_count');
                        }
                        break;
                    case 2:
                        $userFollows = FresnsUserFollows::where('follow_id', $uid)->where('user_id', $markId)->first();
                        if ($type == 1) {
                            FresnsUserFollowsService::addUserFollow($uid, $markTarget, $markId);
                            if ($userFollows) {
                                FresnsUserFollows::where('id', $userFollows['id'])->update(['is_mutual' => 1]);
                                FresnsUserFollows::where('user_id', $uid)->where('follow_type', $markTarget)->where('follow_id', $markId)->update(['is_mutual' => 1]);
                            }

                            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $uid)->increment('follow_user_count');
                            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $markId)->increment('follow_me_count');
                            // Enter a notification to the other party
                            FresnsNotifiesService::markNotifies($markId, $uid, 2, $markTarget, 'Follow');
                        } else {
                            FresnsUserFollowsService::deleUserFollow($uid, $markTarget, $markId);
                            FresnsUserFollows::where('user_id', $markId)->where('follow_type', $markTarget)->where('follow_id', $uid)->update(['is_mutual' => 0]);
                            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $uid)->decrement('follow_user_count');
                            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $markId)->decrement('follow_me_count');
                        }
                        break;
                    default:
                        if ($type == 1) {
                            FresnsUserBlocksService::addUserBlock($uid, $markTarget, $markId);
                            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $uid)->increment('block_user_count');
                            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $markId)->increment('block_me_count');
                        } else {
                            FresnsUserBlocksService::deleUserBlock($uid, $markTarget, $markId);
                            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $uid)->decrement('block_user_count');
                            DB::table(FresnsUserStatsConfig::CFG_TABLE)->where('user_id', $markId)->decrement('block_me_count');
                        }
                        break;
                }

                break;
            case 2:
                switch ($markType) {
                    case 1:
                        if ($type == 1) {
                            FresnsUserLikesService::addUserLike($uid, $markTarget, $markId);
                            FresnsGroups::where('id', $markId)->increment('like_count');
                        } else {
                            FresnsUserLikesService::deleUserLike($uid, $markTarget, $markId);
                            FresnsGroups::where('id', $markId)->decrement('like_count');
                        }
                        break;
                    case 2:
                        if ($type == 1) {
                            FresnsUserFollowsService::addUserFollow($uid, $markTarget, $markId);
                            FresnsGroups::where('id', $markId)->increment('follow_count');
                        } else {
                            FresnsUserFollowsService::deleUserFollow($uid, $markTarget, $markId);
                            FresnsGroups::where('id', $markId)->decrement('follow_count');
                        }

                        break;
                    default:
                        if ($type == 1) {
                            FresnsUserBlocksService::addUserBlock($uid, $markTarget, $markId);
                            DB::table(FresnsGroupsConfig::CFG_TABLE)->where('id', $markId)->increment('block_count');
                        } else {
                            FresnsUserBlocksService::deleUserBlock($uid, $markTarget, $markId);
                            DB::table(FresnsGroupsConfig::CFG_TABLE)->where('id', $markId)->decrement('block_count');
                        }
                        break;
                }
                break;
            case 3:
                switch ($markType) {
                    case 1:
                        if ($type == 1) {
                            FresnsUserLikesService::addUserLike($uid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->increment('like_count');
                        } else {
                            FresnsUserLikesService::deleUserLike($uid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->decrement('like_count');
                        }
                        break;
                    case 2:
                        if ($type == 1) {
                            FresnsUserFollowsService::addUserFollow($uid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->increment('follow_count');
                        } else {
                            FresnsUserFollowsService::deleUserFollow($uid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->decrement('follow_count');
                        }
                        break;
                    default:
                        if ($type == 1) {
                            FresnsUserBlocksService::addUserBlock($uid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->increment('block_count');
                        } else {
                            FresnsUserBlocksService::deleUserBlock($uid, $markTarget, $markId);
                            FresnsHashtags::where('id', $markId)->decrement('block_count');
                        }
                        break;
                }
                break;
            case 4:
                switch ($markType) {
                    case 1:
                        if ($type == 1) {
                            FresnsUserLikesService::addUserLike($uid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->increment('like_count');
                            // Insert a notice
                            $post = FresnsPosts::where('id', $markId)->first();
                            FresnsNotifiesService::markNotifies($post['user_id'], $uid, 3, $markTarget, $post['title'], 1, $markId);
                        } else {
                            FresnsUserLikesService::deleUserLike($uid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->decrement('like_count');
                        }
                        break;
                    case 2:
                        if ($type == 1) {
                            FresnsUserFollowsService::addUserFollow($uid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->increment('follow_count');
                            // Insert a notice
                            $post = FresnsPosts::where('id', $markId)->first();
                            FresnsNotifiesService::markNotifies($post['user_id'], $uid, 2, $markTarget, $post['title'], 1, $markId);
                        } else {
                            FresnsUserFollowsService::deleUserFollow($uid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->decrement('follow_count');
                        }
                        break;
                    default:
                        if ($type == 1) {
                            FresnsUserBlocksService::addUserBlock($uid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->increment('block_count');
                        } else {
                            FresnsUserBlocksService::deleUserBlock($uid, $markTarget, $markId);
                            FresnsPosts::where('id', $markId)->decrement('block_count');
                        }
                        break;
                }
                break;
            default:
                $comment = FresnsComments::where('id', $markId)->first();
                switch ($markType) {
                    case 1:
                        if ($type == 1) {
                            FresnsUserLikesService::addUserLike($uid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->increment('like_count');
                            FresnsPosts::where('id', $comment['post_id'])->increment('comment_like_count');
                            if ($comment['parent_id'] > 0) {
                                FresnsComments::where('id', $comment['parent_id'])->increment('comment_like_count');
                            }
                            // Insert a notice
                            FresnsNotifiesService::markNotifies($comment['user_id'], $uid, 3, $markTarget,
                                $comment['content'], 2, $markId);
                        } else {
                            FresnsUserLikesService::deleUserLike($uid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->decrement('like_count');
                            FresnsPosts::where('id', $comment['post_id'])->decrement('comment_like_count');
                            if ($comment['parent_id'] > 0) {
                                FresnsComments::where('id', $comment['parent_id'])->decrement('comment_like_count');
                            }
                        }
                        break;
                    case 2:
                        if ($type == 1) {
                            FresnsUserFollowsService::addUserFollow($uid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->increment('follow_count');
                            // Insert a notice
                            FresnsNotifiesService::markNotifies($comment['user_id'], $uid, 2, $markTarget,
                                $comment['content'], 2, $markId);
                        } else {
                            FresnsUserFollowsService::deleUserFollow($uid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->decrement('follow_count');
                        }
                        break;
                    default:
                        if ($type == 1) {
                            FresnsUserBlocksService::addUserBlock($uid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->increment('block_count');
                        } else {
                            FresnsUserBlocksService::deleUserBlock($uid, $markTarget, $markId);
                            FresnsComments::where('id', $markId)->decrement('block_count');
                        }
                        break;
                }
                break;
        }

        $this->success();
    }

    // User Mark Data List
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
        $viewUid = $request->input('viewUid');
        $viewUsername = $request->input('viewUsername');
        $viewType = $request->input('viewType');

        $data = [];
        if (empty($viewUid) && empty($viewUsername)) {
            $info = [
                'null body' => 'uid or username is empty',
            ];
            $this->error(ErrorCodeService::USER_CHECK_ERROR, $info);
        }
        if (empty($viewUid)) {
            $uid = FresnsUsers::where('username', $viewUsername)->value('id');
        } else {
            $uid = FresnsUsers::where('uid', $viewUid)->value('id');
        }

        if (empty($uid)) {
            $info = [
                'null user' => 'uid or username',
            ];
            $this->error(ErrorCodeService::USER_CHECK_ERROR, $info);
        }

        $authUserId = GlobalService::getGlobalKey('user_id');
        /**
         * Whether to output data when viewing other people's information
         * https://fresns.org/database/keyname/interactives.html
         * View other people's content settings.
         */
        if ($uid != $authUserId) {
            $isMarkLists = FsChecker::checkMarkLists($viewType, $viewTarget);
            if ($isMarkLists == false) {
                $this->error(ErrorCodeService::POST_BROWSE_ERROR);
            }
        }

        $request->offsetSet('viewUid', $uid);

        switch ($viewTarget) {
            case 1:
                $itLikeUsers = ApiConfigHelper::getConfigByItemKey('it_like_users');
                $data = FsService::getUserList($request);
                if ($itLikeUsers == false) {
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
     * User Operation Delete Content
     * Delete all to verify that the author of the post or comment is me
     * You need to verify that the user has the right to delete, there are some contents that may not be allowed to be deleted, query the can_delete field in the dependent information table.
     */
    public function delete(Request $request)
    {
        $rule = [
            'type' => 'required|numeric|in:1,2',
            'fsid' => 'required',
        ];
        ValidateService::validateRule($request, $rule);

        $uid = GlobalService::getGlobalKey('user_id');

        $fsid = $request->input('fsid');
        $type = $request->input('type');
        switch ($type) {
            case 1:
                $posts = FresnsPosts::where('pid', $fsid)->first();
                if (empty($posts)) {
                    $this->error(ErrorCodeService::DELETE_POST_ERROR);
                }
                if ($posts['user_id'] != $uid) {
                    $this->error(ErrorCodeService::USER_NO_PERMISSION);
                }
                $postsAppend = FresnsPostAppends::where('post_id', $posts['id'])->first();
                if ($postsAppend['can_delete'] == 0) {
                    $this->error(ErrorCodeService::USER_NO_PERMISSION);
                }
                FresnsPosts::where('id', $posts['id'])->delete();
                FresnsPostAppends::where('id', $postsAppend['id'])->delete();
                break;

            default:
                $comments = FresnsComments::where('cid', $fsid)->first();

                if (empty($comments)) {
                    $this->error(ErrorCodeService::DELETE_COMMENT_ERROR);
                }
                if ($comments['user_id'] != $uid) {
                    $this->error(ErrorCodeService::USER_NO_PERMISSION);
                }
                $commentsAppend = FresnsCommentAppends::where('comment_id', $comments['id'])->first();
                if (! empty($commentsAppend)) {
                    if ($commentsAppend['can_delete'] == 0) {
                        $this->error(ErrorCodeService::USER_NO_PERMISSION);
                    }
                }

                FresnsComments::where('id', $comments['id'])->delete();
                FresnsUserStats::where('user_id', $uid)->decrement('comment_publish_count');
                FresnsConfigs::where('item_key', 'comments_count')->decrement('item_value');
                if (! empty($commentsAppend['id'])) {
                    FresnsCommentAppends::where('id', $commentsAppend['id'])->delete();
                }
                break;
        }

        $this->success();
    }
}
