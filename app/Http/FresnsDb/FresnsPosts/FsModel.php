<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsPosts;

use App\Base\Models\BaseAdminModel;
use App\Http\Center\Common\GlobalService;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsDb\FresnsGroups\FresnsGroups;
use App\Http\FresnsDb\FresnsHashtagLinkeds\FresnsHashtagLinkeds;
use App\Http\FresnsDb\FresnsHashtagLinkeds\FresnsHashtagLinkedsConfig;
use App\Http\FresnsDb\FresnsHashtags\FresnsHashtags;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollowsConfig;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShieldsConfig;
use App\Http\FresnsDb\FresnsPostAppends\FresnsPostAppendsConfig;
use Illuminate\Support\Facades\DB;

class FsModel extends BaseAdminModel
{
    protected $table = FsConfig::CFG_TABLE;

    // Front-end form field mapping
    public function formFieldsMap()
    {
        return FsConfig::FORM_FIELDS_MAP;
    }

    // New search criteria
    public function getAddedSearchableFields()
    {
        return FsConfig::ADDED_SEARCHABLE_FIELDS;
    }

    // hook - after adding
    public function hookStoreAfter($id)
    {
    }

    public function getRawSqlQuery()
    {
        $memberShieldsTable = FresnsMemberShieldsConfig::CFG_TABLE;
        $memberFollowTable = FresnsMemberFollowsConfig::CFG_TABLE;
        $postTable = FresnsPostsConfig::CFG_TABLE;
        $append = FresnsPostAppendsConfig::CFG_TABLE;

        /**
         * API Logic
         * https://fresns.org/api/content/post-lists.html.
         */
        $request = request();
        $mid = GlobalService::getGlobalKey('member_id');

        // type_mode = 2 (Private: Only members can see who's in the group and what they post.)
        $FresnsGroups = FresnsGroups::where('type_mode', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $groupMember = DB::table($memberFollowTable)->where('member_id', $mid)->where('deleted_at', null)->where('follow_type', 2)->pluck('follow_id')->toArray();
        $noGroupArr = array_diff($FresnsGroups, $groupMember);

        // Filter the posts of blocked objects (members, groups, hashtags, posts), and the posts of blocked objects are not output.
        $memberShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('deleted_at', null)->where('shield_type', 1)->pluck('shield_id')->toArray();
        $GroupShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('deleted_at', null)->where('shield_type', 2)->pluck('shield_id')->toArray();
        $hashtagShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('deleted_at', null)->where('shield_type', 3)->pluck('shield_id')->toArray();
        $noPostHashtags = DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_type', 1)->where('deleted_at', null)->whereIn('hashtag_id', $hashtagShields)->pluck('linked_id')->toArray();
        $commentShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('deleted_at', null)->where('shield_type', 4)->pluck('shield_id')->toArray();
        $query = DB::table("$postTable as post")->select('post.*')
            ->join("$append as append", 'post.id', '=', 'append.post_id')
            ->whereNotIn('post.member_id', $memberShields)
            ->whereNotIn('post.id', $noPostHashtags)
            ->whereNotIn('post.id', $commentShields)
            ->where('post.deleted_at', null);

        // Posts from the Powerless Group
        if (! empty($noGroupArr)) {
            $postgroupIdArr = FresnsPosts::whereNotIn('group_id', $noGroupArr)->pluck('id')->toArray();
            $noPostgroupIdArr = FresnsPosts::where('group_id', null)->pluck('id')->toArray();
            $postIdArr = array_merge($postgroupIdArr, $noPostgroupIdArr);
            $query->whereIn('post.id', $postIdArr);
        }

        // Posts from the blocking group
        if (! empty($GroupShields)) {
            $postgroupIdArr = FresnsPosts::whereNotIn('group_id', $GroupShields)->pluck('id')->toArray();
            $noPostgroupIdArr = FresnsPosts::where('group_id', null)->pluck('id')->toArray();
            $postIdArr = array_merge($postgroupIdArr, $noPostgroupIdArr);
            $query->whereIn('post.id', $postIdArr);
        }

        // Whether the member > expired_at is valid (null means permanent).
        // 1.The content is not visible after expiration and no post list is output.
        // 2.After expiration, the content before expiration is visible, and the list of posts before expiration date is output.
        // 3.During the validity period, continue the following process.
        $site_mode = ApiConfigHelper::getConfigByItemKey('site_mode');
        if ($site_mode == 'private') {
            $memberInfo = FresnsMembers::find($mid);
            if (! empty($memberInfo['expired_at']) && (strtotime($memberInfo['expired_at'])) < time()) {
                $site_private_end = ApiConfigHelper::getConfigByItemKey('site_private_end');
                if ($site_private_end == 1) {
                    $query->where('post.member_id', '=', 0);
                }
                if ($site_private_end == 2) {
                    $query->where('post.created_at', '<=', $memberInfo['expired_at']);
                }
            }
        }

        // Search: Keywords
        $searchKey = $request->input('searchKey');
        if ($searchKey) {
            $query->where('append.content', 'like', "%{$searchKey}%");
            $query->Orwhere('post.title', 'like', "%{$searchKey}%");
        }
        // Search type (parameters of the search type extension config)
        $searchType = $request->input('searchType');
        if ($searchType) {
            if ($searchType != 'all') {
                $query->where('post.types', 'like', "%{$searchType}%");
            }
        }

        // Specify the range: Member
        $searchMid = $request->input('searchMid');
        if ($searchMid) {
            // configs table settings: whether to allow viewing of other people's posts
            $allowPost = ApiConfigHelper::getConfigByItemKey(FsConfig::IT_PUBLISH_POSTS) ?? true;
            if (! $allowPost) {
                $query->where('post.member_id', '=', 0);
            } else {
                $memberInfo = FresnsMembers::where('uuid', $searchMid)->first();
                if ($memberInfo) {
                    $query->where('post.member_id', '=', $memberInfo['id']);
                } else {
                    $query->where('post.member_id', '=', 0);
                }
            }
        }
        // Specify the range: Group
        $searchGid = $request->input('searchGid');
        if ($searchGid) {
            $groupInfo = FresnsGroups::where('uuid', $searchGid)->first();
            if ($groupInfo) {
                $query->where('post.group_id', '=', $groupInfo['id']);
            } else {
                $query->where('post.group_id', '=', 0);
            }
        }
        // Specify the range: Hashtag
        $searchHuri = $request->input('searchHuri');
        if ($searchHuri) {
            $hashtagInfo = FresnsHashtags::where('slug', $searchHuri)->first();
            if ($hashtagInfo) {
                $hashtagLinkedArr = Db::table('hashtag_linkeds')->where('hashtag_id', $hashtagInfo['id'])->where('linked_type', 1)->pluck('linked_id')->toArray();
                $query->whereIn('post.id', $hashtagLinkedArr);
            } else {
                $query->where('post.id', 0);
            }
        }
        // essence_state
        $searchEssence = $request->input('searchEssence');
        if ($searchEssence) {
            $query->where('post.essence_state', $searchEssence);
        }
        // sticky_state
        $searchSticky = $request->input('searchSticky');
        if ($searchSticky) {
            $query->where('post.sticky_state', $searchSticky);
        }
        // viewCountGt
        $viewCountGt = $request->input('viewCountGt');
        if ($viewCountGt) {
            $query->where('post.view_count', '>=', $viewCountGt);
        }
        // viewCountLt
        $viewCountLt = $request->input('viewCountLt');
        if ($viewCountLt) {
            $query->where('post.view_count', '<=', $viewCountLt);
        }
        // likeCountGt
        $likeCountGt = $request->input('likeCountGt');
        if ($likeCountGt) {
            $query->where('post.like_count', '>=', $likeCountGt);
        }
        // likeCountLt
        $likeCountLt = $request->input('likeCountLt');
        if ($likeCountLt) {
            $query->where('post.like_count', '<=', $likeCountLt);
        }
        // followCountGt
        $followCountGt = $request->input('followCountGt');
        if ($followCountGt) {
            $query->where('post.follow_count', '>=', $followCountGt);
        }
        // followCountLt
        $followCountLt = $request->input('followCountLt');
        if ($followCountLt) {
            $query->where('post.follow_count', '<=', $followCountLt);
        }
        // shieldCountGt
        $shieldCountGt = $request->input('shieldCountGt');
        if ($shieldCountGt) {
            $query->where('post.shield_count', '>=', $shieldCountGt);
        }
        // shieldCountLt
        $shieldCountLt = $request->input('shieldCountLt');
        if ($shieldCountLt) {
            $query->where('post.shield_count', '<=', $shieldCountLt);
        }
        // commentCountGt
        $commentCountGt = $request->input('commentCountGt');
        if ($commentCountGt) {
            $query->where('post.comment_count', '>=', $commentCountGt);
        }
        // commentCountLt
        $commentCountLt = $request->input('commentCountLt');
        if ($commentCountLt) {
            $query->where('post.comment_count', '<=', $commentCountLt);
        }
        // createdTimeGt
        $createdTimeGt = $request->input('createdTimeGt');
        if ($createdTimeGt) {
            $query->where('post.created_at', '>=', $createdTimeGt);
        }
        // createdTimeLt
        $createdTimeLt = $request->input('createdTimeLt');
        if ($createdTimeLt) {
            $query->where('post.created_at', '<=', $createdTimeLt);
        }
        // publishTimeGt
        $publishTimeGt = $request->input('publishTimeGt');
        if ($publishTimeGt) {
            $query->where('post.created_at', '>=', $publishTimeGt);
        }
        // publishTimeLt
        $publishTimeLt = $request->input('publishTimeLt');
        if ($publishTimeLt) {
            $query->where('post.created_at', '<=', $publishTimeLt);
        }

        // Sorting
        $sortType = request()->input('sortType', '');
        $sortDirection = request()->input('sortDirection', 2);
        $sortWayType = $sortDirection == 2 ? 'DESC' : 'ASC';
        switch ($sortType) {
            case 'view':
                $query->orderBy('post.view_count', $sortWayType);
                break;
            case 'like':
                $query->orderBy('post.like_count', $sortWayType);
                break;
            case 'follow':
                $query->orderBy('post.follow_count', $sortWayType);
                break;
            case 'shield':
                $query->orderBy('post.shield_count', $sortWayType);
                break;
            case 'comment ':
                $query->orderBy('post.comment_count', $sortWayType);
                break;
            case 'time':
                $query->orderBy('post.created_at', $sortWayType);
                break;
            default:
                $query->orderBy('post.created_at', $sortWayType);
                break;
        }

        return $query;
    }

    // Search for sorted fields
    public function initOrderByFields()
    {
        $sortType = request()->input('sortType', '');
        $sortWay = request()->input('sortWay', 2);
        $sortWayType = $sortWay == 2 ? 'DESC' : 'ASC';
        switch ($sortType) {
            case 'view':
                $orderByFields = [
                    'view_count' => $sortWayType,
                    // 'updated_at' => 'DESC',
                ];

                return $orderByFields;
                break;
            case 'like':
                $orderByFields = [
                    'like_count' => $sortWayType,
                    // 'updated_at' => 'DESC',
                ];

                return $orderByFields;
                break;
            case 'follow':
                $orderByFields = [
                    'follow_count' => $sortWayType,
                    // 'updated_at' => 'DESC',
                ];

                return $orderByFields;
                break;
            case 'shield':
                $orderByFields = [
                    'shield_count' => $sortWayType,
                    // 'updated_at' => 'DESC',
                ];

                return $orderByFields;
                break;
            case 'post':
                $orderByFields = [
                    'post_count' => $sortWayType,
                    // 'updated_at' => 'DESC',
                ];

                return $orderByFields;
                break;
            case 'essence':
                $orderByFields = [
                    'essence_count' => $sortWayType,
                    // 'updated_at' => 'DESC',
                ];

                return $orderByFields;
                break;
            case 'time':
                $orderByFields = [
                    'created_at' => $sortWayType,
                    // 'updated_at' => 'DESC',
                ];

                return $orderByFields;
                break;

            default:
                $orderByFields = [
                    'created_at' => $sortWayType,
                    // 'updated_at' => 'DESC',
                ];

                return $orderByFields;
                break;
        }
    }
}
