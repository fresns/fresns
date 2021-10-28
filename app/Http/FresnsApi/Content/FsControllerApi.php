<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Content;

use App\Helpers\StrHelper;
use App\Http\Center\Base\BasePluginConfig;
use App\Http\Center\Common\ErrorCodeService;
use App\Http\Center\Common\GlobalService;
use App\Http\Center\Common\LogService;
use App\Http\Center\Common\ValidateService;
use App\Http\Center\Helper\CmdRpcHelper;
use App\Http\Center\Helper\PluginHelper;
use App\Http\Center\Scene\FileSceneService;
use App\Http\FresnsApi\Base\FresnsBaseApiController;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsDb\FresnsCommentAppends\FresnsCommentAppendsConfig;
use App\Http\FresnsDb\FresnsComments\FresnsComments;
use App\Http\FresnsDb\FresnsComments\FresnsCommentsConfig;
use App\Http\FresnsDb\FresnsComments\FresnsCommentsService;
use App\Http\FresnsDb\FresnsExtendLinkeds\FresnsExtendLinkedsConfig;
use App\Http\FresnsDb\FresnsExtends\FresnsExtendsService;
use App\Http\FresnsDb\FresnsFiles\FresnsFiles;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsGroups\FresnsGroupsConfig;
use App\Http\FresnsDb\FresnsGroups\FresnsGroupsService;
use App\Http\FresnsDb\FresnsHashtagLinkeds\FresnsHashtagLinkeds;
use App\Http\FresnsDb\FresnsHashtagLinkeds\FresnsHashtagLinkedsConfig;
use App\Http\FresnsDb\FresnsHashtags\FresnsHashtags;
use App\Http\FresnsDb\FresnsHashtags\FresnsHashtagsConfig;
use App\Http\FresnsDb\FresnsHashtags\FresnsHashtagsService;
use App\Http\FresnsDb\FresnsImplants\FresnsImplantsService;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollowsConfig;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikes;
use App\Http\FresnsDb\FresnsMemberLikes\FresnsMemberLikesService;
use App\Http\FresnsDb\FresnsMemberRoleRels\FresnsMemberRoleRels;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShields;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShieldsConfig;
use App\Http\FresnsDb\FresnsPluginBadges\FresnsPluginBadges;
use App\Http\FresnsDb\FresnsPlugins\FresnsPlugins as pluginUnikey;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsages;
use App\Http\FresnsDb\FresnsPluginUsages\FresnsPluginUsagesService;
use App\Http\FresnsDb\FresnsPostAppends\FresnsPostAppends;
use App\Http\FresnsDb\FresnsPostMembers\FresnsPostMembersService;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsConfig;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class FsControllerApi extends FresnsBaseApiController
{
    // Get group [tree structure list]
    public function groupTrees(Request $request)
    {
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $uid = $this->uid;
            $member_id = $this->mid;
            $uid = $this->uid;
            if (empty($uid)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
            if (empty($member_id)) {
                $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
            }
        }
        $mid = GlobalService::getGlobalKey('member_id');

        // type_find = 2 (Hidden: Only members can find this group.)
        $FresnsGroups = FresnsGroups::where('type_find', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $noGroupArr = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 2)->pluck('follow_id')->toArray();

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
            $uid = $this->uid;
            $member_id = $this->mid;
            $uid = $this->uid;
            if (empty($uid)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
            if (empty($member_id)) {
                $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
            }
        }
        $mid = GlobalService::getGlobalKey('member_id');

        // type_find = 2 (Hidden: Only members can find this group.)
        $FresnsGroups = FresnsGroups::where('type_find', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $groupMember = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 2)->pluck('follow_id')->toArray();

        $noGroupArr = array_diff($FresnsGroups, $groupMember);
        $groupArr = FresnsGroups::whereNotIn('id', $noGroupArr)->pluck('id')->toArray();
        $ids = implode(',', $groupArr);
        $request->offsetSet('ids', $ids);
        $parentId = $request->input('parentGid');
        if ($parentId) {
            $groupParentId = FresnsGroups::where('uuid', $parentId)->first();
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
            'gid' => "required|exists:{$table},uuid",
        ];
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $uid = $this->uid;
            $member_id = $this->mid;
            $uid = $this->uid;
            if (empty($uid)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
            if (empty($member_id)) {
                $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
            }
        }
        $mid = GlobalService::getGlobalKey('member_id');
        $langTag = $this->langTag;
        $mid = $this->mid;
        ValidateService::validateRule($request, $rule);
        $id = $request->input('gid');
        $FresnsGroupsService = new FresnsGroupsService();
        $request->offsetSet('gid', $id);
        $FresnsGroupsService->setResourceDetail(FresnsGroupsResourceDetail::class);
        $group = FresnsGroups::where('uuid', $id)->first();
        $detail = $FresnsGroupsService->detail($group['id']);
        $this->success($detail);
    }

    // Get post [list]
    public function postLists(Request $request)
    {
        $rule = [
            'searchEssence' => 'in:1,2,3',
            'searchSticky' => 'in:1,2,3',
            'createdTimeGt' => 'date_format:"Y-m-d H:i:s"',
            'createdTimeLt' => 'date_format:"Y-m-d H:i:s"',
            'viewCountGt' => 'numeric',
            'viewCountLt' => 'numeric',
            'likeCountGt' => 'numeric',
            'likeCountLt' => 'numeric',
            'followCountGt' => 'numeric',
            'followCountLt' => 'numeric',
            'shieldCountGt' => 'numeric',
            'shieldCountLt' => 'numeric',
            'commentCountGt' => 'numeric',
            'commentCountLt' => 'numeric',
        ];
        ValidateService::validateRule($request, $rule);

        // Site Model = Private
        // Not logged in, content not output
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $uid = $this->uid;
            $member_id = $this->mid;
            $uid = $this->uid;
            if (empty($uid)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
            if (empty($member_id)) {
                $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
            }
        }
        $mid = GlobalService::getGlobalKey('member_id');

        // Data source: whether provided by the plugin
        $sortNumber = $request->input('sortNumber');
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
            'pid' => "required|exists:{$table},uuid",
        ];
        ValidateService::validateRule($request, $rule);
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $uid = $this->uid;
            $member_id = $this->mid;
            $uid = $this->uid;
            if (empty($uid)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
            if (empty($member_id)) {
                $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
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
        $mid = GlobalService::getGlobalKey('member_id');
        $langTag = $this->langTag;
        $id = $request->input('pid');
        $postId = FresnsPosts::where('uuid', $id)->first();
        $FresnsPostsService = new FresnsPostsService();
        $FresnsPostsService->setResourceDetail(FresnsPostsResourceDetail::class);
        $detail = $FresnsPostsService->detail($postId['id']);

        // type_mode = 2 (Private: Only members can see who's in the group and what they post.)
        $FresnsGroups = FresnsGroups::where('type_mode', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $groupMember = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('deleted_at', null)->where('follow_type', 2)->pluck('follow_id')->toArray();
        $noGroupArr = array_diff($FresnsGroups, $groupMember);
        if (! empty($detail['detail']['group_id'])) {
            if (in_array($detail['detail']['group_id'], $noGroupArr)) {
                $detail['detail'] = [];
            }
        }
        // Filter the posts of blocked objects (members, groups, hashtags, posts), and the posts of blocked objects are not output.
        $memberShieldsTable = FresnsMemberShieldsConfig::CFG_TABLE;
        $memberShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('deleted_at', null)->where('shield_type', 1)->pluck('shield_id')->toArray();
        if (in_array($detail['detail']['member_id'], $memberShields)) {
            $detail['detail'] = [];
        }
        $GroupShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('shield_type', 2)->where('deleted_at', null)->pluck('shield_id')->toArray();
        if (! empty($detail['detail']['group_id'])) {
            if (in_array($detail['detail']['group_id'], $GroupShields)) {
                $detail['detail'] = [];
            }
        }
        $hashtagShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('shield_type', 3)->where('deleted_at', null)->pluck('shield_id')->toArray();
        $noPostHashtags = DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_type', 1)->where('deleted_at', null)->whereIn('hashtag_id', $hashtagShields)->pluck('linked_id')->toArray();
        if (in_array($detail['detail']['id'], $noPostHashtags)) {
            $detail['detail'] = [];
        }
        $commentShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('shield_type', 4)->pluck('shield_id')->where('deleted_at', null)->toArray();
        if (in_array($detail['detail']['id'], $commentShields)) {
            $detail['detail'] = [];
        }

        // Site Model = Private
        // Content output processing
        if ($site_mode == 'private') {
            $memberInfo = FresnsMembers::find($mid);
            if (! empty($memberInfo['expired_at']) && (strtotime($memberInfo['expired_at'])) < time()) {
                $site_private_end = ApiConfigHelper::getConfigByItemKey('site_private_end');
                if ($site_private_end == 1) {
                    $this->error(ErrorCodeService::MEMBER_EXPIRED_ERROR);
                }
                if ($site_private_end == 2) {
                    if ($detail['detail']['created_at'] > $memberInfo['expired_at']) {
                        $detail['detail'] = [];
                    }
                }
            }
        }
        $data = [];

        // SEO Info
        $post = Fresnsposts::where('uuid', $id)->first();
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
            $uid = $this->uid;
            $member_id = $this->mid;
            $uid = $this->uid;
            if (empty($uid)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
            if (empty($member_id)) {
                $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
            }
        }
        $mid = GlobalService::getGlobalKey('member_id');
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
            $uid = $this->uid;
            $member_id = $this->mid;
            $uid = $this->uid;
            if (empty($uid)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
            if (empty($member_id)) {
                $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
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
            $uid = $this->uid;
            $member_id = $this->mid;
            $uid = $this->uid;
            if (empty($uid)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
            if (empty($member_id)) {
                $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
            }
        }
        $mid = GlobalService::getGlobalKey('member_id');
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
            'cid' => "required|exists:{$table},uuid",
        ];
        ValidateService::validateRule($request, $rule);

        // Site Model = Private
        // Not logged in, content not output
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        if ($site_mode == FsConfig::PRIVATE) {
            $uid = $this->uid;
            $member_id = $this->mid;
            $uid = $this->uid;
            if (empty($uid)) {
                $this->error(ErrorCodeService::UID_REQUIRED_ERROR);
            }
            if (empty($member_id)) {
                $this->error(ErrorCodeService::MID_REQUIRED_ERROR);
            }
        }
        $comment = FresnsComments::where('uuid', $request->input('cid'))->first();
        $fresnsCommentsService = new FresnsCommentsService();
        $fresnsCommentsService->setResourceDetail(FresnsCommentsResourceDetail::class);
        $detail = $fresnsCommentsService->detail($comment['id']);
        // Target fields to be masked
        $memberShieldsTable = FresnsMemberShieldsConfig::CFG_TABLE;
        $commentTable = FresnsCommentsConfig::CFG_TABLE;
        $commentAppendTable = FresnsCommentAppendsConfig::CFG_TABLE;
        $postTable = FresnsPostsConfig::CFG_TABLE;
        /**
         * Filtering of comments on blocked objects (members and comments).
         */
        // Target fields to be masked
        $request = request();
        $mid = GlobalService::getGlobalKey('member_id');
        $memberShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('deleted_at', null)->where('shield_type', 1)->pluck('shield_id')->toArray();
        if (in_array($detail['detail']['member_id'], $memberShields)) {
            $detail['detail'] = [];
        }
        $commentShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('deleted_at', null)->where('shield_type', 5)->pluck('shield_id')->toArray();
        if (in_array($detail['detail']['id'], $commentShields)) {
            $detail['detail'] = [];
        }

        // Site Model = Private
        // Content output processing
        if ($site_mode == 'private') {
            $memberInfo = FresnsMembers::find($mid);
            if (! empty($memberInfo['expired_at']) && (strtotime($memberInfo['expired_at'])) < time()) {
                $site_private_end = ApiConfigHelper::getConfigByItemKey('site_private_end');
                if ($site_private_end == 1) {
                    // $query->where('comment.member_id','=',0);
                    $this->error(ErrorCodeService::MEMBER_EXPIRED_ERROR);
                }
                if ($site_private_end == 2) {
                    // $query->where('comment.created_at', '<=', $memberInfo['expired_at']);
                    if ($detail['detail']['created_at'] > $memberInfo['expired_at']) {
                        $detail['detail'] = [];
                    }
                }
            }
        }
        $langTag = $this->langTag;

        // SEO Info
        $comment = FresnsComments::where('uuid', $request->input('cid'))->first();
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
        $sortNumber = $request->input('sortNumber');
        $this->isPluginData('postFollows');
        $mid = GlobalService::getGlobalKey('member_id');
        $type = $request->input('followType');
        switch ($type) {
            case 'member':
                // My posts
                $mePostsArr = FresnsPosts::where('member_id', $mid)->pluck('id')->toArray();

                // $followMemberArr = FresnsMemberFollows::where('member_id',$mid)->where('follow_type',1)->pluck('follow_id')->toArray();
                $followMemberArr = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 1)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $postIdArr = FresnsPosts::whereIn('member_id', $followMemberArr)->pluck('id')->toArray();
                $idArr = array_merge($mePostsArr, $postIdArr);
                // $ids = implode(',', $postIdArr);
                break;
            case 'group':
                // $folloGroupArr = FresnsMemberFollows::where('member_id',$mid)->where('follow_type',2)->pluck('follow_id')->toArray();
                $folloGroupArr = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 2)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $idArr = FresnsPosts::whereIn('group_id', $folloGroupArr)->pluck('id')->toArray();
                // $ids = implode(',', $postIdArr);
                break;
            case 'hashtag':
                // $folloHashtagArr = FresnsMemberFollows::where('member_id',$mid)->where('follow_type',3)->pluck('follow_id')->toArray();
                $folloHashtagArr = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 3)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $idArr = FresnsHashtagLinkeds::where('linked_type', 1)->whereIn('hashtag_id', $folloHashtagArr)->pluck('linked_id')->toArray();
                // $ids = implode(',', $postIdArr);
                break;
            default:
                // My posts
                $mePostsArr = FresnsPosts::where('member_id', $mid)->pluck('id')->toArray();

                // Posts by following members
                // $followMemberArr = FresnsMemberFollows::where('member_id',$mid)->where('follow_type',1)->pluck('follow_id')->toArray();
                $followMemberArr = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 1)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $postMemberIdArr = FresnsPosts::whereIn('member_id', $followMemberArr)->pluck('id')->toArray();

                // Only posts that have been added to the essence are exported under groups and hashtags
                // $folloGroupArr = FresnsMemberFollows::where('member_id',$mid)->where('follow_type',2)->pluck('follow_id')->toArray();
                $folloGroupArr = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 2)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $postGroupIdArr = FresnsPosts::whereIn('group_id', $folloGroupArr)->where('essence_state', '!=', 1)->pluck('id')->toArray();

                // $folloHashtagArr = FresnsMemberFollows::where('member_id',$mid)->where('follow_type',3)->pluck('follow_id')->toArray();
                $folloHashtagArr = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('follow_type', 3)->where('deleted_at', null)->pluck('follow_id')->toArray();
                $postIdArr = FresnsHashtagLinkeds::where('linked_type', 1)->whereIn('hashtag_id', $folloHashtagArr)->pluck('linked_id')->toArray();
                $postHashtagIdArr = FresnsPosts::whereIn('id', $postIdArr)->where('essence_state', '!=', 1)->pluck('id')->toArray();

                // Posts set as secondary essence, forced output
                $essenceIdArr = FresnsPosts::where('essence_state', 3)->pluck('id')->toArray();
                $idArr = array_merge($mePostsArr, $postMemberIdArr, $postGroupIdArr, $postHashtagIdArr, $essenceIdArr);
                // $ids = implode(',', $idArr);
                break;
        }

        // Site Model = Private
        // Content output processing
        if ($site_mode == 'private') {
            $memberInfo = FresnsMembers::find($mid);
            if (! empty($memberInfo['expired_at']) && (strtotime($memberInfo['expired_at'])) < time()) {
                $site_private_end = ApiConfigHelper::getConfigByItemKey('site_private_end');
                if ($site_private_end == 1) {
                    $request->offsetSet('id', 0);
                }
                if ($site_private_end == 2) {
                    $request->offsetSet('expired_at', $memberInfo['expired_at']);
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
        $sortNumber = $request->input('sortNumber');
        $this->isPluginData('postNearbys');
        $table = FresnsGroupsConfig::CFG_TABLE;
        $rule = [
            'longitude' => 'required',
            'latitude' => 'required',
            'mapId' => 'required',
        ];
        ValidateService::validateRule($request, $rule);
        $site_mode = ApiConfigHelper::getConfigByItemKey(FsConfig::SITE_MODEL);
        $mid = GlobalService::getGlobalKey('member_id');
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

        $memberShieldsTable = FresnsMemberShieldsConfig::CFG_TABLE;

        // type_mode = 2 (Private: Only members can see who's in the group and what they post.)
        $FresnsGroups = FresnsGroups::where('type_mode', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $groupMember = DB::table(FresnsMemberFollowsConfig::CFG_TABLE)->where('member_id', $mid)->where('deleted_at', null)->where('follow_type', 2)->pluck('follow_id')->toArray();
        $noGroupArr = array_diff($FresnsGroups, $groupMember);

        // Filter the posts of blocked objects (members, groups, hashtags, posts), and the posts of blocked objects are not output.
        $memberShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('shield_type', 1)->where('deleted_at', null)->pluck('shield_id')->toArray();
        $GroupShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('shield_type', 2)->where('deleted_at', null)->pluck('shield_id')->toArray();
        $hashtagShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('shield_type', 3)->where('deleted_at', null)->pluck('shield_id')->toArray();
        $noPostHashtags = FresnsHashtagLinkeds::where('linked_type', 1)->whereIn('hashtag_id', $hashtagShields)->pluck('linked_id')->toArray();
        $commentShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('shield_type', 4)->where('deleted_at', null)->pluck('shield_id')->toArray();
        $postArr2 = FresnsPosts::whereNotIn('group_id', $noGroupArr)->whereNotIn('member_id', $memberShields)->whereNotIn('group_id', $GroupShields)->whereNotIn('id', $noPostHashtags)->whereNotIn('id', $commentShields)->pluck('id')->toArray();
        $idArr = array_intersect($postArr1, $postArr2);
        $searchType = $request->input('searchType', '');
        if ($searchType == 'all') {
            $request->offsetSet('searchType', '');
        }

        // Site Model = Private
        // Content output processing
        if ($site_mode == 'private') {
            $memberInfo = FresnsMembers::find($mid);
            if (! empty($memberInfo['expired_at']) && (strtotime($memberInfo['expired_at'])) < time()) {
                $site_private_end = ApiConfigHelper::getConfigByItemKey('site_private_end');
                if ($site_private_end == 1) {
                    $this->error(ErrorCodeService::MEMBER_EXPIRED_ERROR);
                }
                if ($site_private_end == 2) {
                    $request->offsetSet('expired_at', $memberInfo['expired_at']);
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
            fs_posts
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
                    // Get interface sortNumber parameters
                    // $sortNumber = $d['sortNumber'][0]['id'];

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
