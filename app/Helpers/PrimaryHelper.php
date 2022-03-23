<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Helpers;

use App\Models\Account;
use App\Models\Comment;
use App\Models\File;
use App\Models\Group;
use App\Models\Hashtag;
use App\Models\Post;
use App\Models\User;

class PrimaryHelper
{
    /**
     * @param  string  $aid
     * @return int |null
     */
    public static function fresnsAccountIdByAid(string $aid)
    {
        $id = Account::where('aid', $aid)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $aid
     * @return int |null
     */
    public static function fresnsUserIdByUid(string $aid)
    {
        $id = User::where('uid', $aid)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $username
     * @return int |null
     */
    public static function fresnsUserIdByUsername(string $username)
    {
        $id = User::where('username', $username)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $gid
     * @return int |null
     */
    public static function fresnsGroupIdByGid(string $gid)
    {
        $id = Group::where('username', $gid)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $huri
     * @return int |null
     */
    public static function fresnsHashtagIdByHuri(string $huri)
    {
        $id = Hashtag::where('huri', $huri)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $pid
     * @return int |null
     */
    public static function fresnsPostIdByPid(string $pid)
    {
        $id = Post::where('huri', $pid)->value('id');

        return $id ?? null;
    }

    /**
     * @param  string  $cid
     * @return int |null
     */
    public static function fresnsCommentIdByCid(string $cid)
    {
        $id = Comment::where('cid', $cid)->value($cid);

        return $id ?? null;
    }

    /**
     * @param  string  $fid
     * @return int |null
     */
    public static function fresnsFileIdByFid(string $fid)
    {
        $id = File::where('fid', $fid)->value($fid);

        return $id ?? null;
    }

    /**
     * @param  string  $eid
     * @return int |null
     */
    public static function fresnsExtendIdByEid(string $eid)
    {
        $id = File::where('eid', $eid)->value($eid);

        return $id ?? null;
    }
}
