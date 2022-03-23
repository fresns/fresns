<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Http\Info;

use App\Fresns\Api\FsDb\FresnsExtends\FresnsExtends;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollows;
use App\Fresns\Api\FsDb\FresnsUserFollows\FresnsUserFollowsConfig;

class FsService
{
    // Get information about the query and your Follow
    public static function getUserFollows($queryType, $idArr, $uid, $langTag = null)
    {
        $data = [];
        // Query Related Tables
        switch ($queryType) {
            case 1:
                $followIdArr = FresnsUserFollows::where('user_id', $uid)
                    ->where('follow_type', FresnsUserFollowsConfig::FOLLOW_TYPE_1)
                    ->pluck('follow_id')
                    ->whereIn('follow_id', $idArr)
                    ->toArray();
                break;
            case 2:
                $followIdArr = FresnsUserFollows::where('user_id', $uid)
                    ->where('follow_type', FresnsUserFollowsConfig::FOLLOW_TYPE_2)
                    ->pluck('follow_id')
                    ->whereIn('follow_id', $idArr)
                    ->toArray();
                break;
            case 3:
                $followIdArr = FresnsUserFollows::where('user_id', $uid)
                    ->where('follow_type', FresnsUserFollowsConfig::FOLLOW_TYPE_3)
                    ->pluck('follow_id')
                    ->whereIn('follow_id', $idArr)
                    ->toArray();
                break;
            case 4:
                $followIdArr = FresnsUserFollows::where('user_id', $uid)
                    ->where('follow_type', FresnsUserFollowsConfig::FOLLOW_TYPE_4)
                    ->whereIn('follow_id', $idArr)
                    ->pluck('follow_id')
                    ->toArray();
                break;
            case 5:
                $followIdArr = FresnsExtends::whereIn('id', $idArr)->where('user_id', $uid)->pluck('id')->toArray();
                break;
            default:
                $followIdArr = [];
                break;
        }

        if ($followIdArr) {
            // Quantity per output
            $count = FresnsUserFollowsConfig::INPUTTIPS_COUNT;
            $followCount = count($followIdArr);
            if ($followCount == $count) {
                $data = $followIdArr;
            }
            if ($followCount > $count) {
                $data = array_slice($followIdArr, 0, $count);
            }
            if ($followCount < $count) {
                $diffArr = array_diff($idArr, $followIdArr);
                $diffCount = $count - $followCount;
                sort($diffArr);
                $diffArr = array_slice($diffArr, 0, $diffCount);

                $data = array_merge($followIdArr, $diffArr);
            }
        } else {
            $data = $idArr;
        }

        return $data;
    }
}
