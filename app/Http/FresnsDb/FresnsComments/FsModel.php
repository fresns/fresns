<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsDb\FresnsComments;

use App\Base\Models\BaseAdminModel;
use App\Base\Models\BaseCategoryModel;
use App\Http\Center\Common\GlobalService;
use App\Http\FresnsApi\Helpers\ApiConfigHelper;
use App\Http\FresnsDb\FresnsCommentAppends\FresnsCommentAppendsConfig;
use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsMemberShields\FresnsMemberShieldsConfig;
use App\Http\FresnsDb\FresnsPosts\FresnsPosts;
use App\Http\FresnsDb\FresnsPosts\FresnsPostsConfig;
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
        $memberShieldsTable = FresnsMemberShieldsConfig::CFG_TABLE;
        $commentTable = FresnsCommentsConfig::CFG_TABLE;
        $commentAppendTable = FresnsCommentAppendsConfig::CFG_TABLE;
        $postTable = FresnsPostsConfig::CFG_TABLE;

        /**
         * API Logic
         * https://fresns.org/api/content/comment-lists.html.
         */

        // Target fields to be masked
        $request = request();
        $mid = GlobalService::getGlobalKey('member_id');
        $memberShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('shield_type', 1)->pluck('shield_id')->toArray();
        $commentShields = DB::table($memberShieldsTable)->where('member_id', $mid)->where('shield_type', 5)->pluck('shield_id')->toArray();
        $query = DB::table("$commentTable as comment")->select('comment.*')
            ->join("$commentAppendTable as append", 'comment.id', '=', 'append.comment_id')
            ->join('posts as p', 'comment.post_id', '=', 'p.id')
            ->whereNotIn('comment.member_id', $memberShields)
            ->whereNotIn('comment.id', $commentShields)
            ->where('p.deleted_at', null)
            ->where('comment.deleted_at', null);

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
                    $query->where('comment.member_id', '=', 0);
                }
                if ($site_private_end == 2) {
                    $query->where('comment.created_at', '<=', $memberInfo['expired_at']);
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

        // Specify the range: Member
        $searchMid = $request->input('searchMid');
        if ($searchMid) {
            // configs table settings: whether to allow viewing of other people's comments
            $allowComment = ApiConfigHelper::getConfigByItemKey(FsConfig::IT_PUBLISH_COMMENTS) ?? false;
            $memberInfo = FresnsMembers::where('uuid', $searchMid)->first();
            if (! $allowComment) {
                $query->where('comment.member_id', '=', 0);
            } else {
                if ($memberInfo) {
                    $query->where('comment.member_id', '=', $memberInfo['id']);
                } else {
                    $query->where('comment.member_id', '=', 0);
                }
            }
        }
        // Specify the range: Post
        $searchPid = $request->input('searchPid');
        if ($searchPid) {
            $posts = FresnsPosts::where('uuid', $searchPid)->first();
            if ($posts) {
                $query->where('comment.post_id', '=', $posts['id']);
            } else {
                $query->where('comment.post_id', '=', 0);
            }
        }
        // Specify the range: Comment
        $searchCid = $request->input('searchCid');
        if ($searchCid) {
            $comments = FresnsComments::where('uuid', $searchCid)->first();
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
        // shieldCountGt
        $shieldCountGt = $request->input('shieldCountGt');
        if ($shieldCountGt) {
            $query->where('comment.shield_count', '>=', $shieldCountGt);
        }
        // shieldCountLt
        $shieldCountLt = $request->input('shieldCountLt');
        if ($shieldCountLt) {
            $query->where('comment.shield_count', '<=', $shieldCountLt);
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
            case 'shield':
                $query->orderBy('comment.shield_count', $sortWayType);
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
