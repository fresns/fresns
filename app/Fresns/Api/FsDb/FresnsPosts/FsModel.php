<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsPosts;

use App\Fresns\Api\Base\Models\BaseAdminModel;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\FsDb\FresnsGroups\FresnsGroups;
use App\Fresns\Api\FsDb\FresnsHashtagLinkeds\FresnsHashtagLinkedsConfig;
use App\Fresns\Api\FsDb\FresnsHashtags\FresnsHashtags;
use App\Fresns\Api\FsDb\FresnsPostAppends\FresnsPostAppendsConfig;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocksConfig;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollowsConfig;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\Helpers\ApiConfigHelper;
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
        $userBlocksTable = FresnsUserBlocksConfig::CFG_TABLE;
        $userFollowTable = FresnsUserFollowsConfig::CFG_TABLE;
        $postTable = FresnsPostsConfig::CFG_TABLE;
        $append = FresnsPostAppendsConfig::CFG_TABLE;

        /**
         * API Logic
         * https://fresns.org/api/content/post-lists.html.
         */
        $request = request();
        $uid = GlobalService::getGlobalKey('user_id');

        // type_mode = 2 (Private: Only users can see who's in the group and what they post.)
        $FresnsGroups = FresnsGroups::where('type_mode', 2)->pluck('id')->toArray();
        // $FresnsGroups = FresnsGroups::where('type_mode', 2)->where('type_find', 2)->pluck('id')->toArray();
        $groupUser = DB::table($userFollowTable)->where('user_id', $uid)->where('deleted_at', null)->where('follow_type', 2)->pluck('follow_id')->toArray();
        $noGroupArr = array_diff($FresnsGroups, $groupUser);

        // Filter the posts of blocked objects (users, groups, hashtags, posts), and the posts of blocked objects are not output.
        $userBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('deleted_at', null)->where('block_type', 1)->pluck('block_id')->toArray();
        $GroupBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('deleted_at', null)->where('block_type', 2)->pluck('block_id')->toArray();
        $hashtagBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('deleted_at', null)->where('block_type', 3)->pluck('block_id')->toArray();
        $noPostHashtags = DB::table(FresnsHashtagLinkedsConfig::CFG_TABLE)->where('linked_type', 1)->where('deleted_at', null)->whereIn('hashtag_id', $hashtagBlocks)->pluck('linked_id')->toArray();
        $commentBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('deleted_at', null)->where('block_type', 4)->pluck('block_id')->toArray();
        $query = DB::table("$postTable as post")->select('post.*')
            ->join("$append as append", 'post.id', '=', 'append.post_id')
            ->whereNotIn('post.user_id', $userBlocks)
            ->whereNotIn('post.id', $noPostHashtags)
            ->whereNotIn('post.id', $commentBlocks)
            ->where('post.deleted_at', null);

        // Posts from the Powerless Group
        if (! empty($noGroupArr)) {
            $postgroupIdArr = FresnsPosts::whereNotIn('group_id', $noGroupArr)->pluck('id')->toArray();
            $noPostgroupIdArr = FresnsPosts::where('group_id', null)->pluck('id')->toArray();
            $postIdArr = array_merge($postgroupIdArr, $noPostgroupIdArr);
            $query->whereIn('post.id', $postIdArr);
        }

        // Posts from the blocking group
        if (! empty($GroupBlocks)) {
            $postgroupIdArr = FresnsPosts::whereNotIn('group_id', $GroupBlocks)->pluck('id')->toArray();
            $noPostgroupIdArr = FresnsPosts::where('group_id', null)->pluck('id')->toArray();
            $postIdArr = array_merge($postgroupIdArr, $noPostgroupIdArr);
            $query->whereIn('post.id', $postIdArr);
        }

        // Whether the user > expired_at is valid (null means permanent).
        // 1.The content is not visible after expiration and no post list is output.
        // 2.After expiration, the content before expiration is visible, and the list of posts before expiration date is output.
        // 3.During the validity period, continue the following process.
        $site_mode = ApiConfigHelper::getConfigByItemKey('site_mode');
        if ($site_mode == 'private') {
            $userInfo = FresnsUsers::find($uid);
            if (! empty($userInfo['expired_at']) && (strtotime($userInfo['expired_at'])) < time()) {
                $site_private_end = ApiConfigHelper::getConfigByItemKey('site_private_end');
                if ($site_private_end == 1) {
                    $query->where('post.user_id', '=', 0);
                }
                if ($site_private_end == 2) {
                    $query->where('post.created_at', '<=', $userInfo['expired_at']);
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

        // Specify the range: User
        $searchUid = $request->input('searchUid');
        if ($searchUid) {
            // configs table settings: whether to allow viewing of other people's posts
            $allowPost = ApiConfigHelper::getConfigByItemKey(FsConfig::IT_POSTS) ?? true;
            if (! $allowPost) {
                $query->where('post.user_id', '=', 0);
            } else {
                $userInfo = FresnsUsers::where('uid', $searchUid)->first();
                if ($userInfo) {
                    $query->where('post.user_id', '=', $userInfo['id']);
                } else {
                    $query->where('post.user_id', '=', 0);
                }
            }
        }
        // Specify the range: Group
        $searchGid = $request->input('searchGid');
        if ($searchGid) {
            $groupInfo = FresnsGroups::where('gid', $searchGid)->first();
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
        // digest_state
        $searchDigest = $request->input('searchDigest');
        if ($searchDigest) {
            $query->where('post.digest_state', $searchDigest);
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
        // blockCountGt
        $blockCountGt = $request->input('blockCountGt');
        if ($blockCountGt) {
            $query->where('post.block_count', '>=', $blockCountGt);
        }
        // blockCountLt
        $blockCountLt = $request->input('blockCountLt');
        if ($blockCountLt) {
            $query->where('post.block_count', '<=', $blockCountLt);
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
            case 'block':
                $query->orderBy('post.block_count', $sortWayType);
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
            case 'block':
                $orderByFields = [
                    'block_count' => $sortWayType,
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
            case 'digest':
                $orderByFields = [
                    'digest_count' => $sortWayType,
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
