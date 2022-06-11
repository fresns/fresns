<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Account;
use App\Models\Comment;
use App\Models\Config;
use App\Models\Extend;
use App\Models\File;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;

class PrimaryHelper
{
    public static function fresnsPrimaryId(string $tableName, ?string $tableKey = null)
    {
        if (empty($tableKey)) {
            return null;
        }

        $tableId = match ($tableName) {
            'config' => PrimaryHelper::fresnsConfigIdByItemKey($tableKey),
            'account' => PrimaryHelper::fresnsAccountIdByAid($tableKey),
            'user' => PrimaryHelper::fresnsUserIdByUidOrUsername($tableKey),
            'post' => PrimaryHelper::fresnsPostIdByPid($tableKey),
            'comment' => PrimaryHelper::fresnsCommentIdByCid($tableKey),
            'extend' => PrimaryHelper::fresnsExtendIdByEid($tableKey),
            'group' => PrimaryHelper::fresnsGroupIdByGid($tableKey),
            'hashtag' => PrimaryHelper::fresnsHashtagIdByHid($tableKey),

            'configs' => PrimaryHelper::fresnsConfigIdByItemKey($tableKey),
            'accounts' => PrimaryHelper::fresnsAccountIdByAid($tableKey),
            'users' => PrimaryHelper::fresnsUserIdByUidOrUsername($tableKey),
            'posts' => PrimaryHelper::fresnsPostIdByPid($tableKey),
            'comments' => PrimaryHelper::fresnsCommentIdByCid($tableKey),
            'extends' => PrimaryHelper::fresnsExtendIdByEid($tableKey),
            'groups' => PrimaryHelper::fresnsGroupIdByGid($tableKey),
            'hashtags' => PrimaryHelper::fresnsHashtagIdByHid($tableKey),

            default => null,
        };

        return $tableId;
    }

    /**
     * @param  string  $itemKey
     * @return int |null
     */
    public static function fresnsConfigIdByItemKey(?string $itemKey = null)
    {
        if (empty($itemKey)) {
            return null;
        }

        $id = Config::withTrashed()->where('item_key', $itemKey)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $aid
     * @return int |null
     */
    public static function fresnsAccountIdByAid(?string $aid = null)
    {
        if (empty($aid)) {
            return null;
        }

        $id = Account::withTrashed()->where('aid', $aid)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $uid
     * @return int |null
     */
    public static function fresnsAccountIdByUid(?string $uid = null)
    {
        if (empty($uid)) {
            return null;
        }

        $id = User::withTrashed()->where('uid', $uid)->value('account_id');

        return $id ?? null;
    }

    /**
     * @param  int  $uid
     * @return int |null
     */
    public static function fresnsUserIdByUid(?int $uid = null)
    {
        if (empty($uid)) {
            return null;
        }

        $id = User::withTrashed()->where('uid', $uid)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $username
     * @return int |null
     */
    public static function fresnsUserIdByUsername(?string $username = null)
    {
        if (empty($username)) {
            return null;
        }

        $id = User::withTrashed()->where('username', $username)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $uidOrUsername
     * @return int |null
     */
    public static function fresnsUserIdByUidOrUsername(?string $uidOrUsername = null)
    {
        if (empty($uidOrUsername)) {
            return null;
        }

        $id = User::withTrashed()->where('uid', $uidOrUsername)->orWhere('username', $uidOrUsername)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $gid
     * @return int |null
     */
    public static function fresnsGroupIdByGid(?string $gid = null)
    {
        if (empty($gid)) {
            return null;
        }

        $id = Group::withTrashed()->where('gid', $gid)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $hid
     * @return int |null
     */
    public static function fresnsHashtagIdByHid(?string $hid = null)
    {
        if (empty($hid)) {
            return null;
        }

        $id = Hashtag::withTrashed()->where('slug', $hid)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $pid
     * @return int |null
     */
    public static function fresnsPostIdByPid(?string $pid = null)
    {
        if (empty($pid)) {
            return null;
        }

        $id = Post::withTrashed()->where('pid', $pid)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $cid
     * @return int |null
     */
    public static function fresnsCommentIdByCid(?string $cid = null)
    {
        if (empty($cid)) {
            return null;
        }

        $id = Comment::withTrashed()->where('cid', $cid)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $fid
     * @return int |null
     */
    public static function fresnsFileIdByFid(?string $fid = null)
    {
        if (empty($fid)) {
            return null;
        }

        $id = File::withTrashed()->where('fid', $fid)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $eid
     * @return int |null
     */
    public static function fresnsExtendIdByEid(?string $eid = null)
    {
        if (empty($eid)) {
            return null;
        }

        $id = Extend::withTrashed()->where('eid', $eid)->value('id');

        return $id ?? null;
    }
}
