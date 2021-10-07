<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Info;

use App\Http\FresnsDb\FresnsExtends\FresnsExtends;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollows;
use App\Http\FresnsDb\FresnsMemberFollows\FresnsMemberFollowsConfig;

class FsService
{
    // Get information about the query and your Follow
    public static function getMemberFollows($queryType, $idArr, $mid, $langTag = null)
    {
        $data = [];
        // Query Related Tables
        switch ($queryType) {
            case 1:
                $followIdArr = FresnsMemberFollows::where('member_id', $mid)
                    ->where('follow_type', FresnsMemberFollowsConfig::FOLLOW_TYPE_1)
                    ->pluck('follow_id')
                    ->whereIn('follow_id', $idArr)
                    ->toArray();
                break;
            case 2:
                $followIdArr = FresnsMemberFollows::where('member_id', $mid)
                    ->where('follow_type', FresnsMemberFollowsConfig::FOLLOW_TYPE_2)
                    ->pluck('follow_id')
                    ->whereIn('follow_id', $idArr)
                    ->toArray();
                break;
            case 3:
                $followIdArr = FresnsMemberFollows::where('member_id', $mid)
                    ->where('follow_type', FresnsMemberFollowsConfig::FOLLOW_TYPE_3)
                    ->pluck('follow_id')
                    ->whereIn('follow_id', $idArr)
                    ->toArray();
                break;
            case 4:
                $followIdArr = FresnsMemberFollows::where('member_id', $mid)
                    ->where('follow_type', FresnsMemberFollowsConfig::FOLLOW_TYPE_4)
                    ->whereIn('follow_id', $idArr)
                    ->pluck('follow_id')
                    ->toArray();
                break;
            case 5:
                $followIdArr = FresnsExtends::whereIn('id', $idArr)->where('member_id', $mid)->pluck('id')->toArray();
                break;
            default:
                $followIdArr = [];
                break;
        }

        if ($followIdArr) {
            // Quantity per output
            $count = FresnsMemberFollowsConfig::INPUTTIPS_COUNT;
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
