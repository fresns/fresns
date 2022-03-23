<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\FsDb\FresnsComments;

use App\Fresns\Api\Base\Models\BaseCategoryModel;
use App\Fresns\Api\Center\Common\GlobalService;
use App\Fresns\Api\Helpers\ApiConfigHelper;
use App\Fresns\Api\FsDb\FresnsCommentAppends\FresnsCommentAppendsConfig;
use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsUserBlocks\FresnsUserBlocksConfig;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPosts;
use App\Fresns\Api\FsDb\FresnsPosts\FresnsPostsConfig;
use Illuminate\Support\Facades\DB;

class FsModel extends BaseCategoryModel
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
        $commentTable = FresnsCommentsConfig::CFG_TABLE;
        $commentAppendTable = FresnsCommentAppendsConfig::CFG_TABLE;
        $postTable = FresnsPostsConfig::CFG_TABLE;

        /**
         * API Logic
         * https://fresns.org/api/content/comment-lists.html.
         */

        // Target fields to be masked
        $request = request();
        $uid = GlobalService::getGlobalKey('user_id');
        $userBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('block_type', 1)->pluck('block_id')->toArray();
        $commentBlocks = DB::table($userBlocksTable)->where('user_id', $uid)->where('block_type', 5)->pluck('block_id')->toArray();
        $query = DB::table("$commentTable as comment")->select('comment.*')
            ->join("$commentAppendTable as append", 'comment.id', '=', 'append.comment_id')
            ->join('posts as p', 'comment.post_id', '=', 'p.id')
            ->whereNotIn('comment.user_id', $userBlocks)
            ->whereNotIn('comment.id', $commentBlocks)
            ->where('p.deleted_at', null)
            ->where('comment.deleted_at', null);

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
                    $query->where('comment.user_id', '=', 0);
                }
                if ($site_private_end == 2) {
                    $query->where('comment.created_at', '<=', $userInfo['expired_at']);
                }
            }
        }

        // Search: Keywords
        $searchKey = $request->input('searchKey');
        if ($searchKey) {
            $query->where('append.content', 'like', "%{$searchKey}%");
        }
        // Search type (parameters of the search type extension config)
        $searchType = $request->input('searchType');
        if ($searchType) {
            $query->where('comment.types', 'like', "%{$searchType}%");
        }

        // Specify the range: User
        $searchUid = $request->input('searchUid');
        if ($searchUid) {
            // configs table settings: whether to allow viewing of other people's comments
            $allowComment = ApiConfigHelper::getConfigByItemKey(FsConfig::IT_PUBLISH_COMMENTS) ?? false;
            $userInfo = FresnsUsers::where('uid', $searchUid)->first();
            if (! $allowComment) {
                $query->where('comment.user_id', '=', 0);
            } else {
                if ($userInfo) {
                    $query->where('comment.user_id', '=', $userInfo['id']);
                } else {
                    $query->where('comment.user_id', '=', 0);
                }
            }
        }
        // Specify the range: Post
        $searchPid = $request->input('searchPid');
        if ($searchPid) {
            $posts = FresnsPosts::where('pid', $searchPid)->first();
            if ($posts) {
                $query->where('comment.post_id', '=', $posts['id']);
            } else {
                $query->where('comment.post_id', '=', 0);
            }
        }
        // Specify the range: Comment
        $searchCid = $request->input('searchCid');
        if ($searchCid) {
            $comments = FresnsComments::where('cid', $searchCid)->first();
            // Determine if it is a first class comment (parent_id = 0)
            if ($comments) {
                if ($comments['parent_id'] == 0) {
                    $FsService = new FsService();
                    request()->offsetSet('id', $comments['id']);
                    $data = $FsService->listTreeNoRankNum();
                    $data = $FsService->treeData();
                    if ($data) {
                        $childrenIdArr = [];
                        foreach ($data as $v) {
                            $this->getChildrenIds($v, $childrenIdArr);
                        }
                    }
                    array_unshift($childrenIdArr, $comments['id']);
                    request()->offsetUnset('id');
                    $query->whereIn('comment.id', $childrenIdArr)->where('comment.parent_id', '!=', 0);
                } else {
                    $query->where('comment.id', '=', 0);
                }
            } else {
                $query->where('comment.id', '=', 0);
            }
        } else {
            $query->where('comment.parent_id', '=', 0);
        }

        // sticky state
        $searchSticky = $request->input('searchSticky');
        if (! empty($searchSticky)) {
            $query->where('comment.is_sticky', '=', $searchSticky);
        }
        if ($searchSticky == '0') {
            $query->where('comment.is_sticky', '=', 0);
        }

        // likeCountGt
        $likeCountGt = $request->input('likeCountGt');
        if ($likeCountGt) {
            $query->where('comment.like_count', '>=', $likeCountGt);
        }
        // likeCountLt
        $likeCountLt = $request->input('likeCountLt');
        if ($likeCountLt) {
            $query->where('comment.like_count', '<=', $likeCountLt);
        }
        // followCountGt
        $followCountGt = $request->input('followCountGt');
        if ($followCountGt) {
            $query->where('comment.follow_count', '>=', $followCountGt);
        }
        // followCountLt
        $followCountLt = $request->input('followCountLt');
        if ($followCountLt) {
            $query->where('comment.follow_count', '<=', $followCountLt);
        }
        // blockCountGt
        $blockCountGt = $request->input('blockCountGt');
        if ($blockCountGt) {
            $query->where('comment.block_count', '>=', $blockCountGt);
        }
        // blockCountLt
        $blockCountLt = $request->input('blockCountLt');
        if ($blockCountLt) {
            $query->where('comment.block_count', '<=', $blockCountLt);
        }
        // commentCountGt
        $commentCountGt = $request->input('commentCountGt');
        if ($commentCountGt) {
            $query->where('comment.comment_count', '>=', $commentCountGt);
        }
        // commentCountLt
        $commentCountLt = $request->input('commentCountLt');
        if ($commentCountLt) {
            $query->where('comment.comment_count', '<=', $commentCountLt);
        }
        // createdTimeGt
        $createdTimeGt = $request->input('createdTimeGt');
        if ($createdTimeGt) {
            $query->where('comment.created_at', '>=', $createdTimeGt);
        }
        // createdTimeLt
        $createdTimeLt = $request->input('createdTimeLt');
        if ($createdTimeLt) {
            $query->where('comment.created_at', '<=', $createdTimeLt);
        }
        // publishTimeGt
        $publishTimeGt = $request->input('publishTimeGt');
        if ($publishTimeGt) {
            $query->where('comment.created_at', '>=', $publishTimeGt);
        }
        // publishTimeLt
        $publishTimeLt = $request->input('publishTimeLt');
        if ($publishTimeLt) {
            $query->where('comment.created_at', '<=', $publishTimeLt);
        }

        // Sorting
        $sortType = request()->input('sortType', '');
        $sortWay = request()->input('sortDirection', 2);
        $sortWayType = $sortWay == 2 ? 'DESC' : 'ASC';
        switch ($sortType) {
            case 'view':
                $query->orderBy('comment.view_count', $sortWayType);
                break;
            case 'follow':
                $query->orderBy('comment.follow_count', $sortWayType);
                break;
            case 'block':
                $query->orderBy('comment.block_count', $sortWayType);
                break;
            case 'comment ':
                $query->orderBy('comment.comment_count', $sortWayType);
                break;
            case 'time':
                $query->orderBy('comment.created_at', $sortWayType);
                break;
            default:
                $query->orderBy('comment.created_at', $sortWayType);
                break;
        }

        return $query;
    }

    // Search for sorted fields
    public function initOrderByFields()
    {
        $orderByFields = [
            'created_at' => 'DESC',
            // 'updated_at' => 'DESC',
        ];

        return $orderByFields;
    }

    // Get childrenIds
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
}
