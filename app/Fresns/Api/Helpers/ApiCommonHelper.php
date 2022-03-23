<?php

/*
 * Fresns (https://fresns.org)
 * Copyright (C) 2021-Present Jarvis Tang
 * Released under the Apache-2.0 License.
 */

namespace App\Fresns\Api\Helpers;

use App\Fresns\Api\FsDb\FresnsUsers\FresnsUsers;
use App\Fresns\Api\FsDb\FresnsBlockWords\FresnsBlockWords;

class ApiCommonHelper
{
    // Generate uid (Pure Digital)
    public static function createUserUid()
    {
        $uid = rand(10000000, 99999999);

        // Check if there are duplicates of
        $count = FresnsUsers::where('uid', $uid)->count();
        if ($count > 0) {
            $uid = rand(10000000, 99999999);
        }

        return $uid;
    }

    // Block Word Rules
    public static function messageBlockWords($text)
    {
        $blockWordsArr = FresnsBlockWords::get()->toArray();

        foreach ($blockWordsArr as $v) {
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
