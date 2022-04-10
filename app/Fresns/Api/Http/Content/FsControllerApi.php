<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Content;

use App\Fresns\Api\Center\Base\BasePluginConfig;
use App\Fresns\Api\Center\Common\ErrorCodeService;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\Center\Common\LogService;
use App\Fresns\Api\Center\Common\ValidateService;
use App\Fresns\Api\Center\Helper\CmdRpcHelper;
use App\Fresns\Api\Center\Helper\PluginHelper;
use App\Fresns\Api\FsDb\FresnsCommentAppends\FresnsCommentAppendsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsComments;
use App\Fresns\Api\FsDb\FresnsComments\FresnsCommentsConfig;
use App\Fresns\Api\FsDb\FresnsComments\FresnsCommentsService;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroups;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroupsConfig;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroupsService;
use App\Fresns\Api\FsDb\FresnsHashtagLinkeds\FresnsHashtagLinkeds;
use App\Fresns\Api\FsDb\FresnsHashtagLinkeds\FresnsHashtagLinkedsConfig;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtags;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtagsConfig;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtagsService;
use App\Fresns\Api\FsDb\FresnsImplants\FresnsImplantsService;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Fresns\Api\FsDb\FresnsPluginUsages\FresnsPluginUsagesService;
use App\Fresns\Api\FsDb\FresnsPostAppends\FresnsPostAppends;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPosts;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsService;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocksConfig;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollowsConfig;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\Http\Base\FsApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FsControllerApi extends FsApiController
{
    // Get group [tree structure list]
    public function groupTrees(Request $request)
    {
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $aid = $this->aid;
            $user_id = $this->uid;
            if (empty($aid)) {
                $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
            }
            if (empty($user_id)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
        }
        $uid = GlobalService::getGlobalKey('user_id');

        // type_find = 2 (Hidden: Only users can find this group.)
        $FresnsGroups = FresnsGroups::where('type_find', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $noGroupArr = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 2)->pluck('follow_id')->toArray();

        $groupArr = FresnsGroups::whereNotIn('id', $noGroupArr)->where('parent_id', null)->pluck('id')->toArray();
        $ids = implode(',', $groupArr);
        $request->offsetSet('ids', $ids);
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 30);
        $FresnsDialogsService = new FresnsGroupsService();
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('pageSize', $pageSize);
        $request->offsetSet('is_enable', 1);
        $FresnsDialogsService->setResource(FresnsGroupsTreesResource::class);
        $data = $FresnsDialogsService->searchData();
        $data = [
            'pagination' => $data['pagination'],
            'list' => $data['list'],
        ];
        $this->success($data);
    }

    // Get group [list]
    public function groupLists(Request $request)
    {
        $rule = [
            'type' => 'required|in:1,2',
            'createdTimeGt' => 'date_format:"Y-m-d H:i:s"',
            'createdTimeLt' => 'date_format:"Y-m-d H:i:s"',
        ];
        ValidateService::validateRule($request, $rule);
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $aid = $this->aid;
            $user_id = $this->uid;
            if (empty($aid)) {
                $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
            }
            if (empty($user_id)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
        }
        $uid = GlobalService::getGlobalKey('user_id');

        // type_find = 2 (Hidden: Only users can find this group.)
        $FresnsGroups = FresnsGroups::where('type_find', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $groupUser = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 2)->pluck('follow_id')->toArray();

        $noGroupArr = array_diff($FresnsGroups, $groupUser);
        $groupArr = FresnsGroups::whereNotIn('id', $noGroupArr)->pluck('id')->toArray();
        $ids = implode(',', $groupArr);
        $request->offsetSet('ids', $ids);
        $parentId = $request->input('parentGid');
        if ($parentId) {
            $groupParentId = FresnsGroups::where('gid', $parentId)->first();
            if ($groupParentId) {
                $request->offsetSet('parentId', $groupParentId['id']);
            } else {
                $request->offsetSet('parentId', 0);
            }
        }
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 30);
        $FresnsDialogsService = new FresnsGroupsService();
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('pageSize', $pageSize);
        $request->offsetSet('is_enable', 1);
        $FresnsDialogsService->setResource(FresnsGroupsResource::class);
        $data = $FresnsDialogsService->searchData();
        $data = [
            'pagination' => $data['pagination'],
            'list' => $data['list'],
        ];
        $this->success($data);
    }

    // Get group [detail]
    public function groupDetail(Request $request)
    {
        $table = FresnsGroupsConfig::CFG_TABLE;
        $rule = [
            'gid' => "required|exists:{$table},gid",
        ];
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $aid = $this->aid;
            $user_id = $this->uid;
            if (empty($aid)) {
                $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
            }
            if (empty($user_id)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
        }
        $uid = GlobalService::getGlobalKey('user_id');
        $langTag = $this->langTag;
        $uid = $this->uid;
        ValidateService::validateRule($request, $rule);
        $id = $request->input('gid');
        $FresnsGroupsService = new FresnsGroupsService();
        $request->offsetSet('gid', $id);
        $FresnsGroupsService->setResourceDetail(FresnsGroupsResourceDetail::class);
        $group = FresnsGroups::where('gid', $id)->first();
        $detail = $FresnsGroupsService->detail($group['id']);
        $this->success($detail);
    }

    // Get post [list]
    public function postLists(Request $request)
    {
        $rule = [
            'searchDigest' => 'in:1,2,3',
            'searchSticky' => 'in:1,2,3',
            'createdTimeGt' => 'date_format:"Y-m-d H:i:s"',
            'createdTimeLt' => 'date_format:"Y-m-d H:i:s"',
            'viewCountGt' => 'numeric',
            'viewCountLt' => 'numeric',
            'likeCountGt' => 'numeric',
            'likeCountLt' => 'numeric',
            'followCountGt' => 'numeric',
            'followCountLt' => 'numeric',
            'blockCountGt' => 'numeric',
            'blockCountLt' => 'numeric',
            'commentCountGt' => 'numeric',
            'commentCountLt' => 'numeric',
        ];
        ValidateService::validateRule($request, $rule);

        // Site Model = Private
        // Not logged in, content not output
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $aid = $this->aid;
            $user_id = $this->uid;
            if (empty($aid)) {
                $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
            }
            if (empty($user_id)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
        }
        $uid = GlobalService::getGlobalKey('user_id');

        // Data source: whether provided by the plugin
        $rankNumber = $request->input('rankNumber');
        $this->isPluginData('postLists');

        $request->offsetSet('queryType', FsConfig::QUERY_TYPE_SQL_QUERY);
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 30);
        $FresnsPostsService = new FresnsPostsService();
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('pageSize', $pageSize);
        $FresnsPostsService->setResource(FresnsPostsResource::class);
        $list = $FresnsPostsService->searchData();
        $implants = FresnsImplantsService::getImplants($page, $pageSize, 1);
        $common['implants'] = $implants;
        $data = [
            'pagination' => $list['pagination'],
            'list' => $list['list'],
            'common' => $common,
        ];
        $this->success($data);
    }

    // Get post [detail]
    public function postDetail(Request $request)
    {
        $table = FresnsPostsConfig::CFG_TABLE;
        $rule = [
            'pid' => "required|exists:{$table},pid",
        ];
        ValidateService::validateRule($request, $rule);
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $aid = $this->aid;
            $user_id = $this->uid;
            if (empty($aid)) {
                $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
            }
            if (empty($user_id)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
        }

        // Data source: whether provided by the plugin
        $post_detail_config = ApiConfigHelper::getConfigByItemKey(FsConfig::POST_DETAIL_SERVICE);
        if ($post_detail_config) {
            $cmd = BasePluginConfig::FRESNS_CMD_DEFAULT;
            $pluginClass = PluginHelper::findPluginClass($post_detail_config);
            if (empty($pluginClass)) {
                LogService::error('Plugin not found');
                $this->error(ErrorCodeService::PLUGINS_CLASS_ERROR);
            }
            $input = [
                'type' => 'postDetail',
                'header' => $this->getHeader($request->header()),
                'body' => $request->all(),
            ];
            $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
            $this->success($resp['output']);
        }
        $uid = GlobalService::getGlobalKey('user_id');
        $langTag = $this->langTag;
        $id = $request->input('pid');
        $postId = FresnsPosts::where('pid', $id)->first();
        $FresnsPostsService = new FresnsPostsService();
        $FresnsPostsService->setResourceDetail(FresnsPostsResourceDetail::class);
        $detail = $FresnsPostsService->detail($postId['id']);

        // type_mode = 2 (Private: Only users can see who's in the group and what they post.)
        $FresnsGroups = FresnsGroups::where('type_mode', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $groupUser = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('deleted_at', null)->where('follow_type', 2)->pluck('follow_id')->toArray();
        $noGroupArr = array_diff($FresnsGroups, $groupUser);
        if (! empty($detail['detail']['group_id'])) {
            if (in_array($detail['detail']['group_id'], $noGroupArr)) {
                $detail['detail'] = [];
            }
        }
        // Filter the posts of blocked objects (users, groups, hashtags, posts), and the posts of blocked objects are not output.
        $userBlocksTable = FresnsUserBlocksConfig::CFG_TABLE;
        $userBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('deleted_at', null)->where('block_type', 1)->pluck('block_id')->toArray();
        if (in_array($detail['detail']['user_id'], $userBlocks)) {
            $detail['detail'] = [];
        }
        $GroupBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('block_type', 2)->where('deleted_at', null)->pluck('block_id')->toArray();
        if (! empty($detail['detail']['group_id'])) {
            if (in_array($detail['detail']['group_id'], $GroupBlocks)) {
                $detail['detail'] = [];
            }
        }
        $hashtagBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('block_type', 3)->where('deleted_at', null)->pluck('block_id')->toArray();
        $noPostHashtags = DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_type', 1)->where('deleted_at', null)->whereIn('hashtag_id', $hashtagBlocks)->pluck('linked_id')->toArray();
        if (in_array($detail['detail']['id'], $noPostHashtags)) {
            $detail['detail'] = [];
        }
        $commentBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('block_type', 4)->pluck('block_id')->where('deleted_at', null)->toArray();
        if (in_array($detail['detail']['id'], $commentBlocks)) {
            $detail['detail'] = [];
        }

        // Site Model = Private
        // Content output processing
        if ($site_mode == 'private') {
            $userInfo = FresnsUsers::find($uid);
            if (! empty($userInfo['expired_at']) && (strtotime($userInfo['expired_at'])) < time()) {
                $site_private_end = ApiConfigHelper::getConfigByItemKey('site_private_end');
                if ($site_private_end == 1) {
                    $this->error(ErrorCodeService::USER_EXPIRED_ERROR);
                }
                if ($site_private_end == 2) {
                    if ($detail['detail']['created_at'] > $userInfo['expired_at']) {
                        $detail['detail'] = [];
                    }
                }
            }
        }
        $data = [];

        // SEO Info
        $post = FresnsPosts::where('pid', $id)->first();
        $seoPost['seoInfo'] = [];
        if (! $langTag) {
            $langTag = FresnsPluginUsagesService::getDefaultLanguage();
        }
        $seo = [];
        if ($post) {
            $seo = DB::table('seo')->where('linked_type', 4)->where('linked_id', $post['id'])->where('lang_tag', $langTag)->where('deleted_at', null)->first();
        }
        $seoInfo = [];
        if ($seo) {
            $seoInfo['title'] = $seo->title;
            $seoInfo['keywords'] = $seo->keywords;
            $seoInfo['description'] = $seo->description;
            $seoPost['seoInfo'] = $seoInfo;
        }
        $seoPost['seoInfo'] = (object) $seoPost['seoInfo'];
        $detail['common'] = $seoPost;
        $this->success($detail);
    }

    // Get hashtag [list]
    public function hashtagLists(Request $request)
    {
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $aid = $this->aid;
            $user_id = $this->uid;
            if (empty($aid)) {
                $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
            }
            if (empty($user_id)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
        }
        $uid = GlobalService::getGlobalKey('user_id');
        $langTag = $this->langTag;
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 30);
        $FresnsHashtagsService = new FresnsHashtagsService();
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('pageSize', $pageSize);
        $FresnsHashtagsService->setResource(FresnsHashtagsResource::class);
        $data = $FresnsHashtagsService->searchData();
        $data = [
            'pagination' => $data['pagination'],
            'list' => $data['list'],
        ];
        $this->success($data);
    }

    // Get hashtag [detail]
    public function hashtagDetail(Request $request)
    {
        $table = FresnsHashtagsConfig::CFG_TABLE;
        $rule = [
            'huri' => "required|exists:{$table},slug",
        ];
        ValidateService::validateRule($request, $rule);
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $aid = $this->aid;
            $user_id = $this->uid;
            if (empty($aid)) {
                $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
            }
            if (empty($user_id)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
        }
        $langTag = $this->langTag;
        $FresnsHashtagsService = new FresnsHashtagsService();
        $FresnsHashtagsService->setResourceDetail(FresnsHashtagsResourceDetail::class);
        $id = FresnsHashtags::where('slug', $request->input('huri'))->first();
        $detail = $FresnsHashtagsService->detail($id['id']);
        $this->success($detail);
    }

    // Get comment [list]
    public function commentLists(Request $request)
    {
        // Site Model = Private
        // Not logged in, content not output
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $aid = $this->aid;
            $user_id = $this->uid;
            if (empty($aid)) {
                $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
            }
            if (empty($user_id)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
        }
        $uid = GlobalService::getGlobalKey('user_id');
        $request->offsetSet('queryType', FsConfig::QUERY_TYPE_SQL_QUERY);
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 30);
        $fresnsCommentsService = new FresnsCommentsService();
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('pageSize', $pageSize);
        $fresnsCommentsService->setResource(FresnsCommentsResource::class);
        $list = $fresnsCommentsService->searchData();
        $implants = FresnsImplantsService::getImplants($page, $pageSize, 1);
        $common['implants'] = $implants;
        $data = [
            'pagination' => $list['pagination'],
            'list' => $list['list'],
            'common' => $common,
        ];
        $this->success($data);
    }

    // Get comment [detail]
    public function commentDetail(Request $request)
    {
        $table = FresnsCommentsConfig::CFG_TABLE;
        $rule = [
            'cid' => "required|exists:{$table},cid",
        ];
        ValidateService::validateRule($request, $rule);

        // Site Model = Private
        // Not logged in, content not output
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $aid = $this->aid;
            $user_id = $this->uid;
            if (empty($aid)) {
                $this->error(ErrorCodeService::AID_REQUIRED_ERROR);
            }
            if (empty($user_id)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
        }
        $comment = FresnsComments::where('cid', $request->input('cid'))->first();
        $fresnsCommentsService = new FresnsCommentsService();
        $fresnsCommentsService->setResourceDetail(FresnsCommentsResourceDetail::class);
        $detail = $fresnsCommentsService->detail($comment['id']);
        // Target fields to be masked
        $userBlocksTable = FresnsUserBlocksConfig::CFG_TABLE;
        $commentTable = FresnsCommentsConfig::CFG_TABLE;
        $commentAppendTable = FresnsCommentAppendsConfig::CFG_TABLE;
        $postTable = FresnsPostsConfig::CFG_TABLE;
        /**
         * Filtering of comments on blocked objects (users and comments).
         */
        // Target fields to be masked
        $request = request();
        $uid = GlobalService::getGlobalKey('user_id');
        $userBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('deleted_at', null)->where('block_type', 1)->pluck('block_id')->toArray();
        if (in_array($detail['detail']['user_id'], $userBlocks)) {
            $detail['detail'] = [];
        }
        $commentBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('deleted_at', null)->where('block_type', 5)->pluck('block_id')->toArray();
        if (in_array($detail['detail']['id'], $commentBlocks)) {
            $detail['detail'] = [];
        }

        // Site Model = Private
        // Content output processing
        if ($site_mode == 'private') {
            $userInfo = FresnsUsers::find($uid);
            if (! empty($userInfo['expired_at']) && (strtotime($userInfo['expired_at'])) < time()) {
                $site_private_end = ApiConfigHelper::getConfigByItemKey('site_private_end');
                if ($site_private_end == 1) {
                    // $query->where('comment.user_id','=',0);
                    $this->error(ErrorCodeService::USER_EXPIRED_ERROR);
                }
                if ($site_private_end == 2) {
                    // $query->where('comment.created_at', '<=', $userInfo['expired_at']);
                    if ($detail['detail']['created_at'] > $userInfo['expired_at']) {
                        $detail['detail'] = [];
                    }
                }
            }
        }
        $langTag = $this->langTag;

        // SEO Info
        $comment = FresnsComments::where('cid', $request->input('cid'))->first();
        $seoComment['seoInfo'] = [];
        if (! $langTag) {
            $langTag = FresnsPluginUsagesService::getDefaultLanguage();
        }
        $seo = [];
        if ($comment) {
            $seo = DB::table('seo')->where('linked_type', 5)->where('linked_id', $comment['id'])->where('lang_tag',
                $langTag)->where('deleted_at', null)->first();
        }
        $seoInfo = [];
        if ($seo) {
            $seoInfo['title'] = $seo->title;
            $seoInfo['keywords'] = $seo->keywords;
            $seoInfo['description'] = $seo->description;
            $seoComment['seoInfo'] = $seoInfo;
        }
        $seoComment['seoInfo'] = (object) $seoComment['seoInfo'];

        $detail['common'] = $seoComment;

        $this->success($detail);
    }

    // Get posts to follow [list]
    public function postFollows(Request $request)
    {
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);

        // Data source: whether provided by the plugin
        $rankNumber = $request->input('rankNumber');
        $this->isPluginData('postFollows');
        $uid = GlobalService::getGlobalKey('user_id');
        $type = $request->input('followType');
        switch ($type) {
            case 'user':
                // My posts
                $mePostsArr = FresnsPosts::where('user_id', $uid)->pluck('id')->toArray();

                // $followUserArr = FresnsUserFollows::where('user_id',$uid)->where('follow_type',1)->pluck('follow_id')->toArray();
                $followUserArr = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 1)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $postIdArr = FresnsPosts::whereIn('user_id', $followUserArr)->pluck('id')->toArray();
                $idArr = array_merge($mePostsArr, $postIdArr);
                // $ids = implode(',', $postIdArr);
                break;
            case 'group':
                // $folloGroupArr = FresnsUserFollows::where('user_id',$uid)->where('follow_type',2)->pluck('follow_id')->toArray();
                $folloGroupArr = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 2)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $idArr = FresnsPosts::whereIn('group_id', $folloGroupArr)->pluck('id')->toArray();
                // $ids = implode(',', $postIdArr);
                break;
            case 'hashtag':
                // $folloHashtagArr = FresnsUserFollows::where('user_id',$uid)->where('follow_type',3)->pluck('follow_id')->toArray();
                $folloHashtagArr = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 3)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $idArr = FresnsHashtagLinkeds::where('linked_type', 1)->whereIn('hashtag_id', $folloHashtagArr)->pluck('linked_id')->toArray();
                // $ids = implode(',', $postIdArr);
                break;
            default:
                // My posts
                $mePostsArr = FresnsPosts::where('user_id', $uid)->pluck('id')->toArray();

                // Posts by following users
                // $followUserArr = FresnsUserFollows::where('user_id',$uid)->where('follow_type',1)->pluck('follow_id')->toArray();
                $followUserArr = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 1)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $postUserIdArr = FresnsPosts::whereIn('user_id', $followUserArr)->pluck('id')->toArray();

                // Only posts that have been added to the digest are exported under groups and hashtags
                // $folloGroupArr = FresnsUserFollows::where('user_id',$uid)->where('follow_type',2)->pluck('follow_id')->toArray();
                $folloGroupArr = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 2)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $postGroupIdArr = FresnsPosts::whereIn('group_id', $folloGroupArr)->where('digest_state', '!=', 1)->pluck('id')->toArray();

                // $folloHashtagArr = FresnsUserFollows::where('user_id',$uid)->where('follow_type',3)->pluck('follow_id')->toArray();
                $folloHashtagArr = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('follow_type', 3)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $postIdArr = FresnsHashtagLinkeds::where('linked_type', 1)->whereIn('hashtag_id', $folloHashtagArr)->pluck('linked_id')->toArray();
                $postHashtagIdArr = FresnsPosts::whereIn('id', $postIdArr)->where('digest_state', '!=', 1)->pluck('id')->toArray();

                // Posts set as secondary digest, forced output
                $digestIdArr = FresnsPosts::where('digest_state', 3)->pluck('id')->toArray();
                $idArr = array_merge($mePostsArr, $postUserIdArr, $postGroupIdArr, $postHashtagIdArr, $digestIdArr);
                // $ids = implode(',', $idArr);
                break;
        }

        // Site Model = Private
        // Content output processing
        if ($site_mode == 'private') {
            $userInfo = FresnsUsers::find($uid);
            if (! empty($userInfo['expired_at']) && (strtotime($userInfo['expired_at'])) < time()) {
                $site_private_end = ApiConfigHelper::getConfigByItemKey('site_private_end');
                if ($site_private_end == 1) {
                    $request->offsetSet('id', 0);
                }
                if ($site_private_end == 2) {
                    $request->offsetSet('expired_at', $userInfo['expired_at']);
                }
            }
        }

        // Content Type
        $searchType = $request->input('searchType', '');
        if ($searchType == 'all') {
            $request->offsetSet('searchType', '');
        }
        // Filter Table
        $idArr = FresnsPostAppends::whereIn('post_id', $idArr)->pluck('post_id')->toArray();
        $ids = implode(',', $idArr);

        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 30);
        $FresnsPostsService = new FresnsPostsService();
        $request->offsetSet('ids', $ids);
        $request->offsetSet('is_enable', 1);
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('pageSize', $pageSize);
        $FresnsPostsService->setResource(FresnsPostsResource::class);
        $list = $FresnsPostsService->searchData();
        $implants = FresnsImplantsService::getImplants($page, $pageSize, 1);
        $common['implants'] = $implants;
        $data = [
            'pagination' => $list['pagination'],
            'list' => $list['list'],
            'common' => $common,
        ];
        $this->success($data);
    }

    // Get posts to nearby [list]
    public function postNearbys(Request $request)
    {
        // Data source: whether provided by the plugin
        $rankNumber = $request->input('rankNumber');
        $this->isPluginData('postNearbys');
        $table = FresnsGroupsConfig::CFG_TABLE;
        $rule = [
            'longitude' => 'required',
            'latitude' => 'required',
            'mapId' => 'required',
        ];
        ValidateService::validateRule($request, $rule);
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        $uid = GlobalService::getGlobalKey('user_id');
        $langTag = $this->langTag;
        // Default kilometers
        $configLength = ApiConfigHelper::getConfigByItemKey('nearby_length');
        $length = $request->input('length', $configLength);

        $lengthUnits = $request->input('lengthUnits');
        if (! $lengthUnits) {
            // Distance
            $languages = ApiConfigHelper::distanceUnits($langTag);
            $lengthUnits = empty($languages) ? 'km' : $languages;
        }
        $longitude = $request->input('longitude');
        $latitude = $request->input('latitude');
        if ($lengthUnits == 'mi') {
            $distance = 1609.344 * $length;
        } else {
            $distance = 1000 * $length;
        }

        $postArr1 = self::distance1($longitude, $latitude, $distance);

        $userBlocksTable = FresnsUserBlocksConfig::CFG_TABLE;

        // type_mode = 2 (Private: Only users can see who's in the group and what they post.)
        $FresnsGroups = FresnsGroups::where('type_mode', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $groupUser = DB::table(FresnsUserFollowsConfig::CFG_TABLE)->where('user_id', $uid)->where('deleted_at', null)->where('follow_type', 2)->pluck('follow_id')->toArray();
        $noGroupArr = array_diff($FresnsGroups, $groupUser);

        // Filter the posts of blocked objects (users, groups, hashtags, posts), and the posts of blocked objects are not output.
        $userBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('block_type', 1)->where('deleted_at', null)->pluck('block_id')->toArray();
        $GroupBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('block_type', 2)->where('deleted_at', null)->pluck('block_id')->toArray();
        $hashtagBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('block_type', 3)->where('deleted_at', null)->pluck('block_id')->toArray();
        $noPostHashtags = FresnsHashtagLinkeds::where('linked_type', 1)->whereIn('hashtag_id', $hashtagBlocks)->pluck('linked_id')->toArray();
        $commentBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('block_type', 4)->where('deleted_at', null)->pluck('block_id')->toArray();
        $postArr2 = FresnsPosts::whereNotIn('group_id', $noGroupArr)->whereNotIn('user_id', $userBlocks)->whereNotIn('group_id', $GroupBlocks)->whereNotIn('id', $noPostHashtags)->whereNotIn('id', $commentBlocks)->pluck('id')->toArray();
        $idArr = array_intersect($postArr1, $postArr2);
        $searchType = $request->input('searchType', '');
        if ($searchType == 'all') {
            $request->offsetSet('searchType', '');
        }

        // Site Model = Private
        // Content output processing
        if ($site_mode == 'private') {
            $userInfo = FresnsUsers::find($uid);
            if (! empty($userInfo['expired_at']) && (strtotime($userInfo['expired_at'])) < time()) {
                $site_private_end = ApiConfigHelper::getConfigByItemKey('site_private_end');
                if ($site_private_end == 1) {
                    $this->error(ErrorCodeService::USER_EXPIRED_ERROR);
                }
                if ($site_private_end == 2) {
                    $request->offsetSet('expired_at', $userInfo['expired_at']);
                }
            }
        }
        $page = $request->input('page', 1);
        $pageSize = $request->input('pageSize', 30);
        $FresnsPostsService = new FresnsPostsService();
        $request->offsetSet('ids', implode(',', $idArr));
        $request->offsetSet('is_enable', true);
        $request->offsetSet('currentPage', $page);
        $request->offsetSet('pageSize', $pageSize);
        $FresnsPostsService->setResource(FresnsPostsResource::class);
        $list = $FresnsPostsService->searchData();
        $implants = FresnsImplantsService::getImplants($page, $pageSize, 1);
        $common = $implants;
        $data = [
            'pagination' => $list['pagination'],
            'list' => $list['list'],
            'common' => $common,
        ];
        $this->success($data);
    }

    // Calculate distance by latitude and longitude
    public static function distance1($longitude, $latitude, $distance)
    {
        $tableName = env('DB_PREFIX').'posts';
        $sql = "SELECT id,
        ROUND(
            6378.138 * 2 * ASIN(
                SQRT(
                    POW(
                        SIN(
                            (
                                $latitude * PI() / 180 - map_latitude * PI() / 180
                            ) / 2
                        ),
                        2
                    ) + COS($latitude * PI() / 180) * COS(map_latitude * PI() / 180) * POW(
                        SIN(
                            (
                                $longitude * PI() / 180 - map_longitude * PI() / 180
                            ) / 2
                        ),
                        2
                    )
                )
            ) * 1000
        ) AS juli
        FROM
            $tableName
        HAVING
            juli < $distance
        ORDER BY
            juli ASC";
        $result = DB::select($sql);
        $res = [];
        foreach ($result as $key => $v) {
            $res[] = $v->id;
        }

        return $res;
    }

    // Data source: whether provided by the plugin
    public function isPluginData($apiName)
    {
        $request = request();
        $pluginUsages = FresnsPluginUsages::where('type', 4)->where('is_enable', 1)->first();
        // $status = false;
        if (! $pluginUsages || empty($pluginUsages['data_sources'])) {
            return;
        }
        $data_source = json_decode($pluginUsages['data_sources'], true);
        if (! $data_source) {
            return;
        }
        foreach ($data_source as $key => $d) {
            if ($key == $apiName) {
                if (! isset($d['pluginUnikey'])) {
                    return;
                } else {
                    if (empty($d['pluginUnikey'])) {
                        return;
                    }
                    // Get interface rankNumber parameters
                    // $rankNumber = $d['rankNumber'][0]['id'];

                    // Plugin return data
                    $pluginUnikey = $d['pluginUnikey'];

                    // Request Plugin
                    $pluginClass = PluginHelper::findPluginClass($pluginUnikey);
                    if (empty($pluginClass)) {
                        LogService::error('Plugin not found');
                        $this->error(ErrorCodeService::PLUGINS_CLASS_ERROR);
                    }
                    $cmd = BasePluginConfig::FRESNS_CMD_DEFAULT;
                    $pluginClass = PluginHelper::findPluginClass($pluginUnikey);
                    $input = [
                        'type' => $apiName,
                        'header' => $this->getHeader($request->header()),
                        'body' => $request->all(),
                    ];
                    $resp = CmdRpcHelper::call($pluginClass, $cmd, $input);
                    $this->success($resp['output']);
                }
            }
        }
    }

    public function getHeader($header)
    {
        $arr = [];
        foreach ($header as $key => $h) {
            foreach ($h as $v) {
                $arr[$key] = $v;
            }
        }

        return $arr;
    }
}
