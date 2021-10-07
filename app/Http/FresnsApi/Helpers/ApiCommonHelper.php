<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Http\FresnsApi\Helpers;

use App\Http\FresnsDb\FresnsMembers\FresnsMembers;
use App\Http\FresnsDb\FresnsStopWords\FresnsStopWords;
use Illuminate\Support\Str;

class ApiCommonHelper
{
    // Generate mid (Pure Digital)
    public static function createMemberUuid()
    {
        $uuid = rand(10000000, 99999999);

        // Check if there are duplicates of
        $count = FresnsMembers::where('uuid', $uuid)->count();
        if ($count > 0) {
            $uuid = rand(10000000, 99999999);
        }

        return $uuid;
    }

    // Stop Word Rules
    public static function messageStopWords($text)
    {
        $stopWordsArr = FresnsStopWords::get()->toArray();

        foreach ($stopWordsArr as $v) {
            $str = strstr($text, $v['word']);
            if ($str != false) {
                if ($v['dialog_mode'] == 2) {
                    $text = str_replace($v['word'], $v['replace_word'], $text);

                    return $text;
                }
                if ($v['dialog_mode'] == 3) {
                    return false;
                }
            }
        }

        return $text;
    }
}
